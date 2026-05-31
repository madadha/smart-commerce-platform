<?php

namespace Database\Seeders;

use App\Enums\DigitalCodeStatus;
use App\Models\Product;
use App\Models\ProductDigitalCode;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductDigitalCodeSeeder extends Seeder
{
    public function run(): void
    {
        $psCard = Product::query()
            ->where('slug', 'playstation-store-card-50-us')
            ->first();

        if ($psCard) {
            $codes = [
                'PSN-US-50-AAAA-BBBB-0001',
                'PSN-US-50-AAAA-BBBB-0002',
                'PSN-US-50-AAAA-BBBB-0003',
                'PSN-US-50-AAAA-BBBB-0004',
                'PSN-US-50-AAAA-BBBB-0005',
            ];

            foreach ($codes as $index => $code) {
                ProductDigitalCode::query()->updateOrCreate(
                    ['code' => $code],
                    [
                        'product_id' => $psCard->id,
                        'product_variant_id' => null,
                        'status' => DigitalCodeStatus::Available,
                        'source' => 'manual',
                        'expires_at' => now()->addYear(),
                        'internal_notes' => 'Demo PlayStation digital code.',
                        'is_active' => true,
                        'sort_order' => $index + 1,
                    ]
                );
            }
        }

        $iphone = Product::query()
            ->where('slug', 'iphone-16-pro-max')
            ->first();

        if ($iphone) {
            $variant = ProductVariant::query()
                ->where('sku', 'IPHONE-16-PM-BLK-256')
                ->first();

            if ($variant) {
                ProductDigitalCode::query()->updateOrCreate(
                    ['code' => 'WARRANTY-IPHONE-BLK-256-0001'],
                    [
                        'product_id' => $iphone->id,
                        'product_variant_id' => $variant->id,
                        'status' => DigitalCodeStatus::Available,
                        'source' => 'manual',
                        'expires_at' => now()->addMonths(24),
                        'internal_notes' => 'Example warranty/service digital code for variant.',
                        'is_active' => true,
                        'sort_order' => 1,
                    ]
                );
            }
        }
    }
}