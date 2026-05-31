<?php

namespace Database\Seeders;

use App\Enums\ShippingMethodType;
use App\Models\Country;
use App\Models\Currency;
use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    public function run(): void
    {
        $israel = Country::query()->where('code', 'IL')->first();
        $ils = Currency::query()->where('code', 'ILS')->first();

        $methods = [
            [
                'slug' => 'home-delivery',
                'name' => [
                    'ar' => 'توصيل للبيت',
                    'he' => 'משלוח עד הבית',
                    'en' => 'Home Delivery',
                ],
                'description' => [
                    'ar' => 'توصيل الطلب إلى عنوان الزبون.',
                    'he' => 'משלוח ההזמנה לכתובת הלקוח.',
                    'en' => 'Delivery to customer address.',
                ],
                'type' => ShippingMethodType::HomeDelivery,
                'base_cost' => 30,
                'free_shipping_min_total' => 500,
                'min_delivery_days' => 2,
                'max_delivery_days' => 4,
                'is_default' => true,
                'sort_order' => 1,
            ],
            [
                'slug' => 'pickup',
                'name' => [
                    'ar' => 'استلام ذاتي',
                    'he' => 'איסוף עצמי',
                    'en' => 'Pickup',
                ],
                'description' => [
                    'ar' => 'استلام الطلب من نقطة الاستلام.',
                    'he' => 'איסוף ההזמנה מנקודת איסוף.',
                    'en' => 'Pickup from store or pickup point.',
                ],
                'type' => ShippingMethodType::Pickup,
                'base_cost' => 0,
                'free_shipping_min_total' => null,
                'min_delivery_days' => 0,
                'max_delivery_days' => 1,
                'is_default' => false,
                'sort_order' => 2,
            ],
            [
                'slug' => 'express-delivery',
                'name' => [
                    'ar' => 'توصيل سريع',
                    'he' => 'משלוח מהיר',
                    'en' => 'Express Delivery',
                ],
                'description' => [
                    'ar' => 'توصيل سريع خلال وقت قصير.',
                    'he' => 'משלוח מהיר בזמן קצר.',
                    'en' => 'Fast express delivery.',
                ],
                'type' => ShippingMethodType::Express,
                'base_cost' => 60,
                'free_shipping_min_total' => 1000,
                'min_delivery_days' => 1,
                'max_delivery_days' => 2,
                'is_default' => false,
                'sort_order' => 3,
            ],
            [
                'slug' => 'standard-delivery',
                'name' => [
                    'ar' => 'توصيل عادي',
                    'he' => 'משלוח רגיל',
                    'en' => 'Standard Delivery',
                ],
                'description' => [
                    'ar' => 'توصيل عادي بتكلفة مناسبة.',
                    'he' => 'משלוח רגיל במחיר נוח.',
                    'en' => 'Standard delivery with affordable cost.',
                ],
                'type' => ShippingMethodType::Standard,
                'base_cost' => 20,
                'free_shipping_min_total' => 400,
                'min_delivery_days' => 3,
                'max_delivery_days' => 7,
                'is_default' => false,
                'sort_order' => 4,
            ],
            [
                'slug' => 'free-delivery',
                'name' => [
                    'ar' => 'توصيل مجاني',
                    'he' => 'משלוח חינם',
                    'en' => 'Free Delivery',
                ],
                'description' => [
                    'ar' => 'توصيل مجاني للطلبات المؤهلة.',
                    'he' => 'משלוח חינם להזמנות מתאימות.',
                    'en' => 'Free delivery for eligible orders.',
                ],
                'type' => ShippingMethodType::Free,
                'base_cost' => 0,
                'free_shipping_min_total' => 0,
                'min_delivery_days' => 3,
                'max_delivery_days' => 7,
                'is_default' => false,
                'sort_order' => 5,
            ],
            [
                'slug' => 'external-company-delivery',
                'name' => [
                    'ar' => 'شركة توصيل خارجية',
                    'he' => 'חברת משלוחים חיצונית',
                    'en' => 'External Shipping Company',
                ],
                'description' => [
                    'ar' => 'توصيل عن طريق شركة خارجية.',
                    'he' => 'משלוח באמצעות חברה חיצונית.',
                    'en' => 'Delivery through an external shipping company.',
                ],
                'type' => ShippingMethodType::ExternalCompany,
                'base_cost' => 45,
                'free_shipping_min_total' => 800,
                'min_delivery_days' => 2,
                'max_delivery_days' => 5,
                'external_company_name' => 'Demo Shipping Company',
                'external_company_phone' => '+972000000000',
                'external_company_website' => 'https://example.com',
                'is_default' => false,
                'sort_order' => 6,
            ],
        ];

        foreach ($methods as $method) {
            ShippingMethod::query()->updateOrCreate(
                ['slug' => $method['slug']],
                [
                    ...$method,
                    'country_id' => $israel?->id,
                    'currency_id' => $ils?->id,
                    'is_active' => true,
                ]
            );
        }
    }
}