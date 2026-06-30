<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
