<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            [
                'name' => ['ar' => 'إسرائيل', 'he' => 'ישראל', 'en' => 'Israel'],
                'code' => 'IL',
                'currency_code' => 'ILS',
                'phone_code' => '+972',
                'flag' => '🇮🇱',
                'sort_order' => 1,
            ],
            [
                'name' => ['ar' => 'الأردن', 'he' => 'ירדן', 'en' => 'Jordan'],
                'code' => 'JO',
                'currency_code' => 'JOD',
                'phone_code' => '+962',
                'flag' => '🇯🇴',
                'sort_order' => 2,
            ],
            [
                'name' => ['ar' => 'الإمارات', 'he' => 'איחוד האמירויות', 'en' => 'United Arab Emirates'],
                'code' => 'AE',
                'currency_code' => 'AED',
                'phone_code' => '+971',
                'flag' => '🇦🇪',
                'sort_order' => 3,
            ],
            [
                'name' => ['ar' => 'مصر', 'he' => 'מצרים', 'en' => 'Egypt'],
                'code' => 'EG',
                'currency_code' => 'EGP',
                'phone_code' => '+20',
                'flag' => '🇪🇬',
                'sort_order' => 4,
            ],
            [
                'name' => ['ar' => 'الولايات المتحدة', 'he' => 'ארצות הברית', 'en' => 'United States'],
                'code' => 'US',
                'currency_code' => 'USD',
                'phone_code' => '+1',
                'flag' => '🇺🇸',
                'sort_order' => 5,
            ],
            [
                'name' => ['ar' => 'السعودية', 'he' => 'ערב הסעודית', 'en' => 'Saudi Arabia'],
                'code' => 'SA',
                'currency_code' => 'SAR',
                'phone_code' => '+966',
                'flag' => '🇸🇦',
                'sort_order' => 6,
            ],
        ];

        foreach ($countries as $country) {
            $currency = Currency::where('code', $country['currency_code'])->first();

            Country::updateOrCreate(
                ['code' => $country['code']],
                [
                    'name' => $country['name'],
                    'currency_id' => $currency?->id,
                    'phone_code' => $country['phone_code'],
                    'flag' => $country['flag'],
                    'is_active' => true,
                    'sort_order' => $country['sort_order'],
                ]
            );
        }
    }
}