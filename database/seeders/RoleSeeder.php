<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $vendor = Role::firstOrCreate(['name' => 'vendor']);
        $customer = Role::firstOrCreate(['name' => 'customer']);

        // Create permissions
        $permissions = [
            'manage-products',
            'manage-orders',
            'manage-users',
            'view-reports'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $admin->givePermissionTo(Permission::all());
        $vendor->givePermissionTo(['manage-products', 'manage-orders']);
        // Customer gets no additional permissions
    }
}