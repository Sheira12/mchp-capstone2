<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            MassScheduleSeeder::class,
            ServiceSeeder::class,
            ServiceRequirementSeeder::class,
            ServicePackageSeeder::class,
            EligibilityRuleSeeder::class,
            DemoDataSeeder::class,
            AnalyticsDataSeeder::class,
            DemoUsersSeeder::class,
            FullDemoSeeder::class,
            CurrentMonthSeeder::class,
        ]);
    }
}
