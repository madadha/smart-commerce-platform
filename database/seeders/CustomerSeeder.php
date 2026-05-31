<?php

namespace Database\Seeders;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Models\Country;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $israel = Country::query()->where('code', 'IL')->first();
        $jordan = Country::query()->where('code', 'JO')->first();

        $customers = [
            [
                'first_name' => 'محمد',
                'last_name' => 'أحمد',
                'email' => 'mohammad@example.com',
                'phone' => '+972501111111',
                'whatsapp' => '+972501111111',
                'customer_type' => CustomerType::Regular,
                'status' => CustomerStatus::Active,
                'country_id' => $israel?->id,
                'city' => 'الناصرة',
                'area' => 'الحي الشرقي',
                'street' => 'Main Street',
                'building' => '10',
                'apartment' => '2',
                'accepts_marketing' => true,
                'sort_order' => 1,
            ],
            [
                'first_name' => 'سارة',
                'last_name' => 'خالد',
                'email' => 'sara@example.com',
                'phone' => '+972502222222',
                'whatsapp' => '+972502222222',
                'customer_type' => CustomerType::Vip,
                'status' => CustomerStatus::Active,
                'country_id' => $israel?->id,
                'city' => 'حيفا',
                'area' => 'المركز',
                'street' => 'Herzl Street',
                'building' => '15',
                'apartment' => '5',
                'accepts_marketing' => true,
                'sort_order' => 2,
            ],
            [
                'first_name' => 'أحمد',
                'last_name' => 'الريسيلر',
                'email' => 'reseller@example.com',
                'phone' => '+972503333333',
                'whatsapp' => '+972503333333',
                'customer_type' => CustomerType::Reseller,
                'status' => CustomerStatus::Active,
                'country_id' => $israel?->id,
                'company_name' => 'Reseller Store',
                'tax_number' => '123456789',
                'city' => 'القدس',
                'area' => 'بيت حنينا',
                'street' => 'Market Street',
                'building' => '22',
                'internal_notes' => 'Demo reseller customer.',
                'accepts_marketing' => false,
                'sort_order' => 3,
            ],
            [
                'first_name' => 'Omar',
                'last_name' => 'Jordan',
                'email' => 'omar.jordan@example.com',
                'phone' => '+962790000000',
                'whatsapp' => '+962790000000',
                'customer_type' => CustomerType::Regular,
                'status' => CustomerStatus::Active,
                'country_id' => $jordan?->id,
                'city' => 'Amman',
                'area' => 'Downtown',
                'street' => 'Rainbow Street',
                'building' => '8',
                'accepts_marketing' => false,
                'sort_order' => 4,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::query()->updateOrCreate(
                ['email' => $customer['email']],
                [
                    ...$customer,
                    'is_active' => true,
                ]
            );
        }
    }
}