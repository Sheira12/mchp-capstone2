<?php

namespace Database\Seeders;

use App\Models\MassSchedule;
use Illuminate\Database\Seeder;

class MassScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = [
            // Weekdays
            ['day_of_week' => 1, 'time' => '06:00:00', 'language' => 'Filipino'],
            ['day_of_week' => 1, 'time' => '18:00:00', 'language' => 'Filipino'],
            ['day_of_week' => 2, 'time' => '06:00:00', 'language' => 'Filipino'],
            ['day_of_week' => 2, 'time' => '18:00:00', 'language' => 'Filipino'],
            ['day_of_week' => 3, 'time' => '06:00:00', 'language' => 'Filipino'],
            ['day_of_week' => 3, 'time' => '18:00:00', 'language' => 'Filipino'],
            ['day_of_week' => 4, 'time' => '06:00:00', 'language' => 'Filipino'],
            ['day_of_week' => 4, 'time' => '18:00:00', 'language' => 'Filipino'],
            ['day_of_week' => 5, 'time' => '06:00:00', 'language' => 'Filipino'],
            ['day_of_week' => 5, 'time' => '18:00:00', 'language' => 'Filipino'],
            // Saturday
            ['day_of_week' => 6, 'time' => '06:00:00', 'language' => 'Filipino'],
            ['day_of_week' => 6, 'time' => '18:00:00', 'language' => 'Filipino'],
            // Sunday
            ['day_of_week' => 0, 'time' => '06:00:00', 'language' => 'Filipino'],
            ['day_of_week' => 0, 'time' => '08:00:00', 'language' => 'Filipino'],
            ['day_of_week' => 0, 'time' => '10:00:00', 'language' => 'English'],
            ['day_of_week' => 0, 'time' => '18:00:00', 'language' => 'Filipino'],
        ];

        foreach ($schedules as $schedule) {
            MassSchedule::firstOrCreate(
                ['day_of_week' => $schedule['day_of_week'], 'time' => $schedule['time']],
                array_merge($schedule, ['is_active' => true])
            );
        }
    }
}
