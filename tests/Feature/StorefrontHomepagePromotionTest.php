<?php

namespace Tests\Feature;

use App\Models\StorefrontPromotion;
use App\Models\StorefrontSetting;
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

    public function test_homepage_section_copy_is_loaded_from_storefront_settings(): void
    {
        StorefrontSetting::query()->forceCreate([
            'store_name' => ['en' => 'Smart Commerce'],
            'store_tagline' => ['en' => 'Marketplace Platform'],
            'categories_section_title' => ['en' => 'Admin Categories'],
            'categories_section_subtitle' => ['en' => 'Admin category subtitle.'],
            'featured_section_title' => ['en' => 'Admin Featured'],
            'featured_section_subtitle' => ['en' => 'Admin featured subtitle.'],
            'latest_section_title' => ['en' => 'Admin Latest'],
            'latest_section_subtitle' => ['en' => 'Admin latest subtitle.'],
            'brands_section_title' => ['en' => 'Admin Brands'],
            'brands_section_subtitle' => ['en' => 'Admin brands subtitle.'],
            'is_active' => true,
        ]);

        $response = $this->get('/?lang=en');

        $response->assertOk();
        $response->assertSee('Admin Categories');
        $response->assertSee('Admin category subtitle.');
        $response->assertSee('Admin Featured');
        $response->assertSee('Admin featured subtitle.');
        $response->assertSee('Admin Latest');
        $response->assertSee('Admin latest subtitle.');
        $response->assertSee('Admin Brands');
        $response->assertSee('Admin brands subtitle.');
    }
}
