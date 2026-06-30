<?php

namespace Tests\Feature;

use App\Models\StorefrontPromotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontProductsAdsTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_page_renders_large_ads_slider_and_small_ad_tiles(): void
    {
        StorefrontPromotion::query()->forceCreate([
            'title' => ['en' => 'Apple Watch Campaign'],
            'description' => ['en' => 'The ultimate watch for a healthy life.'],
            'button_text' => ['en' => 'Shop now'],
            'button_url' => '/store/products?brand=1',
            'image_path' => 'storefront/promotions/watch.jpg',
            'placement' => 'products_ads_hero',
            'style' => 'dark',
            'is_active' => true,
        ]);

        StorefrontPromotion::query()->forceCreate([
            'title' => ['en' => 'Trade-in Deals'],
            'button_text' => ['en' => 'Learn more'],
            'button_url' => '#',
            'image_path' => 'storefront/promotions/trade-in.jpg',
            'placement' => 'products_ads_strip',
            'style' => 'light',
            'is_active' => true,
        ]);

        $response = $this->get('/store/products?lang=en');

        $response->assertOk();
        $response->assertSee('scp-products-ad-slider', false);
        $response->assertSee('scp-products-ad-tiles', false);
        $response->assertSee('Apple Watch Campaign');
        $response->assertSee('The ultimate watch for a healthy life.');
        $response->assertSee('/store/products?brand=1&amp;lang=en', false);
        $response->assertSee('Trade-in Deals');
        $response->assertSee('storage/storefront/promotions/watch.jpg', false);
        $response->assertSee('storage/storefront/promotions/trade-in.jpg', false);
    }
}
