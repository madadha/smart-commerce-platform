<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\StorefrontSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontProductsFilterSidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_sidebar_filter_titles_and_icons_are_admin_controlled(): void
    {
        StorefrontSetting::query()->forceCreate([
            'store_name' => ['en' => 'Smart Commerce'],
            'store_tagline' => ['en' => 'Marketplace Platform'],
            'products_categories_filter_title' => ['en' => 'Shop by Department'],
            'products_brands_filter_title' => ['en' => 'Shop by Maker'],
            'is_active' => true,
        ]);

        Category::query()->forceCreate([
            'name' => ['en' => 'Laptops'],
            'slug' => 'laptops',
            'icon' => 'categories/icons/laptop.svg',
            'is_active' => true,
            'show_in_menu' => true,
            'sort_order' => 1,
        ]);

        Brand::query()->forceCreate([
            'name' => ['en' => 'Apple'],
            'slug' => 'apple',
            'logo' => 'brands/logos/apple.svg',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get('/store/products?lang=en');

        $response->assertOk();
        $response->assertSee('Shop by Department');
        $response->assertSee('Shop by Maker');
        $response->assertSee('Laptops');
        $response->assertSee('Apple');
        $response->assertSee('storage/categories/icons/laptop.svg', false);
        $response->assertSee('storage/brands/logos/apple.svg', false);
        $response->assertSee('scp-filter-link-icon', false);
    }
}
