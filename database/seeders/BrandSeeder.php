<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            [
                'name' => [
                    'ar' => 'آبل',
                    'he' => 'אפל',
                    'en' => 'Apple',
                ],
                'slug' => 'apple',
                'website_url' => 'https://www.apple.com',
                'sort_order' => 1,
            ],
            [
                'name' => [
                    'ar' => 'سامسونج',
                    'he' => 'סמסונג',
                    'en' => 'Samsung',
                ],
                'slug' => 'samsung',
                'website_url' => 'https://www.samsung.com',
                'sort_order' => 2,
            ],
            [
                'name' => [
                    'ar' => 'سوني',
                    'he' => 'סוני',
                    'en' => 'Sony',
                ],
                'slug' => 'sony',
                'website_url' => 'https://www.sony.com',
                'sort_order' => 3,
            ],
            [
                'name' => [
                    'ar' => 'بلايستيشن',
                    'he' => 'פלייסטיישן',
                    'en' => 'PlayStation',
                ],
                'slug' => 'playstation',
                'website_url' => 'https://www.playstation.com',
                'sort_order' => 4,
            ],
            [
                'name' => [
                    'ar' => 'إكس بوكس',
                    'he' => 'אקס בוקס',
                    'en' => 'Xbox',
                ],
                'slug' => 'xbox',
                'website_url' => 'https://www.xbox.com',
                'sort_order' => 5,
            ],
            [
                'name' => [
                    'ar' => 'نينتندو',
                    'he' => 'נינטנדו',
                    'en' => 'Nintendo',
                ],
                'slug' => 'nintendo',
                'website_url' => 'https://www.nintendo.com',
                'sort_order' => 6,
            ],
        ];

        foreach ($brands as $brand) {
            Brand::query()->updateOrCreate(
                [
                    'slug' => $brand['slug'],
                ],
                [
                    'name' => $brand['name'],
                    'website_url' => $brand['website_url'],
                    'is_active' => true,
                    'sort_order' => $brand['sort_order'],
                ]
            );
        }
    }
}