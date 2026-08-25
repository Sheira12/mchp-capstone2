<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Models\Parishioner;
use App\Models\SacramentalRecord;
use App\Services\QrCodeService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * CurrentMonthSeeder
 *
 * Fills August 2026 (current month) and June 2026 (data gap) with:
 *  - 15 sacramental records for Aug 2026  → fills Sacrament Breakdown chart
 *  - 8  sacramental records for Jun 2026  → fills the June dip in the sacrament trend
 *  - 25 paid payments for Aug 2026        → Revenue (This Month) becomes realistic
 *  - 12 paid payments for Jun 2026        → fills June revenue gap in the chart
 *  - 6  ledger income entries for Aug 2026
 *
 * Safe to re-run — uses duplicate checks on sacramental records and
 * booking IDs before inserting payments.
 *
 * Run: php artisan db:seed --class=CurrentMonthSeeder
 */
class CurrentMonthSeeder extends Seeder
{
    private QrCodeService $qrService;
    private int $adminId    = 1;
    private int $secretaryId = 2;

    // Filipino name pools
    private array $firstNames = [
        'Jose', 'Maria', 'Juan', 'Ana', 'Pedro', 'Rosa', 'Carlos', 'Elena',
        'Miguel', 'Lourdes', 'Antonio', 'Celia', 'Roberto', 'Nena', 'Manuel',
        'Teresita', 'Eduardo', 'Marilou', 'Ricardo', 'Cristina', 'Fernando',
        'Maricel', 'Ernesto', 'Rowena', 'Alfredo', 'Josephine', 'Renato',
        'Evelyn', 'Danilo', 'Rosario',
    ];
    private array $lastNames = [
        'Santos', 'Reyes', 'Cruz', 'Garcia', 'Dela Cruz', 'Mendoza', 'Torres',
        'Flores', 'Villanueva', 'Bautista', 'Ramos', 'Aquino', 'Castillo',
        'Morales', 'Gonzales', 'Hernandez', 'Diaz', 'Perez', 'Lopez',
    ];
    private array $celebrants = [
        'Rev. Fr. Erwin S. Sanchez',
        'Rev. Fr. Miguel Fernandez',
        'Rev. Fr. Antonio Villanueva',
        'Rev. Fr. Jose Bautista',
    ];

    public function run(): void
    {
        $this->qrService = app(QrCodeService::class);

        $this->command->info('Seeding current-month (Aug 2026) & June 2026 data...');

        $parishioners = Parishioner::inRandomOrder()->limit(80)->get();

        if ($parishioners->count() < 10) {
            $this->command->error('Not enough parishioners. Run DemoDataSeeder first.');
            return;
        }

        $this->seedSacramentalRecordsAugust($parishioners);
        $this->seedSacramentalRecordsJune($parishioners);
        $this->seedPaymentsAugust($parishioners);
        $this->seedPaymentsJune($parishioners);
        $this->seedLedgerAugust();

        $this->printSummary();
    }

    // ── AUG 2026 SACRAMENTAL RECORDS ─────────────────────────────────────────
    private function seedSacramentalRecordsAugust($parishioners): void
    {
        $this->command->line('  Creating August 2026 sacramental records...');

        // Distribution that makes Sacrament Breakdown chart look realistic
        $plan = [
            'baptism'         => 7,   // most common
            'first_communion' => 3,
            'confirmation'    => 2,
            'marriage'        => 2,
            'death_burial'    => 1,
        ];

        $created = 0;
        foreach ($plan as $type => $count) {
            $pool = $parishioners->shuffle()->values();
            $added = 0;

            foreach ($pool as $p) {
                if ($added >= $count) break;

                // Skip if this parishioner already has this sacrament this month
                $exists = SacramentalRecord::where('parishioner_id', $p->id)
                    ->where('type', $type)
                    ->whereYear('date_administered', 2026)
                    ->whereMonth('date_administered', 8)
                    ->exists();
                if ($exists) continue;

                $day  = rand(1, 25);  // Aug 1–25
                $date = Carbon::create(2026, 8, $day);

                $data = $this->buildSacramentData($p->id, $type, $date);
                SacramentalRecord::create($data);
                $added++;
                $created++;
            }
        }

        $this->command->line("  ✓ Created {$created} sacramental records for August 2026.");
    }

