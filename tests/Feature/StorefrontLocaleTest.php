<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_uses_the_correct_document_language_and_direction(): void
    {
        $this->get('/?lang=ar')
            ->assertOk()
            ->assertSee('<html lang="ar" dir="rtl">', false);

        $this->get('/?lang=he')
            ->assertOk()
            ->assertSee('<html lang="he" dir="rtl">', false);

        $this->get('/?lang=en')
            ->assertOk()
            ->assertSee('<html lang="en" dir="ltr">', false);
    }

    public function test_login_and_registration_use_the_correct_direction(): void
    {
        $this->get('/login?lang=ar')
            ->assertOk()
            ->assertSee('<html lang="ar" dir="rtl">', false);

        $this->get('/register?lang=en')
            ->assertOk()
            ->assertSee('<html lang="en" dir="ltr">', false);
    }
}
