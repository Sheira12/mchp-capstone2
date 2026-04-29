<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        $permissions = [
            // Parishioners
            'view parishioners', 'create parishioners', 'edit parishioners', 'delete parishioners',
            // Families
            'view families', 'create families', 'edit families', 'delete families',
            // Sacramental Records
            'view records', 'create records', 'edit records', 'delete records', 'verify records',
            // Bookings
            'view bookings', 'create bookings', 'edit bookings', 'confirm bookings', 'cancel bookings',
            // Certificates
            'view certificates', 'create certificates', 'download certificates', 'release certificates',
            // Payments
            'view payments', 'record payments', 'refund payments', 'void payments', 'view reports',
            // Users
            'view users', 'create users', 'edit users', 'delete users',
            // System
            'view audit logs', 'manage settings', 'manage announcements', 'manage mass schedules',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $secretary = Role::firstOrCreate(['name' => 'parish_secretary']);
        $secretary->givePermissionTo([
            'view parishioners', 'create parishioners', 'edit parishioners',
            'view families', 'create families', 'edit families',
            'view records', 'create records', 'edit records', 'verify records',
            'view bookings', 'create bookings', 'edit bookings', 'confirm bookings', 'cancel bookings',
            'view certificates', 'create certificates', 'download certificates', 'release certificates',
            'view payments',
            'manage announcements', 'manage mass schedules',
        ]);

        $finance = Role::firstOrCreate(['name' => 'finance_officer']);
        $finance->givePermissionTo([
            'view parishioners',
            'view bookings',
            'view certificates',
            'view payments', 'record payments', 'refund payments', 'void payments', 'view reports',
        ]);

        $parishioner = Role::firstOrCreate(['name' => 'parishioner']);
        $parishioner->givePermissionTo([
            'view bookings', 'create bookings',
            'view certificates', 'download certificates',
            'view payments',
        ]);
    }
}
