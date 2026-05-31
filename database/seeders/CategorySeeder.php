<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $electronics = Category::query()->updateOrCreate(
            ['slug' => 'electronics'],
            [
                'name' => [
                    'ar' => 'إلكترونيات',
                    'he' => 'אלקטרוניקה',
                    'en' => 'Electronics',
                ],
                'description' => [
                    'ar' => 'منتجات إلكترونية وأجهزة ذكية.',
                    'he' => 'מוצרי אלקטרוניקה ומכשירים חכמים.',
                    'en' => 'Electronic products and smart devices.',
                ],
                'is_active' => true,
                'show_in_menu' => true,
                'sort_order' => 1,
            ]
        );

        $phones = Category::query()->updateOrCreate(
            ['slug' => 'phones'],
            [
                'parent_id' => $electronics->id,
                'name' => [
                    'ar' => 'هواتف',
                    'he' => 'טלפונים',
                    'en' => 'Phones',
                ],
                'description' => [
                    'ar' => 'هواتف ذكية وموبايلات.',
                    'he' => 'טלפונים חכמים ומכשירים ניידים.',
                    'en' => 'Smartphones and mobile phones.',
                ],
                'is_active' => true,
                'show_in_menu' => true,
                'sort_order' => 1,
            ]
        );

        $iphone = Category::query()->updateOrCreate(
            ['slug' => 'iphone'],
            [
                'parent_id' => $phones->id,
                'name' => [
                    'ar' => 'iPhone',
                    'he' => 'אייפון',
                    'en' => 'iPhone',
                ],
                'description' => [
                    'ar' => 'أجهزة آيفون وملحقاتها.',
                    'he' => 'מכשירי אייפון ואביזרים.',
                    'en' => 'iPhone devices and accessories.',
                ],
                'is_active' => true,
                'show_in_menu' => true,
                'sort_order' => 1,
            ]
        );

        Category::query()->updateOrCreate(
            ['slug' => 'iphone-16'],
            [
                'parent_id' => $iphone->id,
                'name' => [
                    'ar' => 'iPhone 16',
                    'he' => 'אייפון 16',
                    'en' => 'iPhone 16',
                ],
                'description' => [
                    'ar' => 'أجهزة iPhone 16 وملحقاتها.',
                    'he' => 'מכשירי iPhone 16 ואביזרים.',
                    'en' => 'iPhone 16 devices and accessories.',
                ],
                'is_active' => true,
                'show_in_menu' => true,
                'sort_order' => 1,
            ]
        );

        $digitalCards = Category::query()->updateOrCreate(
            ['slug' => 'digital-cards'],
            [
                'name' => [
                    'ar' => 'بطاقات رقمية',
                    'he' => 'כרטיסים דיגיטליים',
                    'en' => 'Digital Cards',
                ],
                'description' => [
                    'ar' => 'بطاقات شحن وأكواد رقمية.',
                    'he' => 'כרטיסי טעינה וקודים דיגיטליים.',
                    'en' => 'Recharge cards and digital codes.',
                ],
                'is_active' => true,
                'show_in_menu' => true,
                'sort_order' => 2,
            ]
        );

        $gaming = Category::query()->updateOrCreate(
            ['slug' => 'gaming'],
            [
                'parent_id' => $digitalCards->id,
                'name' => [
                    'ar' => 'ألعاب',
                    'he' => 'משחקים',
                    'en' => 'Gaming',
                ],
                'description' => [
                    'ar' => 'بطاقات وأكواد الألعاب.',
                    'he' => 'כרטיסים וקודים למשחקים.',
                    'en' => 'Gaming cards and codes.',
                ],
                'is_active' => true,
                'show_in_menu' => true,
                'sort_order' => 1,
            ]
        );

        $playstation = Category::query()->updateOrCreate(
            ['slug' => 'playstation-cards'],
            [
                'parent_id' => $gaming->id,
                'name' => [
                    'ar' => 'PlayStation',
                    'he' => 'פלייסטיישן',
                    'en' => 'PlayStation',
                ],
                'description' => [
                    'ar' => 'بطاقات وأكواد بلايستيشن.',
                    'he' => 'כרטיסים וקודים לפלייסטיישן.',
                    'en' => 'PlayStation cards and codes.',
                ],
                'is_active' => true,
                'show_in_menu' => true,
                'sort_order' => 1,
            ]
        );

        Category::query()->updateOrCreate(
            ['slug' => 'playstation-us'],
            [
                'parent_id' => $playstation->id,
                'name' => [
                    'ar' => 'PlayStation US',
                    'he' => 'פלייסטיישן ארה״ב',
                    'en' => 'PlayStation US',
                ],
                'description' => [
                    'ar' => 'بطاقات بلايستيشن أمريكية.',
                    'he' => 'כרטיסי פלייסטיישן אמריקאיים.',
                    'en' => 'US PlayStation cards.',
                ],
                'is_active' => true,
                'show_in_menu' => true,
                'sort_order' => 1,
            ]
        );
    }
}