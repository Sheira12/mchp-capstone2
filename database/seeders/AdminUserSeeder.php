<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Super Admin ──────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'maryhelpparish@gmail.com'],
            [
                'name'      => 'System Administrator',
                'password'  => Hash::make('Admin@1234'),
                'is_active' => true,
            ]
        );
        // Force password & active in case record already existed
        $admin->update(['password' => Hash::make('Admin@1234'), 'is_active' => true]);
        $admin->syncRoles(['super_admin']);

        // ── Parish Secretary ─────────────────────────────────
        $secretary = User::firstOrCreate(
            ['email' => 'cumpioaries07@gmail.com'],
            [
                'name'      => 'Parish Secretary',
                'password'  => Hash::make('Secretary@1234'),
                'is_active' => true,
            ]
        );
        $secretary->update(['password' => Hash::make('Secretary@1234'), 'is_active' => true]);
        $secretary->syncRoles(['parish_secretary']);

        // ── Finance Officer ──────────────────────────────────
        $finance = User::firstOrCreate(
            ['email' => 'financemhcpparish@gmail.com'],   // ← no trailing newline
            [
                'name'      => 'Finance Officer',
                'password'  => Hash::make('Finance@1234'),
                'is_active' => true,
            ]
        );
        $finance->update(['password' => Hash::make('Finance@1234'), 'is_active' => true]);
        $finance->syncRoles(['finance_officer']);

        $this->command->info('');
        $this->command->info('✅ Admin accounts ready:');
        $this->command->info('  Super Admin  : maryhelpparish@gmail.com  / Admin@1234');
        $this->command->info('  Secretary    : cumpioaries07@gmail.com   / Secretary@1234');
        $this->command->info('  Finance      : financemhcpparish@gmail.com / Finance@1234');
        $this->command->warn('  ⚠  Change these passwords after first login!');
    }
}
