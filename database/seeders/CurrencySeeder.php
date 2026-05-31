<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'name' => ['ar' => 'شيكل إسرائيلي', 'he' => 'שקל ישראלי', 'en' => 'Israeli Shekel'],
                'code' => 'ILS',
                'symbol' => '₪',
                'country_code' => 'IL',
                'exchange_rate' => 1,
                'symbol_position' => 'before',
                'decimal_places' => 2,
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => ['ar' => 'دينار أردني', 'he' => 'דינר ירדני', 'en' => 'Jordanian Dinar'],
                'code' => 'JOD',
                'symbol' => 'د.أ',
                'country_code' => 'JO',
                'exchange_rate' => 0.19,
                'symbol_position' => 'after',
                'decimal_places' => 2,
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => ['ar' => 'درهم إماراتي', 'he' => 'דירהם איחוד האמירויות', 'en' => 'UAE Dirham'],
                'code' => 'AED',
                'symbol' => 'د.إ',
                'country_code' => 'AE',
                'exchange_rate' => 1.02,
                'symbol_position' => 'after',
                'decimal_places' => 2,
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => ['ar' => 'جنيه مصري', 'he' => 'לירה מצרית', 'en' => 'Egyptian Pound'],
                'code' => 'EGP',
                'symbol' => 'ج.م',
                'country_code' => 'EG',
                'exchange_rate' => 13.50,
                'symbol_position' => 'after',
                'decimal_places' => 2,
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => ['ar' => 'دولار أمريكي', 'he' => 'דולר אמריקאי', 'en' => 'US Dollar'],
                'code' => 'USD',
                'symbol' => '$',
                'country_code' => 'US',
                'exchange_rate' => 0.28,
                'symbol_position' => 'before',
                'decimal_places' => 2,
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => ['ar' => 'ريال سعودي', 'he' => 'ריאל סעודי', 'en' => 'Saudi Riyal'],
                'code' => 'SAR',
                'symbol' => 'ر.س',
                'country_code' => 'SA',
                'exchange_rate' => 1.05,
                'symbol_position' => 'after',
                'decimal_places' => 2,
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}