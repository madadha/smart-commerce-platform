<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $ils = Currency::query()->where('code', 'ILS')->first();
        $apple = Brand::query()->where('slug', 'apple')->first();
        $sony = Brand::query()->where('slug', 'sony')->first();
        $playstationBrand = Brand::query()->where('slug', 'playstation')->first();
        $supplier = Company::query()->where('slug', 'main-supplier')->first();

        $iphone16Category = Category::query()->where('slug', 'iphone-16')->first();
        $playstationUsCategory = Category::query()->where('slug', 'playstation-us')->first();

        $iphone = Product::query()->updateOrCreate(
            ['slug' => 'iphone-16-pro-max'],
            [
                'name' => [
                    'ar' => 'iPhone 16 Pro Max',
                    'he' => 'אייפון 16 פרו מקס',
                    'en' => 'iPhone 16 Pro Max',
                ],
                'short_description' => [
                    'ar' => 'هاتف ذكي قوي من آبل.',
                    'he' => 'טלפון חכם חזק מבית אפל.',
                    'en' => 'Powerful smartphone from Apple.',
                ],
                'description' => [
                    'ar' => 'هاتف iPhone 16 Pro Max بمواصفات عالية وتصميم فاخر.',
                    'he' => 'iPhone 16 Pro Max עם מפרט גבוה ועיצוב יוקרתי.',
                    'en' => 'iPhone 16 Pro Max with premium design and high performance.',
                ],
                'sku' => 'IPHONE-16-PM',
                'product_type' => ProductType::Physical,
                'status' => ProductStatus::Active,
                'brand_id' => $apple?->id,
                'company_id' => $supplier?->id,
                'currency_id' => $ils?->id,
                'price' => 4999,
                'sale_price' => 4699,
                'track_stock' => true,
                'stock_quantity' => 10,
                'min_stock_quantity' => 2,
                'requires_shipping' => true,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        if ($iphone16Category) {
            $iphone->categories()->syncWithoutDetaching([$iphone16Category->id]);
        }

        $psCard = Product::query()->updateOrCreate(
            ['slug' => 'playstation-store-card-50-us'],
            [
                'name' => [
                    'ar' => 'بطاقة PlayStation Store 50$ US',
                    'he' => 'כרטיס PlayStation Store 50$ US',
                    'en' => 'PlayStation Store Card $50 US',
                ],
                'short_description' => [
                    'ar' => 'بطاقة رقمية أمريكية لمتجر بلايستيشن.',
                    'he' => 'כרטיס דיגיטלי אמריקאי לחנות פלייסטיישן.',
                    'en' => 'US digital card for PlayStation Store.',
                ],
                'description' => [
                    'ar' => 'كود رقمي يتم تسليمه بعد إتمام الطلب.',
                    'he' => 'קוד דיגיטלי הנמסר לאחר השלמת ההזמנה.',
                    'en' => 'Digital code delivered after order completion.',
                ],
                'sku' => 'PSN-50-US',
                'product_type' => ProductType::DigitalCard,
                'status' => ProductStatus::Active,
                'brand_id' => $playstationBrand?->id ?? $sony?->id,
                'company_id' => $supplier?->id,
                'currency_id' => $ils?->id,
                'price' => 190,
                'sale_price' => null,
                'track_stock' => true,
                'stock_quantity' => 25,
                'min_stock_quantity' => 5,
                'requires_shipping' => false,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        if ($playstationUsCategory) {
            $psCard->categories()->syncWithoutDetaching([$playstationUsCategory->id]);
        }
    }
}