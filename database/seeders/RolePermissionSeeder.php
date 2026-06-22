<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view admin panel',
            'catalog.view', 'catalog.manage',
            'orders.view', 'orders.manage',
            'customers.view', 'customers.manage',
            'shipping.view', 'shipping.manage',
            'payments.view', 'payments.manage', 'payments.refund',
            'digital_codes.view', 'digital_codes.manage',
            'settings.view', 'settings.manage',
            'support.view', 'support.manage',
            'users.view', 'users.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $roles = [
            'super-admin' => $permissions,
            'admin' => $permissions,
            'orders-manager' => ['view admin panel', 'orders.view', 'orders.manage', 'customers.view', 'customers.manage', 'shipping.view', 'shipping.manage', 'payments.view', 'digital_codes.view', 'support.view', 'support.manage'],
            'catalog-manager' => ['view admin panel', 'catalog.view', 'catalog.manage', 'digital_codes.view', 'digital_codes.manage', 'settings.view'],
            'support' => ['view admin panel', 'orders.view', 'customers.view', 'shipping.view', 'payments.view', 'support.view', 'support.manage'],
            'customer' => [],
            'reseller' => [],
        ];

        foreach ($roles as $name => $rolePermissions) {
            Role::firstOrCreate(['name' => $name, 'guard_name' => 'web'])
                ->syncPermissions($rolePermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
