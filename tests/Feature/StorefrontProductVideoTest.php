<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontProductVideoTest extends TestCase
{
    use RefreshDatabase;

    public function test_enabled_youtube_video_is_rendered_with_privacy_enhanced_embed(): void
    {
        $product = Product::query()->create([
            'name' => ['ar' => 'منتج فيديو', 'he' => 'מוצר וידאו', 'en' => 'Video Product'],
            'slug' => 'video-product',
            'product_type' => 'physical',
            'status' => 'active',
            'price' => 100,
            'youtube_enabled' => true,
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'is_active' => true,
        ]);

        $this->get(route('storefront.products.show', ['slug' => $product->slug, 'lang' => 'en']))
            ->assertOk()
            ->assertSee('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', false);
    }

    public function test_disabled_youtube_video_is_not_rendered(): void
    {
        $product = Product::query()->create([
            'name' => ['ar' => 'منتج فيديو', 'he' => 'מוצר וידאו', 'en' => 'Video Product'],
            'slug' => 'video-disabled',
            'product_type' => 'physical',
            'status' => 'active',
            'price' => 100,
            'youtube_enabled' => false,
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'is_active' => true,
        ]);

        $this->get(route('storefront.products.show', ['slug' => $product->slug, 'lang' => 'en']))
            ->assertOk()
            ->assertDontSee('youtube-nocookie.com', false);
    }
}
