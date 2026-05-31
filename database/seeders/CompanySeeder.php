<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Country;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $israel = Country::query()->where('code', 'IL')->first();

        $companies = [
            [
                'name' => ['ar' => 'المورد الرئيسي', 'he' => 'הספק הראשי', 'en' => 'Main Supplier'],
                'slug' => 'main-supplier',
                'type' => 'supplier',
                'email' => 'supplier@example.com',
                'phone' => '+972000000000',
                'country_id' => $israel?->id,
                'sort_order' => 1,
            ],
            [
                'name' => ['ar' => 'شريك تجاري', 'he' => 'שותף עסקי', 'en' => 'Business Partner'],
                'slug' => 'business-partner',
                'type' => 'partner',
                'email' => 'partner@example.com',
                'phone' => '+972000000001',
                'country_id' => $israel?->id,
                'sort_order' => 2,
            ],
            [
                'name' => ['ar' => 'مزود خدمات', 'he' => 'ספק שירותים', 'en' => 'Service Provider'],
                'slug' => 'service-provider',
                'type' => 'service_provider',
                'email' => 'service@example.com',
                'phone' => '+972000000002',
                'country_id' => $israel?->id,
                'sort_order' => 3,
            ],
        ];

        foreach ($companies as $company) {
            Company::query()->updateOrCreate(
                ['slug' => $company['slug']],
                [
                    'name' => $company['name'],
                    'type' => $company['type'],
                    'email' => $company['email'],
                    'phone' => $company['phone'],
                    'country_id' => $company['country_id'],
                    'is_active' => true,
                    'sort_order' => $company['sort_order'],
                ]
            );
        }
    }
}