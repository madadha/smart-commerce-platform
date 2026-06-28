<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveLanguageStorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_storefront_switcher_only_renders_active_languages(): void
    {
        $this->createLanguage('ar', true, true, 'rtl', 1);
        $this->createLanguage('he', true, false, 'rtl', 2);
        $this->createLanguage('en', false, false, 'ltr', 3);

        $this->get('/?lang=ar')
            ->assertOk()
            ->assertSee('lang=ar', false)
            ->assertSee('lang=he', false)
            ->assertDontSee('lang=en', false);
    }

    public function test_disabled_language_request_falls_back_to_the_active_default_language(): void
    {
        $this->createLanguage('ar', true, true, 'rtl', 1);
        $this->createLanguage('he', false, false, 'rtl', 2);
        $this->createLanguage('en', false, false, 'ltr', 3);

        $this->get('/?lang=en')
            ->assertOk()
            ->assertSee('<html lang="ar" dir="rtl">', false)
            ->assertDontSee('lang=en', false);
    }

    public function test_admin_product_form_hides_fields_for_inactive_languages(): void
    {
        $this->createLanguage('ar', true, true, 'rtl', 1);
        $this->createLanguage('he', false, false, 'rtl', 2);
        $this->createLanguage('en', false, false, 'ltr', 3);

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin)
            ->get('/admin/products/create')
            ->assertOk()
            ->assertSee('الاسم بالعربية')
            ->assertDontSee('الاسم بالعبرية')
            ->assertDontSee('الاسم بالإنجليزية');
    }

    public function test_admin_navigation_does_not_crash_when_english_is_default_language(): void
    {
        $this->createLanguage('en', true, true, 'ltr', 1);
        $this->createLanguage('ar', true, false, 'rtl', 2);
        $this->createLanguage('he', false, false, 'rtl', 3);

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin)
            ->get('/admin/languages')
            ->assertOk()
            ->assertSee('Languages');
    }

    public function test_admin_invoice_grid_uses_translated_order_number_label(): void
    {
        $this->createLanguage('ar', true, true, 'rtl', 1);
        $this->createLanguage('en', true, false, 'ltr', 2);
        $this->createLanguage('he', false, false, 'rtl', 3);

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin)
            ->get('/admin/invoices')
            ->assertOk()
            ->assertSee('رقم الطلب')
            ->assertDontSeeText('order.order_number');

        $this->actingAs($admin)
            ->get('/admin/invoices/create')
            ->assertOk()
            ->assertSee('رقم الطلب')
            ->assertDontSeeText('order.order_number');
    }

    private function createLanguage(string $code, bool $isActive, bool $isDefault, string $direction, int $sortOrder): Language
    {
        return Language::query()->forceCreate([
            'name' => strtoupper($code),
            'native_name' => strtoupper($code),
            'code' => $code,
            'direction' => $direction,
            'is_active' => $isActive,
            'is_default' => $isDefault,
            'sort_order' => $sortOrder,
        ]);
    }
}
