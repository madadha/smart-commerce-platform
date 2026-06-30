<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\StorefrontSetting;
use Tests\TestCase;

class StorefrontCookieConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_cookie_consent_is_rendered_only_on_store_pages_with_current_language(): void
    {
        $this->get('/store?lang=ar')
            ->assertOk()
            ->assertSee('scp-cookie-consent', false)
            ->assertSee('dir="rtl"', false)
            ->assertSee('موافق')
            ->assertSee('سياسة الخصوصية');

        $this->get('/store/products?lang=he')
            ->assertOk()
            ->assertSee('scp-cookie-consent', false)
            ->assertSee('קראתי')
            ->assertSee('מדיניות הפרטיות שלנו');

        $this->get('/store/products?lang=en')
            ->assertOk()
            ->assertSee('scp-cookie-consent', false)
            ->assertSee('dir="ltr"', false)
            ->assertSee('Got it')
            ->assertSee('Privacy Policy');

        $this->get('/?lang=ar')
            ->assertOk()
            ->assertDontSee('scp-cookie-consent', false);
    }

    public function test_cookie_consent_uses_admin_controlled_settings(): void
    {
        StorefrontSetting::query()->forceCreate([
            'store_name' => ['en' => 'Smart Commerce'],
            'store_tagline' => ['en' => 'Marketplace Platform'],
            'show_cookie_consent' => true,
            'cookie_consent_text' => ['en' => 'Custom cookie copy from admin.'],
            'cookie_consent_button_text' => ['en' => 'Accept custom'],
            'cookie_consent_privacy_text' => ['en' => 'Custom privacy'],
            'cookie_consent_privacy_url' => '/privacy',
            'cookie_consent_storage_key' => 'custom_cookie_key',
            'cookie_consent_background_color' => '#071225',
            'cookie_consent_button_color' => '#d4a24c',
            'is_active' => true,
        ]);

        $this->get('/store?lang=en')
            ->assertOk()
            ->assertSee('Custom cookie copy from admin.')
            ->assertSee('Accept custom')
            ->assertSee('Custom privacy')
            ->assertSee('href="/privacy"', false)
            ->assertSee('data-storage-key="custom_cookie_key"', false)
            ->assertSee('--scp-cookie-bg: #071225;', false)
            ->assertSee('--scp-cookie-button-bg: #d4a24c;', false);
    }

    public function test_cookie_consent_can_be_disabled_from_admin(): void
    {
        StorefrontSetting::query()->forceCreate([
            'store_name' => ['en' => 'Smart Commerce'],
            'store_tagline' => ['en' => 'Marketplace Platform'],
            'show_cookie_consent' => false,
            'is_active' => true,
        ]);

        $this->get('/store?lang=en')
            ->assertOk()
            ->assertDontSee('scp-cookie-consent', false);
    }
}
