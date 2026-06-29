<?php

namespace Tests\Feature;

use App\Models\StorefrontPromotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontHomepagePromotionTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_active_promotions_with_localized_links(): void
    {
        StorefrontPromotion::query()->forceCreate([
            'eyebrow' => ['en' => 'Hot Deals', 'ar' => 'عروض قوية'],
            'title' => ['en' => 'Weekend laptop offers', 'ar' => 'عروض لابتوبات نهاية الأسبوع'],
            'description' => ['en' => 'Curated offers controlled from the admin panel.'],
            'button_text' => ['en' => 'Shop deals'],
            'button_url' => '/store/products?on_sale=1',
            'placement' => 'home_after_hero',
            'style' => 'gradient',
            'is_active' => true,
        ]);

        $response = $this->get('/?lang=en');

        $response->assertOk();
        $response->assertSee('Weekend laptop offers');
        $response->assertSee('Shop deals');
        $response->assertSee('/store/products?on_sale=1&amp;lang=en', false);
        $response->assertDontSee('href="#"', false);
    }
}
