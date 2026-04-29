<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@mhcparish.ph'],
            [
                'name'     => 'System Administrator',
                'password' => Hash::make('Admin@1234'),
                'is_active' => true,
            ]
        );
        $admin->assignRole('super_admin');

        // Parish Secretary
        $secretary = User::firstOrCreate(
            ['email' => 'secretary@mhcparish.ph'],
            [
                'name'     => 'Parish Secretary',
                'password' => Hash::make('Secretary@1234'),
                'is_active' => true,
            ]
        );
        $secretary->assignRole('parish_secretary');

        // Finance Officer
        $finance = User::firstOrCreate(
            ['email' => 'finance@mhcparish.ph'],
            [
                'name'     => 'Finance Officer',
                'password' => Hash::make('Finance@1234'),
                'is_active' => true,
            ]
        );
        $finance->assignRole('finance_officer');

        $this->command->info('Admin users created:');
        $this->command->info('  Super Admin: admin@mhcparish.ph / Admin@1234');
        $this->command->info('  Secretary:   secretary@mhcparish.ph / Secretary@1234');
        $this->command->info('  Finance:     finance@mhcparish.ph / Finance@1234');
        $this->command->warn('  ⚠ Change these passwords immediately after first login!');
    }
}
