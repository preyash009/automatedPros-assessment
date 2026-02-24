<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = ['events', 'tickets', 'bookings', 'payments', 'roles', 'permissions'];
        $actions = ['manage', 'create', 'edit', 'delete', 'view'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => $action . '_' . $module,
                    'guard_name' => 'api'
                ]);
            }
        }

        // Create Roles
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $organizer = Role::firstOrCreate(['name' => 'organizer', 'guard_name' => 'api']);
        $customer = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'api']);

        $admin->syncPermissions(Permission::where('guard_name', 'api')->get());

        $organizer->syncPermissions([
            'manage_events',
            'create_events',
            'edit_events',
            'delete_events',
            'view_events',
            'manage_tickets',
            'create_tickets',
            'edit_tickets',
            'delete_tickets',
            'view_tickets',
            'view_bookings',
            'delete_bookings',
            'view_payments',
        ]);

        // Assign Permissions to Customer
        $customer->syncPermissions([
            'view_events',
            'view_tickets',
            'create_bookings',
            'delete_bookings',
            'view_bookings',
            'view_payments'
        ]);
    }
}