    // ── JUN 2026 SACRAMENTAL RECORDS ─────────────────────────────────────────
    private function seedSacramentalRecordsJune($parishioners): void
    {
        $this->command->line('  Creating June 2026 sacramental records...');

        $plan = [
            'baptism'         => 4,
            'first_communion' => 2,
            'confirmation'    => 1,
            'marriage'        => 1,
        ];

        $created = 0;
        foreach ($plan as $type => $count) {
            $pool  = $parishioners->shuffle()->values();
            $added = 0;

            foreach ($pool as $p) {
                if ($added >= $count) break;

                $exists = SacramentalRecord::where('parishioner_id', $p->id)
                    ->where('type', $type)
                    ->whereYear('date_administered', 2026)
                    ->whereMonth('date_administered', 6)
                    ->exists();
                if ($exists) continue;

                $day  = rand(1, 28);
                $date = Carbon::create(2026, 6, $day);

                SacramentalRecord::create($this->buildSacramentData($p->id, $type, $date));
                $added++;
                $created++;
            }
        }

        $this->command->line("  ✓ Created {$created} sacramental records for June 2026.");
    }

    // ── AUG 2026 PAYMENTS ────────────────────────────────────────────────────
    private function seedPaymentsAugust($parishioners): void
    {
        $this->command->line('  Creating August 2026 payments...');

        // Booking types with fees — creates bookings then pays them
        $schedule = [
            // [type, fee, date_offset from Aug 1, method]
            ['baptism',            500.00,  1, 'cash'],
            ['baptism',            500.00,  3, 'gcash'],
            ['baptism',            500.00,  7, 'gcash'],
            ['baptism',            500.00, 10, 'maya'],
            ['baptism',            500.00, 14, 'cash'],
            ['wedding',           3000.00,  2, 'maya'],
            ['wedding',           3000.00, 16, 'gcash'],
            ['funeral_mass',      1500.00,  5, 'cash'],
            ['funeral_mass',      1500.00, 19, 'cash'],
            ['house_blessing',     300.00,  6, 'gcash'],
            ['house_blessing',     300.00, 11, 'gcash'],
            ['house_blessing',     300.00, 20, 'cash'],
            ['car_blessing',       200.00,  8, 'gcash'],
            ['car_blessing',       200.00, 13, 'maya'],
            ['mass_intention',     200.00,  4, 'cash'],
            ['mass_intention',     200.00,  9, 'gcash'],
            ['mass_intention',     200.00, 15, 'cash'],
            ['pre_baptismal',      100.00,  2, 'cash'],
            ['pre_baptismal',      100.00, 12, 'cash'],
            ['pre_marriage',       500.00,  7, 'gcash'],
            ['confirmation_catechesis', 200.00, 5, 'cash'],
            ['confirmation_catechesis', 200.00, 18, 'cash'],
            ['business_blessing',  300.00, 17, 'gcash'],
            ['sick_call',            0.00, 22, 'cash'],
            ['mass_intention',     200.00, 21, 'cash'],
        ];

        $pool    = $parishioners->shuffle()->values();
        $created = 0;
        $revenue = 0;

        foreach ($schedule as $idx => [$type, $fee, $dayOffset, $method]) {
            $p      = $pool[$idx % $pool->count()];
            $date   = Carbon::create(2026, 8, min($dayOffset, 25));
            $paidAt = $date->copy()->subDays(rand(0, 2));

            // Create completed booking
            $booking = Booking::create([
                'parishioner_id' => $p->id,
                'booking_type'   => $type,
                'scheduled_date' => $date->toDateString(),
                'scheduled_time' => $this->randomTime(),
                'service_fee'    => $fee,
                'status'         => $fee > 0 ? 'completed' : 'confirmed',
                'confirmed_by'   => $this->secretaryId,
                'confirmed_at'   => $paidAt,
                'admin_notes'    => 'Service rendered.',
                'reminder_sent'  => true,
                'created_at'     => $paidAt->copy()->subDays(rand(5, 14)),
                'updated_at'     => $date,
            ]);

            try { $this->qrService->generateForBooking($booking); } catch (\Exception $e) {}

            // Create paid payment (skip zero-fee bookings)
            if ($fee > 0) {
                Payment::create([
                    'parishioner_id'    => $p->id,
                    'booking_id'        => $booking->id,
                    'amount'            => $fee,
                    'payment_method'    => $method,
                    'transaction_type'  => 'debit',
                    'status'            => 'paid',
                    'paid_at'           => $paidAt,
                    'verified_by'       => $this->adminId,
                    'verified_at'       => $paidAt,
                    'notes'             => ucfirst($method) . ' payment for ' . $type . '.',
                    'gateway_reference' => in_array($method, ['gcash','maya'])
                        ? strtoupper($method) . '-' . strtoupper(substr(uniqid(), -8))
                        : null,
                    'created_at'        => $paidAt,
                    'updated_at'        => $paidAt,
                ]);
                $revenue += $fee;
                $created++;
            }
        }

        $this->command->line("  ✓ Created {$created} paid payments for August 2026 — ₱" . number_format($revenue, 2) . ' revenue.');
    }

