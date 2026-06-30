<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameRegion;
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

    public function test_homepage_renders_large_ads_slider_and_small_ad_tiles(): void
    {
        StorefrontPromotion::query()->forceCreate([
            'title' => ['en' => 'Homepage Mega Campaign'],
            'description' => ['en' => 'Large homepage ad controlled from admin.'],
            'button_text' => ['en' => 'Explore'],
            'button_url' => '/store/products?on_sale=1',
            'image_path' => 'storefront/promotions/home-mega.jpg',
            'placement' => 'home_ads_hero',
            'style' => 'dark',
            'is_active' => true,
        ]);

        StorefrontPromotion::query()->forceCreate([
            'title' => ['en' => 'Homepage Small Deal'],
            'button_text' => ['en' => 'Shop'],
            'button_url' => '/store/products',
            'image_path' => 'storefront/promotions/home-small.jpg',
            'placement' => 'home_ads_strip',
            'style' => 'light',
            'is_active' => true,
        ]);

        $response = $this->get('/?lang=en');

        $response->assertOk();
        $response->assertSee('data-scp-home-ads', false);
        $response->assertSee('scp-products-ad-slider', false);
        $response->assertSee('scp-products-ad-tiles', false);
        $response->assertSee('Homepage Mega Campaign');
        $response->assertSee('Large homepage ad controlled from admin.');
        $response->assertSee('/store/products?on_sale=1&amp;lang=en', false);
        $response->assertSee('Homepage Small Deal');
        $response->assertSee('storage/storefront/promotions/home-mega.jpg', false);
        $response->assertSee('storage/storefront/promotions/home-small.jpg', false);
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

    public function test_homepage_renders_game_topup_section_when_active_games_exist(): void
    {
        $game = Game::query()->forceCreate([
            'name' => ['en' => 'PUBG MOBILE', 'ar' => 'ببجي موبايل'],
            'description' => ['en' => 'Recharge UC packages safely.'],
            'slug' => 'pubg-mobile',
            'icon' => 'games/icons/pubg.png',
            'banner_image' => 'games/banners/pubg.jpg',
            'supports_player_validation' => true,
            'is_active' => true,
        ]);

        $region = GameRegion::query()->forceCreate([
            'name' => ['en' => 'Middle East'],
            'code' => 'middle-east',
            'is_active' => true,
        ]);

        $game->regions()->attach($region->id, ['is_active' => true]);

        $response = $this->get('/?lang=en');

        $response->assertOk();
        $response->assertSee('Gaming Recharge');
        $response->assertSee('PUBG MOBILE');
        $response->assertSee('Recharge UC packages safely.');
        $response->assertSee('Player validation');
        $response->assertSee('/store/products?lang=en&amp;type=game_topup', false);
        $response->assertSee('storage/games/icons/pubg.png', false);
        $response->assertSee('storage/games/banners/pubg.jpg', false);
    }

    public function test_homepage_hides_game_topup_section_when_feature_is_disabled(): void
    {
        StorefrontSetting::query()->forceCreate([
            'store_name' => ['en' => 'Smart Commerce'],
            'store_tagline' => ['en' => 'Marketplace Platform'],
            'enable_game_topups' => false,
            'is_active' => true,
        ]);

        Game::query()->forceCreate([
            'name' => ['en' => 'PUBG MOBILE'],
            'slug' => 'pubg-mobile',
            'is_active' => true,
        ]);

        $response = $this->get('/?lang=en');

        $response->assertOk();
        $response->assertDontSee('Gaming Recharge');
        $response->assertDontSee('PUBG MOBILE');
    }
}
