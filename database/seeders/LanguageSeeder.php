<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            [
                'name' => 'Arabic',
                'native_name' => 'العربية',
                'code' => 'ar',
                'direction' => 'rtl',
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Hebrew',
                'native_name' => 'עברית',
                'code' => 'he',
                'direction' => 'rtl',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'English',
                'native_name' => 'English',
                'code' => 'en',
                'direction' => 'ltr',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(
                ['code' => $language['code']],
                $language
            );
        }
    }
}