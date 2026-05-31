<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'group' => 'general',
                'key' => 'site_name',
                'value' => 'Smart Commerce Platform',
                'type' => 'text',
                'is_public' => true,
                'sort_order' => 1,
            ],
            [
                'group' => 'general',
                'key' => 'site_description',
                'value' => 'AI-ready multilingual modular e-commerce platform.',
                'type' => 'textarea',
                'is_public' => true,
                'sort_order' => 2,
            ],
            [
                'group' => 'general',
                'key' => 'support_email',
                'value' => 'support@example.com',
                'type' => 'email',
                'is_public' => true,
                'sort_order' => 3,
            ],
            [
                'group' => 'general',
                'key' => 'whatsapp_number',
                'value' => '+972000000000',
                'type' => 'text',
                'is_public' => true,
                'sort_order' => 4,
            ],
            [
                'group' => 'store',
                'key' => 'enable_guest_checkout',
                'value' => 'true',
                'type' => 'boolean',
                'is_public' => false,
                'sort_order' => 1,
            ],
            [
                'group' => 'store',
                'key' => 'enable_reseller_register',
                'value' => 'false',
                'type' => 'boolean',
                'is_public' => false,
                'sort_order' => 2,
            ],
            [
                'group' => 'theme',
                'key' => 'primary_color',
                'value' => '#0F172A',
                'type' => 'color',
                'is_public' => true,
                'sort_order' => 1,
            ],
            [
                'group' => 'theme',
                'key' => 'secondary_color',
                'value' => '#2563EB',
                'type' => 'color',
                'is_public' => true,
                'sort_order' => 2,
            ],
            [
                'group' => 'theme',
                'key' => 'accent_color',
                'value' => '#F97316',
                'type' => 'color',
                'is_public' => true,
                'sort_order' => 3,
            ],
            [
                'group' => 'language',
                'key' => 'default_language',
                'value' => 'ar',
                'type' => 'text',
                'is_public' => true,
                'sort_order' => 1,
            ],
            [
                'group' => 'currency',
                'key' => 'default_currency',
                'value' => 'ILS',
                'type' => 'text',
                'is_public' => true,
                'sort_order' => 1,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::query()->updateOrCreate(
                [
                    'group' => $setting['group'],
                    'key' => $setting['key'],
                ],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'is_public' => $setting['is_public'],
                    'is_active' => true,
                    'sort_order' => $setting['sort_order'],
                ]
            );
        }
    }
}