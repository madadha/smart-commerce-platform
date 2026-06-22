<?php

namespace Tests\Feature;

use App\Models\PaymentProviderSetting;
use App\Models\User;
use App\Payments\PaymentGatewayManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PaymentProviderSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_supported_providers_are_seeded_in_a_safe_disabled_state(): void
    {
        $this->assertSame(
            ['payplus', 'paypal', 'stripe', 'paddle'],
            PaymentProviderSetting::query()->orderBy('sort_order')->pluck('provider')->all(),
        );
        $this->assertSame(0, PaymentProviderSetting::query()->where('is_enabled', true)->count());
    }

    public function test_provider_credentials_are_encrypted_at_rest(): void
    {
        $provider = PaymentProviderSetting::query()->where('provider', 'payplus')->firstOrFail();
        $provider->update([
            'sandbox_credentials' => [
                'api_key' => 'sandbox-api-secret',
                'secret_key' => 'sandbox-private-secret',
                'payment_page_uid' => 'sandbox-page-uid',
            ],
        ]);

        $rawValue = DB::table('payment_provider_settings')
            ->where('id', $provider->id)
            ->value('sandbox_credentials');

        $this->assertStringNotContainsString('sandbox-api-secret', $rawValue);
        $this->assertStringNotContainsString('sandbox-private-secret', $rawValue);
        $this->assertSame('sandbox-api-secret', $provider->fresh()->sandbox_credentials['api_key']);
    }

    public function test_enabling_unverified_provider_does_not_expose_it_at_checkout(): void
    {
        $provider = PaymentProviderSetting::query()->where('provider', 'payplus')->firstOrFail();
        $provider->update([
            'is_enabled' => true,
            'connection_status' => 'untested',
            'sandbox_credentials' => [
                'api_key' => 'api-key',
                'secret_key' => 'secret-key',
                'payment_page_uid' => 'page-uid',
            ],
        ]);

        $methods = app(PaymentGatewayManager::class)->enabledMethods();

        $this->assertArrayHasKey('cash', $methods);
        $this->assertArrayHasKey('bank_transfer', $methods);
        $this->assertArrayNotHasKey('payplus', $methods);
    }

    public function test_required_credentials_are_provider_specific(): void
    {
        $provider = PaymentProviderSetting::query()->where('provider', 'paypal')->firstOrFail();
        $provider->update([
            'sandbox_credentials' => [
                'client_id' => 'client-id',
                'client_secret' => 'client-secret',
            ],
        ]);

        $this->assertFalse($provider->fresh()->hasRequiredCredentials());

        $provider->update([
            'sandbox_credentials' => [
                'client_id' => 'client-id',
                'client_secret' => 'client-secret',
                'webhook_id' => 'webhook-id',
            ],
        ]);

        $this->assertTrue($provider->fresh()->hasRequiredCredentials());
    }

    public function test_authorized_admin_can_open_provider_list_and_payplus_configuration(): void
    {
        $user = User::factory()->create();
        Permission::findOrCreate('view admin panel', 'web');
        $user->givePermissionTo('view admin panel');
        $provider = PaymentProviderSetting::query()->where('provider', 'payplus')->firstOrFail();

        $this->actingAs($user)
            ->get('/admin/payment-provider-settings')
            ->assertOk()
            ->assertSee('PayPlus')
            ->assertSee('PayPal')
            ->assertSee('Stripe')
            ->assertSee('Paddle');

        $this->actingAs($user)
            ->get("/admin/payment-provider-settings/{$provider->id}/edit")
            ->assertOk()
            ->assertSee('Provider Status')
            ->assertSee('Sandbox Credentials')
            ->assertSee('Live Credentials');
    }
}
