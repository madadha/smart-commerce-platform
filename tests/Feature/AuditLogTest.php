<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\PaymentProviderSetting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_sensitive_payment_provider_changes_are_logged_with_redacted_credentials(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $provider = PaymentProviderSetting::query()->where('provider', 'payplus')->firstOrFail();

        $provider->forceFill([
            'display_name' => ['en' => 'PayPlus'],
            'description' => ['en' => 'Initial description'],
            'is_enabled' => true,
            'mode' => 'sandbox',
            'sandbox_credentials' => [
                'api_key' => 'sk_test_initial',
                'secret_key' => 'secret_initial',
                'webhook_secret' => 'whsec_initial',
            ],
            'live_credentials' => [
                'api_key' => 'sk_live_initial',
                'secret_key' => 'secret_live_initial',
                'webhook_secret' => 'whsec_live_initial',
            ],
            'supported_currencies' => ['USD', 'JOD'],
            'connection_status' => 'untested',
            'sort_order' => 1,
        ])->save();

        $this->actingAs($admin);

        $provider->forceFill([
            'description' => ['en' => 'Updated description'],
            'sandbox_credentials' => [
                'api_key' => 'sk_test_updated',
                'secret_key' => 'secret_updated',
                'webhook_secret' => 'whsec_updated',
            ],
            'live_credentials' => [
                'api_key' => 'sk_live_updated',
                'secret_key' => 'secret_live_updated',
                'webhook_secret' => 'whsec_live_updated',
            ],
            'connection_status' => 'verified',
        ])->save();

        $audit = AuditLog::query()
            ->where('event', 'updated')
            ->where('subject_type', PaymentProviderSetting::class)
            ->latest('id')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame($admin->id, $audit->user_id);
        $this->assertSame('[redacted]', data_get($audit->old_values, 'sandbox_credentials'));
        $this->assertSame('[redacted]', data_get($audit->new_values, 'sandbox_credentials'));
        $this->assertSame('[redacted]', data_get($audit->old_values, 'live_credentials'));
        $this->assertSame('[redacted]', data_get($audit->new_values, 'live_credentials'));
        $this->assertSame('verified', data_get($audit->new_values, 'connection_status'));
    }

    public function test_audit_logs_are_visible_only_to_privileged_admins(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $ordersManager = User::factory()->create();
        $ordersManager->assignRole('orders-manager');

        $this->actingAs($superAdmin)->get('/admin/audit-logs')->assertOk();
        $this->actingAs($ordersManager)->get('/admin/audit-logs')->assertForbidden();
    }
}
