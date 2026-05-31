<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view admin panel',

            'view users',
            'create users',
            'edit users',
            'delete users',

            'view settings',
            'edit settings',

            'view products',
            'create products',
            'edit products',
            'delete products',

            'view categories',
            'create categories',
            'edit categories',
            'delete categories',

            'view orders',
            'edit orders',

            'view reports',

            'manage languages',
            'manage currencies',
            'manage shipping',
            'manage payments',
            'manage resellers',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $manager = Role::firstOrCreate([
            'name' => 'manager',
            'guard_name' => 'web',
        ]);

        $employee = Role::firstOrCreate([
            'name' => 'employee',
            'guard_name' => 'web',
        ]);

        $reseller = Role::firstOrCreate([
            'name' => 'reseller',
            'guard_name' => 'web',
        ]);

        $customer = Role::firstOrCreate([
            'name' => 'customer',
            'guard_name' => 'web',
        ]);

        $admin->syncPermissions(Permission::all());

        $manager->syncPermissions([
            'view admin panel',
            'view products',
            'create products',
            'edit products',
            'view categories',
            'create categories',
            'edit categories',
            'view orders',
            'edit orders',
            'view reports',
        ]);

        $employee->syncPermissions([
            'view admin panel',
            'view products',
            'view categories',
            'view orders',
        ]);

        $reseller->syncPermissions([
            'view products',
            'view orders',
        ]);

        $customer->syncPermissions([]);
    }
}