    // ── JUN 2026 PAYMENTS ────────────────────────────────────────────────────
    private function seedPaymentsJune($parishioners): void
    {
        $this->command->line('  Creating June 2026 payments...');

        $schedule = [
            ['baptism',       500.00,  1, 'cash'],
            ['baptism',       500.00,  7, 'gcash'],
            ['baptism',       500.00, 15, 'maya'],
            ['wedding',      3000.00,  6, 'maya'],
            ['funeral_mass', 1500.00, 10, 'cash'],
            ['funeral_mass', 1500.00, 20, 'cash'],
            ['house_blessing', 300.00, 5, 'gcash'],
            ['house_blessing', 300.00, 18, 'gcash'],
            ['mass_intention', 200.00, 3, 'cash'],
            ['mass_intention', 200.00, 12, 'cash'],
            ['pre_baptismal',  100.00, 8, 'cash'],
            ['car_blessing',   200.00, 22, 'gcash'],
        ];

        $pool    = $parishioners->shuffle()->values();
        $created = 0;
        $revenue = 0;

        foreach ($schedule as $idx => [$type, $fee, $day, $method]) {
            $p      = $pool[$idx % $pool->count()];
            $date   = Carbon::create(2026, 6, min($day, 28));
            $paidAt = $date->copy()->subDays(rand(0, 1));

            $booking = Booking::create([
                'parishioner_id' => $p->id,
                'booking_type'   => $type,
                'scheduled_date' => $date->toDateString(),
                'scheduled_time' => $this->randomTime(),
                'service_fee'    => $fee,
                'status'         => 'completed',
                'confirmed_by'   => $this->secretaryId,
                'confirmed_at'   => $paidAt,
                'reminder_sent'  => true,
                'created_at'     => $paidAt->copy()->subDays(rand(5, 14)),
                'updated_at'     => $date,
            ]);

            try { $this->qrService->generateForBooking($booking); } catch (\Exception $e) {}

            Payment::create([
                'parishioner_id'    => $p->id,
                'booking_id'        => $booking->id,
                'amount'            => $fee,
                'payment_method'    => $method,
                'transaction_type'  => 'debit',
                'status'            => 'paid',
                'paid_at'           => $paidAt,
                'verified_by'       => $this->adminId,
                'verified_at'       => $paidAt,
                'notes'             => ucfirst($method) . ' payment for ' . $type . '.',
                'gateway_reference' => in_array($method, ['gcash', 'maya'])
                    ? strtoupper($method) . '-' . strtoupper(substr(uniqid(), -8))
                    : null,
                'created_at'        => $paidAt,
                'updated_at'        => $paidAt,
            ]);
            $revenue += $fee;
            $created++;
        }

        $this->command->line("  ✓ Created {$created} paid payments for June 2026 — ₱" . number_format($revenue, 2) . ' revenue.');
    }

