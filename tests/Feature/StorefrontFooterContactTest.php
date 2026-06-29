<?php

namespace Tests\Feature;

use App\Models\StorefrontSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontFooterContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_footer_renders_contact_settings_from_admin(): void
    {
        StorefrontSetting::query()->forceCreate([
            'store_name' => ['en' => 'Smart Commerce'],
            'store_tagline' => ['en' => 'Marketplace Platform'],
            'footer_description' => ['en' => 'Admin controlled footer text.'],
            'footer_rights_text' => ['en' => 'Custom rights text.'],
            'address' => ['en' => 'Haifa, Israel'],
            'contact_email' => 'support@example.test',
            'contact_phone' => '+972 50 000 0000',
            'whatsapp' => '+972500000001',
            'facebook_url' => 'https://facebook.example.test/store',
            'facebook_icon' => 'FB',
            'whatsapp_floating_icon' => 'WA',
            'show_floating_whatsapp' => true,
            'is_active' => true,
        ]);

        $response = $this->get('/?lang=en');

        $response->assertOk();
        $response->assertSee('Admin controlled footer text.');
        $response->assertSee('support@example.test');
        $response->assertSee('+972 50 000 0000');
        $response->assertSee('+972500000001');
        $response->assertSee('Haifa, Israel');
        $response->assertSee('https://facebook.example.test/store');
        $response->assertSee('FB');
        $response->assertSee('Custom rights text.');
        $response->assertSee('scp-floating-whatsapp', false);
        $response->assertSee('https://wa.me/972500000001');
    }
}
