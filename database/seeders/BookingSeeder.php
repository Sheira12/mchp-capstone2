<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\SacramentalRecord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        // Clear bookings for July–August 2026 only (keep older data)
        Booking::whereBetween('scheduled_date', ['2026-07-01', '2026-08-31'])->forceDelete();
        SacramentalRecord::whereBetween('date_administered', ['2026-07-01', '2026-08-31'])->delete();

        $adminId = DB::table('users')->where('email', 'maryhelpparish@gmail.com')->value('id') ?? 1;

        // Get parishioner IDs
        $pids = \App\Models\Parishioner::pluck('id')->toArray();
        $pick = fn($n) => collect($pids)->shuffle()->take($n)->values()->toArray();

        // ── JULY 2026 BOOKINGS ─────────────────────────────────────────────────────
        $julyBookings = [
            // Baptisms (8)
            ['baptism',           '2026-07-05', '09:00', 'completed', 1500.00, $pick(1)[0]],
            ['baptism',           '2026-07-05', '10:00', 'completed', 1500.00, $pick(1)[0]],
            ['baptism',           '2026-07-12', '09:00', 'completed', 1500.00, $pick(1)[0]],
            ['baptism',           '2026-07-12', '10:30', 'completed', 1500.00, $pick(1)[0]],
            ['baptism',           '2026-07-19', '09:00', 'completed', 1500.00, $pick(1)[0]],
            ['baptism',           '2026-07-19', '11:00', 'confirmed', 1500.00, $pick(1)[0]],
            ['baptism',           '2026-07-26', '09:00', 'confirmed', 1500.00, $pick(1)[0]],
            ['baptism',           '2026-07-26', '10:00', 'pending',   1500.00, $pick(1)[0]],

            // Weddings (3)
            ['wedding',           '2026-07-11', '10:00', 'completed', 8000.00, $pick(1)[0]],
            ['wedding',           '2026-07-18', '14:00', 'confirmed', 8000.00, $pick(1)[0]],
            ['wedding',           '2026-07-25', '10:00', 'pending',   8000.00, $pick(1)[0]],

            // Funeral Masses (2)
            ['funeral_mass',      '2026-07-08', '09:00', 'completed', 3500.00, $pick(1)[0]],
            ['funeral_mass',      '2026-07-20', '08:00', 'completed', 3500.00, $pick(1)[0]],

            // Pre-Baptismal Seminar (2)
            ['pre_baptismal',     '2026-07-10', '14:00', 'completed', 150.00,  $pick(1)[0]],
            ['pre_baptismal',     '2026-07-24', '14:00', 'confirmed', 150.00,  $pick(1)[0]],

            // Pre-Marriage Seminar (1)
            ['pre_marriage',      '2026-07-13', '08:00', 'completed', 500.00,  $pick(1)[0]],

            // Confirmation Catechesis (1)
            ['confirmation_catechesis', '2026-07-07', '15:00', 'completed', 0.00, $pick(1)[0]],

            // House Blessings (4)
            ['house_blessing',    '2026-07-04', '10:00', 'completed', 1500.00, $pick(1)[0]],
            ['house_blessing',    '2026-07-09', '09:00', 'completed', 1500.00, $pick(1)[0]],
            ['house_blessing',    '2026-07-17', '14:00', 'completed', 1500.00, $pick(1)[0]],
            ['house_blessing',    '2026-07-23', '10:00', 'confirmed', 1500.00, $pick(1)[0]],

            // Car Blessings (2)
            ['car_blessing',      '2026-07-06', '08:00', 'completed', 500.00,  $pick(1)[0]],
            ['car_blessing',      '2026-07-15', '08:00', 'completed', 500.00,  $pick(1)[0]],

            // Sick Calls (2)
            ['sick_call',         '2026-07-02', '16:00', 'completed', 0.00,    $pick(1)[0]],
            ['sick_call',         '2026-07-16', '15:00', 'completed', 0.00,    $pick(1)[0]],

            // Mass Intentions (5)
            ['mass_intention',    '2026-07-01', '06:00', 'completed', 500.00,  $pick(1)[0]],
            ['mass_intention',    '2026-07-08', '06:00', 'completed', 500.00,  $pick(1)[0]],
            ['mass_intention',    '2026-07-15', '06:00', 'completed', 500.00,  $pick(1)[0]],
            ['mass_intention',    '2026-07-22', '06:00', 'confirmed', 500.00,  $pick(1)[0]],
            ['mass_intention',    '2026-07-29', '06:00', 'pending',   500.00,  $pick(1)[0]],

            // Cancelled
            ['baptism',           '2026-07-14', '09:00', 'cancelled', 1500.00, $pick(1)[0]],
            ['wedding',           '2026-07-21', '10:00', 'cancelled', 8000.00, $pick(1)[0]],
        ];

        // ── AUGUST 2026 BOOKINGS ───────────────────────────────────────────────────
        $augustBookings = [
            // Baptisms (6)
            ['baptism',           '2026-08-02', '09:00', 'confirmed', 1500.00, $pick(1)[0]],
            ['baptism',           '2026-08-02', '10:30', 'confirmed', 1500.00, $pick(1)[0]],
            ['baptism',           '2026-08-09', '09:00', 'pending',   1500.00, $pick(1)[0]],
            ['baptism',           '2026-08-16', '09:00', 'pending',   1500.00, $pick(1)[0]],
            ['baptism',           '2026-08-23', '09:00', 'pending',   1500.00, $pick(1)[0]],
            ['baptism',           '2026-08-30', '10:00', 'pending',   1500.00, $pick(1)[0]],

            // Weddings (3)
            ['wedding',           '2026-08-08', '10:00', 'confirmed', 8000.00, $pick(1)[0]],
            ['wedding',           '2026-08-15', '14:00', 'pending',   8000.00, $pick(1)[0]],
            ['wedding',           '2026-08-29', '10:00', 'pending',   8000.00, $pick(1)[0]],

            // Funeral Masses (1)
            ['funeral_mass',      '2026-08-05', '09:00', 'confirmed', 3500.00, $pick(1)[0]],

            // Pre-Baptismal Seminar (2)
            ['pre_baptismal',     '2026-08-07', '14:00', 'confirmed', 150.00,  $pick(1)[0]],
            ['pre_baptismal',     '2026-08-21', '14:00', 'pending',   150.00,  $pick(1)[0]],

            // Pre-Marriage Seminar (1)
            ['pre_marriage',      '2026-08-10', '08:00', 'confirmed', 500.00,  $pick(1)[0]],

            // Confirmation Catechesis (1)
            ['confirmation_catechesis', '2026-08-11', '15:00', 'confirmed', 0.00, $pick(1)[0]],

            // House Blessings (3)
            ['house_blessing',    '2026-08-01', '10:00', 'confirmed', 1500.00, $pick(1)[0]],
            ['house_blessing',    '2026-08-13', '09:00', 'pending',   1500.00, $pick(1)[0]],
            ['house_blessing',    '2026-08-20', '14:00', 'pending',   1500.00, $pick(1)[0]],

            // Car Blessings (1)
            ['car_blessing',      '2026-08-04', '08:00', 'confirmed', 500.00,  $pick(1)[0]],

            // Mass Intentions (4)
            ['mass_intention',    '2026-08-05', '06:00', 'confirmed', 500.00,  $pick(1)[0]],
            ['mass_intention',    '2026-08-12', '06:00', 'pending',   500.00,  $pick(1)[0]],
            ['mass_intention',    '2026-08-19', '06:00', 'pending',   500.00,  $pick(1)[0]],
            ['mass_intention',    '2026-08-26', '06:00', 'pending',   500.00,  $pick(1)[0]],

            // Sick Call (1)
            ['sick_call',         '2026-08-06', '15:00', 'pending',   0.00,    $pick(1)[0]],
        ];

        $allBookings = array_merge($julyBookings, $augustBookings);
        $createdIds = [];

        foreach ($allBookings as [$type, $date, $time, $status, $fee, $parishionerId]) {
            $booking = Booking::create([
                'parishioner_id' => $parishionerId,
                'booking_type'   => $type,
                'scheduled_date' => $date,
                'scheduled_time' => $time,
                'status'         => $status,
                'service_fee'    => $fee,
                'notes'          => 'Seeded booking for demonstration',
                'confirmed_by'   => in_array($status, ['confirmed', 'completed']) ? $adminId : null,
                'confirmed_at'   => in_array($status, ['confirmed', 'completed'])
                    ? \Carbon\Carbon::parse($date)->subDays(rand(1, 5))
                    : null,
            ]);

            // Add payment for completed bookings with a fee
            if ($status === 'completed' && $fee > 0) {
                Payment::create([
                    'parishioner_id'   => $parishionerId,
                    'booking_id'       => $booking->id,
                    'amount'           => $fee,
                    'payment_method'   => collect(['cash', 'gcash', 'maya'])->random(),
                    'status'           => 'paid',
                    'paid_at'          => \Carbon\Carbon::parse($date)->addHours(2),
                    'reference_number' => 'PAY-' . strtoupper(uniqid()),
                    'notes'            => 'Seeded payment',
                ]);
            }

            $createdIds[] = ['id' => $booking->id, 'type' => $type, 'date' => $date, 'status' => $status];
        }

        // ── SACRAMENTAL RECORDS for completed sacrament bookings ─────────────────
        $sacramentMap = ['baptism' => 'baptism', 'wedding' => 'marriage', 'funeral_mass' => 'death_burial'];
        $priests = ['Rev. Fr. Erwin S. Sanchez', 'Rev. Fr. Juan dela Cruz', 'Rev. Fr. Jose Santos'];

        foreach ($createdIds as $b) {
            if ($b['status'] !== 'completed') continue;
            if (!isset($sacramentMap[$b['type']])) continue;

            $booking = Booking::find($b['id']);
            SacramentalRecord::create([
                'parishioner_id'   => $booking->parishioner_id,
                'type'             => $sacramentMap[$b['type']],
                'date_administered'=> $b['date'],
                'celebrant'        => $priests[array_rand($priests)],
                'venue'            => 'Mary Help of Christians Parish, Cabuyao, Laguna',
                'notes'            => 'Auto-generated from booking',
                'recorded_by'      => $adminId,
            ]);
        }

        // ── SUMMARY ──────────────────────────────────────────────────────────────
        $july = collect($createdIds)->filter(fn($b) => str_starts_with($b['date'], '2026-07'));
        $aug  = collect($createdIds)->filter(fn($b) => str_starts_with($b['date'], '2026-08'));

        $this->command->info('');
        $this->command->info('Bookings seeded:');
        $this->command->info('  July 2026  : ' . $july->count() . ' bookings');
        $this->command->info('  August 2026: ' . $aug->count()  . ' bookings');
        $this->command->info('  Total      : ' . count($createdIds) . ' bookings');
        $this->command->info('');
        $types = collect($createdIds)->groupBy('type')->map->count();
        foreach ($types as $t => $c) {
            $this->command->info("    {$t}: {$c}");
        }
    }
}
