<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductOptionsSeeder extends Seeder
{
    public function run(): void
    {
        $iphone = Product::query()->where('slug', 'iphone-16-pro-max')->first();

        if ($iphone) {
            ProductOption::query()->updateOrCreate(
                [
                    'product_id' => $iphone->id,
                    'slug' => 'color',
                ],
                [
                    'name' => [
                        'ar' => 'اللون',
                        'he' => 'צבע',
                        'en' => 'Color',
                    ],
                    'type' => 'color',
                    'values' => [
                        [
                            'ar' => 'أسود',
                            'he' => 'שחור',
                            'en' => 'Black',
                            'value' => 'black',
                            'color' => '#000000',
                        ],
                        [
                            'ar' => 'ذهبي',
                            'he' => 'זהב',
                            'en' => 'Gold',
                            'value' => 'gold',
                            'color' => '#D4AF37',
                        ],
                        [
                            'ar' => 'أبيض',
                            'he' => 'לבן',
                            'en' => 'White',
                            'value' => 'white',
                            'color' => '#FFFFFF',
                        ],
                    ],
                    'is_required' => true,
                    'is_active' => true,
                    'sort_order' => 1,
                ]
            );

            ProductOption::query()->updateOrCreate(
                [
                    'product_id' => $iphone->id,
                    'slug' => 'storage',
                ],
                [
                    'name' => [
                        'ar' => 'التخزين',
                        'he' => 'אחסון',
                        'en' => 'Storage',
                    ],
                    'type' => 'select',
                    'values' => [
                        [
                            'ar' => '256GB',
                            'he' => '256GB',
                            'en' => '256GB',
                            'value' => '256gb',
                        ],
                        [
                            'ar' => '512GB',
                            'he' => '512GB',
                            'en' => '512GB',
                            'value' => '512gb',
                        ],
                        [
                            'ar' => '1TB',
                            'he' => '1TB',
                            'en' => '1TB',
                            'value' => '1tb',
                        ],
                    ],
                    'is_required' => true,
                    'is_active' => true,
                    'sort_order' => 2,
                ]
            );

            ProductVariant::query()->updateOrCreate(
                ['sku' => 'IPHONE-16-PM-BLK-256'],
                [
                    'product_id' => $iphone->id,
                    'name' => [
                        'ar' => 'iPhone 16 Pro Max أسود 256GB',
                        'he' => 'iPhone 16 Pro Max שחור 256GB',
                        'en' => 'iPhone 16 Pro Max Black 256GB',
                    ],
                    'option_values' => [
                        'color' => 'black',
                        'storage' => '256gb',
                    ],
                    'price' => 4999,
                    'sale_price' => 4699,
                    'track_stock' => true,
                    'stock_quantity' => 5,
                    'min_stock_quantity' => 1,
                    'is_default' => true,
                    'is_active' => true,
                    'sort_order' => 1,
                ]
            );

            ProductVariant::query()->updateOrCreate(
                ['sku' => 'IPHONE-16-PM-BLK-512'],
                [
                    'product_id' => $iphone->id,
                    'name' => [
                        'ar' => 'iPhone 16 Pro Max أسود 512GB',
                        'he' => 'iPhone 16 Pro Max שחור 512GB',
                        'en' => 'iPhone 16 Pro Max Black 512GB',
                    ],
                    'option_values' => [
                        'color' => 'black',
                        'storage' => '512gb',
                    ],
                    'price' => 5499,
                    'sale_price' => null,
                    'track_stock' => true,
                    'stock_quantity' => 3,
                    'min_stock_quantity' => 1,
                    'is_default' => false,
                    'is_active' => true,
                    'sort_order' => 2,
                ]
            );

            ProductVariant::query()->updateOrCreate(
                ['sku' => 'IPHONE-16-PM-GLD-256'],
                [
                    'product_id' => $iphone->id,
                    'name' => [
                        'ar' => 'iPhone 16 Pro Max ذهبي 256GB',
                        'he' => 'iPhone 16 Pro Max זהב 256GB',
                        'en' => 'iPhone 16 Pro Max Gold 256GB',
                    ],
                    'option_values' => [
                        'color' => 'gold',
                        'storage' => '256gb',
                    ],
                    'price' => 5099,
                    'sale_price' => null,
                    'track_stock' => true,
                    'stock_quantity' => 2,
                    'min_stock_quantity' => 1,
                    'is_default' => false,
                    'is_active' => true,
                    'sort_order' => 3,
                ]
            );
        }
    }
}