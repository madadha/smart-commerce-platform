<?php

namespace Database\Seeders;

use App\Enums\CouponDiscountType;
use App\Models\Coupon;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $ils = Currency::query()->where('code', 'ILS')->first();

        $coupons = [
            [
                'code' => 'WELCOME10',
                'name' => [
                    'ar' => 'خصم ترحيبي 10%',
                    'he' => 'הנחת ברוכים הבאים 10%',
                    'en' => 'Welcome 10% Discount',
                ],
                'description' => [
                    'ar' => 'خصم 10% للزبائن الجدد.',
                    'he' => 'הנחה של 10% ללקוחות חדשים.',
                    'en' => '10% discount for new customers.',
                ],
                'discount_type' => CouponDiscountType::Percentage,
                'discount_value' => 10,
                'minimum_order_total' => 100,
                'maximum_discount_amount' => 150,
                'usage_limit' => 100,
                'usage_limit_per_customer' => 1,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addMonths(3),
                'sort_order' => 1,
            ],
            [
                'code' => 'SALE50',
                'name' => [
                    'ar' => 'خصم 50 شيكل',
                    'he' => 'הנחה 50 ש״ח',
                    'en' => '50 ILS Discount',
                ],
                'description' => [
                    'ar' => 'خصم ثابت بقيمة 50 شيكل.',
                    'he' => 'הנחה קבועה בסך 50 ש״ח.',
                    'en' => 'Fixed discount of 50 ILS.',
                ],
                'discount_type' => CouponDiscountType::FixedAmount,
                'discount_value' => 50,
                'minimum_order_total' => 300,
                'maximum_discount_amount' => null,
                'usage_limit' => 50,
                'usage_limit_per_customer' => 2,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addMonth(),
                'sort_order' => 2,
            ],
            [
                'code' => 'FREESHIP',
                'name' => [
                    'ar' => 'شحن مجاني',
                    'he' => 'משלוח חינם',
                    'en' => 'Free Shipping',
                ],
                'description' => [
                    'ar' => 'كوبون شحن مجاني للطلبات المؤهلة.',
                    'he' => 'קופון משלוח חינם להזמנות מתאימות.',
                    'en' => 'Free shipping coupon for eligible orders.',
                ],
                'discount_type' => CouponDiscountType::FreeShipping,
                'discount_value' => 0,
                'minimum_order_total' => 200,
                'maximum_discount_amount' => null,
                'usage_limit' => 200,
                'usage_limit_per_customer' => 3,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addMonths(2),
                'sort_order' => 3,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::query()->updateOrCreate(
                ['code' => $coupon['code']],
                [
                    ...$coupon,
                    'currency_id' => $ils?->id,
                    'used_count' => 0,
                    'is_active' => true,
                ]
            );
        }
    }
}