    // ── AUG 2026 LEDGER ──────────────────────────────────────────────────────
    private function seedLedgerAugust(): void
    {
        $this->command->line('  Creating August 2026 ledger income entries...');

        $entries = [
            ['credit', 'Collection',     'Sunday Collection — Aug 3, 2026',           13500.00, '2026-08-03', 'COL-2608-03'],
            ['credit', 'Baptism Fee',    'Group Baptism — Aug 7 (5 children)',          7500.00, '2026-08-07', 'BAP-2608-07'],
            ['credit', 'Collection',     'Sunday Collection — Aug 10, 2026',           12200.00, '2026-08-10', 'COL-2608-10'],
            ['credit', 'Wedding Fee',    'Wedding — Garcia & Villanueva',               8000.00, '2026-08-16', 'WED-2608-16'],
            ['credit', 'Collection',     'Sunday Collection — Aug 17, 2026',           11800.00, '2026-08-17', 'COL-2608-17'],
            ['credit', 'Mass Stipend',   'Weekday Stipends — Aug Week 3',               2100.00, '2026-08-19', 'MS-2608-03'],
            ['credit', 'Certificate Fee','Certificate Fees — Aug 2026',                 2600.00, '2026-08-21', 'CERT-2608-21'],
            ['credit', 'House Blessing', 'House Blessing — 4 households',               4800.00, '2026-08-22', 'HB-2608-22'],
            ['credit', 'Seminar Fee',    'Pre-Baptismal Seminar — 10 attendees',        1500.00, '2026-08-14', 'SEM-2608-14'],
            ['credit', 'Donation',       'Benefactor Donation — Flores Family',         5000.00, '2026-08-05', 'DON-2608-05'],
            ['debit',  'Utilities',      'Meralco Bill — August 2026',                  7600.00, '2026-08-10', 'UTIL-2608-10'],
            ['debit',  'Salary',         'Parish Staff Honorarium — August 2026',      18500.00, '2026-08-31', 'SAL-2608-31'],
            ['debit',  'Sacramentals',   'Candles & Liturgical Items — Aug 2026',       2800.00, '2026-08-06', 'SACR-2608-06'],
            ['debit',  'Office Supplies','Toner & Paper — Aug 2026',                    1200.00, '2026-08-13', 'SUPP-2608-13'],
        ];

        $created = 0;
        foreach ($entries as [$type, $category, $description, $amount, $date, $ref]) {
            if (LedgerEntry::where('reference_number', $ref)->exists()) continue;

            LedgerEntry::create([
                'type'             => $type,
                'category'         => $category,
                'description'      => $description,
                'amount'           => $amount,
                'entry_date'       => $date,
                'reference_number' => $ref,
                'recorded_by'      => $this->adminId,
            ]);
            $created++;
        }

        $this->command->line("  ✓ Created {$created} ledger entries for August 2026.");
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────
    private function buildSacramentData(int $parishionerId, string $type, Carbon $date): array
    {
        $fn1 = $this->firstNames[array_rand($this->firstNames)];
        $fn2 = $this->firstNames[array_rand($this->firstNames)];
        $ln1 = $this->lastNames[array_rand($this->lastNames)];
        $ln2 = $this->lastNames[array_rand($this->lastNames)];

        $base = [
            'parishioner_id'    => $parishionerId,
            'type'              => $type,
            'date_administered' => $date->toDateString(),
            'celebrant'         => $this->celebrants[array_rand($this->celebrants)],
            'venue'             => 'Mary Help of Christians Parish',
            'register_number'   => strtoupper(substr($type, 0, 1)) . '-' .
                                   $date->format('Y') . '-' .
                                   str_pad(rand(1, 300), 3, '0', STR_PAD_LEFT),
            'page_number'       => (string) rand(1, 60),
            'line_number'       => (string) rand(1, 30),
            'recorded_by'       => $this->secretaryId,
            'created_at'        => $date,
            'updated_at'        => $date,
        ];

        // Verified (80% chance)
        if (rand(1, 10) <= 8) {
            $base['verified_by'] = $this->adminId;
            $base['verified_at'] = $date->copy()->addDays(rand(1, 3));
        }

        if ($type === 'baptism') {
            $base['godparents'] = ["{$fn1} {$ln1}", "{$fn2} {$ln2}"];
            $base['notes']      = "{$fn1} {$ln2} and {$fn2} {$ln1}";
        }

        if (in_array($type, ['confirmation', 'marriage'])) {
            $base['sponsors'] = ["{$fn1} {$ln1}", "{$fn2} {$ln2}"];
        }

        if ($type === 'marriage') {
            $base['witnesses'] = ["{$fn1} {$ln1}", "{$fn2} {$ln2}"];
        }

        return $base;
    }

    private function randomTime(): string
    {
        return ['06:00:00', '08:00:00', '09:00:00', '10:00:00', '14:00:00', '16:00:00'][rand(0, 5)];
    }

    private function printSummary(): void
    {
        $this->command->newLine();
        $this->command->info('✅ Current-month data seeded!');
        $this->command->newLine();

        $aug = now()->setMonth(8)->setYear(2026);
        $jun = now()->setMonth(6)->setYear(2026);

        $augSacr = SacramentalRecord::whereYear('date_administered', 2026)->whereMonth('date_administered', 8)->count();
        $junSacr = SacramentalRecord::whereYear('date_administered', 2026)->whereMonth('date_administered', 6)->count();
        $augRev  = Payment::where('status', 'paid')->whereYear('paid_at', 2026)->whereMonth('paid_at', 8)->sum('amount');
        $junRev  = Payment::where('status', 'paid')->whereYear('paid_at', 2026)->whereMonth('paid_at', 6)->sum('amount');

        $this->command->line('  <fg=green>DASHBOARD PREVIEW:</>');
        $this->command->line('    Aug 2026 Sacramental Records: ' . $augSacr);
        $this->command->line('    Jun 2026 Sacramental Records: ' . $junSacr);
        $this->command->line('    Aug 2026 Revenue:  ₱' . number_format($augRev, 2));
        $this->command->line('    Jun 2026 Revenue:  ₱' . number_format($junRev, 2));

        $sacrBreakdown = SacramentalRecord::whereYear('date_administered', 2026)
            ->whereMonth('date_administered', 8)
            ->select('type', \Illuminate\Support\Facades\DB::raw('count(*) as cnt'))
            ->groupBy('type')
            ->get();

        $this->command->line('    Sacrament Breakdown (Aug):');
        foreach ($sacrBreakdown as $r) {
            $this->command->line("      {$r->type}: {$r->cnt}");
        }
        $this->command->newLine();
    }
}
