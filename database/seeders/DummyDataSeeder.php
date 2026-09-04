<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Certificate;
use App\Models\Family;
use App\Models\LedgerEntry;
use App\Models\Parishioner;
use App\Models\Payment;
use App\Models\SacramentalRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * DummyDataSeeder
 *
 * ─── PURPOSE ──────────────────────────────────────────────────────────────────
 * Creates clearly-tagged demo records for testing the Reports module.
 * All records are stamped with [DUMMY] in their `notes` field so they can be
 * identified and removed without touching real production data.
 *
 * ─── SAFETY RULES ─────────────────────────────────────────────────────────────
 * • Never calls truncate(), delete(), migrate:fresh, or db:wipe.
 * • Fully idempotent — safe to run multiple times; uses firstOrCreate() on
 *   unique lookup keys so no duplicate records are created.
 * • Never calls PayMongo. Payments are local demo records only.
 * • Uses @example.com email addresses — never real Gmail addresses.
 * • Leaves ALL existing production records untouched.
 *
 * ─── TAGGING STRATEGY ─────────────────────────────────────────────────────────
 * Every record created by this seeder has '[DUMMY]' at the start of its `notes`
 * column. The cleanup command `php artisan demo-data:clear` deletes ONLY records
 * whose `notes` starts with '[DUMMY]'.
 *
 * ─── USAGE ────────────────────────────────────────────────────────────────────
 * Seed:   php artisan db:seed --class=DummyDataSeeder
 * Remove: php artisan demo-data:clear
 *
 * ─── RECORD COUNTS ────────────────────────────────────────────────────────────
 * Families            :  8
 * Parishioners        : 10  (+ 10 linked User accounts)
 * Sacramental records : 18
 * Bookings            : 22  (spread across 3 months, all statuses)
 * Payments            : 20  (pending, paid, failed, refunded — all methods)
 * Certificates        : 10  (draft, issued, released)
 * Ledger entries      : 24  (credit + debit across 3 months)
 */
class DummyDataSeeder extends Seeder
{
    // ── Every dummy record has this prefix in its notes column ──────────────
    const TAG = '[DUMMY]';

    private ?User $admin;
    private ?User $secretary;

    // Months used for report filter testing
    private Carbon $m1; // 3 months ago
    private Carbon $m2; // 2 months ago
    private Carbon $m3; // last month

    public function run(): void
    {
        $this->admin = User::where('email', 'maryhelpparish@gmail.com')
                           ->orWhere('email', 'admin@mhcparish.ph')
                           ->first()
                      ?? User::role('super_admin')->first();

        $this->secretary = User::where('email', 'cumpioaries07@gmail.com')
                               ->orWhere('email', 'secretary@mhcparish.ph')
                               ->first()
                          ?? User::role('parish_secretary')->first();

        if (!$this->admin) {
            $this->command?->error('No admin user found. Run AdminUserSeeder first.');
            return;
        }

        $now       = Carbon::now();
        $this->m1  = $now->copy()->subMonths(3)->startOfMonth();
        $this->m2  = $now->copy()->subMonths(2)->startOfMonth();
        $this->m3  = $now->copy()->subMonths(1)->startOfMonth();

        $this->command?->info('Creating dummy data for Reports module...');

        // Ordered to satisfy foreign key constraints
        $families      = $this->seedFamilies();
        $parishioners  = $this->seedParishioners($families);
        $this->seedSacramentalRecords($parishioners);
        $bookings      = $this->seedBookings($parishioners);
        $this->seedPayments($parishioners, $bookings);
        $this->seedCertificates($parishioners);
        $this->seedLedgerEntries();

        $this->command?->info('');
        $this->command?->info('✅ Dummy data seeded successfully.');
        $this->command?->info('   To remove: php artisan demo-data:clear');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FAMILIES (8 records)
    // ─────────────────────────────────────────────────────────────────────────
    private function seedFamilies(): array
    {
        $this->command?->line('  Creating demo families...');

        $data = [
            ['family_name' => '[DUMMY] Dela Cruz Family',  'barangay' => 'Niugan',      'contact_number' => '09100000001'],
            ['family_name' => '[DUMMY] Reyes Family',       'barangay' => 'Banay-banay', 'contact_number' => '09100000002'],
            ['family_name' => '[DUMMY] Santos Family',      'barangay' => 'Pulo',        'contact_number' => '09100000003'],
            ['family_name' => '[DUMMY] Bautista Family',    'barangay' => 'Sala',        'contact_number' => '09100000004'],
            ['family_name' => '[DUMMY] Flores Family',      'barangay' => 'Marinig',     'contact_number' => '09100000005'],
            ['family_name' => '[DUMMY] Torres Family',      'barangay' => 'Niugan',      'contact_number' => '09100000006'],
            ['family_name' => '[DUMMY] Villanueva Family',  'barangay' => 'Diezmo',      'contact_number' => '09100000007'],
            ['family_name' => '[DUMMY] Aquino Family',      'barangay' => 'Mamatid',     'contact_number' => '09100000008'],
        ];

        $families = [];
        foreach ($data as $item) {
            $families[] = Family::firstOrCreate(
                ['family_name' => $item['family_name']],
                [
                    'address'        => 'Demo Street, Southville 1',
                    'barangay'       => $item['barangay'],
                    'city'           => 'Cabuyao',
                    'province'       => 'Laguna',
                    'contact_number' => $item['contact_number'],
                    'notes'          => self::TAG . ' Demo family record — safe to delete.',
                ]
            );
        }

        return $families;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PARISHIONERS + USERS (10 each)
    // ─────────────────────────────────────────────────────────────────────────
    private function seedParishioners(array $families): array
    {
        $this->command?->line('  Creating demo parishioners and users...');

        $profiles = [
            [
                'email'      => 'demo.parishioner001@example.com',
                'first_name' => 'Antonio',   'middle_name' => 'Dela Cruz', 'last_name' => 'Santos',
                'birthdate'  => '1985-03-12', 'gender' => 'male',   'civil_status' => 'married',
                'family_idx' => 0,            'is_head' => true,    'relation' => 'Head',
            ],
            [
                'email'      => 'demo.parishioner002@example.com',
                'first_name' => 'Maria',     'middle_name' => 'Santos',    'last_name' => 'Reyes',
                'birthdate'  => '1990-07-25', 'gender' => 'female', 'civil_status' => 'married',
                'family_idx' => 1,            'is_head' => false,   'relation' => 'Spouse',
            ],
            [
                'email'      => 'demo.parishioner003@example.com',
                'first_name' => 'Jose',      'middle_name' => 'Bautista',  'last_name' => 'Dela Cruz',
                'birthdate'  => '1972-11-08', 'gender' => 'male',   'civil_status' => 'widowed',
                'family_idx' => 0,            'is_head' => false,   'relation' => 'Child',
            ],
            [
                'email'      => 'demo.parishioner004@example.com',
                'first_name' => 'Rosa',      'middle_name' => 'Flores',    'last_name' => 'Torres',
                'birthdate'  => '2000-01-30', 'gender' => 'female', 'civil_status' => 'single',
                'family_idx' => 5,            'is_head' => true,    'relation' => 'Head',
            ],
            [
                'email'      => 'demo.parishioner005@example.com',
                'first_name' => 'Miguel',    'middle_name' => 'Cruz',      'last_name' => 'Aquino',
                'birthdate'  => '1968-06-14', 'gender' => 'male',   'civil_status' => 'married',
                'family_idx' => 7,            'is_head' => true,    'relation' => 'Head',
            ],
            [
                'email'      => 'demo.parishioner006@example.com',
                'first_name' => 'Elena',     'middle_name' => 'Reyes',     'last_name' => 'Flores',
                'birthdate'  => '1995-09-03', 'gender' => 'female', 'civil_status' => 'single',
                'family_idx' => 4,            'is_head' => false,   'relation' => 'Child',
            ],
            [
                'email'      => 'demo.parishioner007@example.com',
                'first_name' => 'Carlos',    'middle_name' => 'Villanueva', 'last_name' => 'Bautista',
                'birthdate'  => '1980-12-19', 'gender' => 'male',   'civil_status' => 'married',
                'family_idx' => 3,            'is_head' => true,    'relation' => 'Head',
            ],
            [
                'email'      => 'demo.parishioner008@example.com',
                'first_name' => 'Luz',       'middle_name' => 'Aquino',    'last_name' => 'Villanueva',
                'birthdate'  => '2005-04-22', 'gender' => 'female', 'civil_status' => 'single',
                'family_idx' => 6,            'is_head' => false,   'relation' => 'Child',
            ],
            [
                'email'      => 'demo.parishioner009@example.com',
                'first_name' => 'Ramon',     'middle_name' => 'Torres',    'last_name' => 'Mendez',
                'birthdate'  => '1958-08-07', 'gender' => 'male',   'civil_status' => 'married',
                'family_idx' => 5,            'is_head' => false,   'relation' => 'Spouse',
            ],
            [
                'email'      => 'demo.parishioner010@example.com',
                'first_name' => 'Cecilia',   'middle_name' => 'Bautista',  'last_name' => 'Santos',
                'birthdate'  => '1943-02-11', 'gender' => 'female', 'civil_status' => 'widowed',
                'family_idx' => 2,            'is_head' => true,    'relation' => 'Head',
            ],
        ];

        $parishioners = [];
        foreach ($profiles as $p) {
            $parishioner = Parishioner::firstOrCreate(
                ['email' => $p['email']],
                [
                    'family_id'            => $families[$p['family_idx']]->id,
                    'first_name'           => $p['first_name'],
                    'middle_name'          => $p['middle_name'],
                    'last_name'            => $p['last_name'],
                    'birthdate'            => $p['birthdate'],
                    'gender'               => $p['gender'],
                    'civil_status'         => $p['civil_status'],
                    'address'              => 'Demo Street, Southville 1',
                    'barangay'             => 'Niugan',
                    'city'                 => 'Cabuyao',
                    'province'             => 'Laguna',
                    'postal_code'          => '4025',
                    'contact_number'       => '09100000000',
                    'is_head_of_family'    => $p['is_head'],
                    'relationship_to_head' => $p['relation'],
                    'is_active'            => true,
                    'notes'                => self::TAG . ' Demo parishioner — safe to delete.',
                ]
            );

            // Create linked user account (login portal access)
            $user = User::firstOrCreate(
                ['email' => $p['email']],
                [
                    'name'           => $p['first_name'] . ' ' . $p['last_name'],
                    'password'       => Hash::make('DemoPassword@123'),
                    'parishioner_id' => $parishioner->id,
                    'is_active'      => true,
                ]
            );

            if ($user->wasRecentlyCreated) {
                $user->syncRoles(['parishioner']);
            }

            $parishioners[] = $parishioner;
        }

        return $parishioners;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SACRAMENTAL RECORDS (18 records)
    // Spread across parishioners with different types
    // ─────────────────────────────────────────────────────────────────────────
    private function seedSacramentalRecords(array $parishioners): void
    {
        $this->command?->line('  Creating demo sacramental records...');

        $priest     = 'Rev. Fr. Erwin S. Sanchez';
        $venue      = 'Mary Help of Christians Parish';
        $adminId    = $this->admin->id;
        $secId      = $this->secretary?->id ?? $adminId;
        $tag        = self::TAG . ' Demo sacramental record.';

        $records = [
            // Baptisms — 5 records
            [$parishioners[0], 'baptism',         '1985-03-24', $priest, $venue, 'B-1985-044', '11', '3',  ['Manuel Santos', 'Lucia Dela Cruz'], null, null],
            [$parishioners[1], 'baptism',         '1990-08-05', $priest, $venue, 'B-1990-067', '17', '1',  ['Pedro Reyes', 'Carmen Santos'],     null, null],
            [$parishioners[2], 'baptism',         '1972-11-19', $priest, $venue, 'B-1972-088', '22', '6',  ['Arturo Bautista', 'Nena Cruz'],      null, null],
            [$parishioners[4], 'baptism',         '1968-06-23', $priest, $venue, 'B-1968-031', '8',  '9',  ['Alfonso Aquino', 'Rosario Mendez'],  null, null],
            [$parishioners[9], 'baptism',         '1943-02-20', $priest, $venue, 'B-1943-012', '3',  '4',  ['Domingo Bautista', 'Fe Santos'],     null, null],
            // First Communions — 3 records
            [$parishioners[0], 'first_communion',  '1995-05-12', $priest, $venue, 'FC-1995-018', '5', '7',  null, null, null],
            [$parishioners[1], 'first_communion',  '2000-05-07', $priest, $venue, 'FC-2000-022', '6', '3',  null, null, null],
            [$parishioners[4], 'first_communion',  '1978-05-14', $priest, $venue, 'FC-1978-009', '2', '11', null, null, null],
            // Confirmations — 3 records
            [$parishioners[0], 'confirmation',     '2001-04-07', 'Most Rev. Bishop Jose Oliveros', 'San Pablo Cathedral', 'C-2001-041', '10', '5', null, ['Manuel Santos'], null],
            [$parishioners[2], 'confirmation',     '1988-04-03', 'Most Rev. Bishop Ricardo Santos', 'San Pablo Cathedral', 'C-1988-019', '5',  '8', null, ['Arturo Bautista'], null],
            [$parishioners[6], 'confirmation',     '1996-03-30', 'Most Rev. Bishop Jose Oliveros', 'San Pablo Cathedral', 'C-1996-033', '8',  '2', null, ['Carlos Villanueva'], null],
            // Marriages — 3 records
            [$parishioners[0], 'marriage',         '2010-02-14', $priest, $venue, 'M-2010-003', '1', '3', null, ['Manuel Santos', 'Lucia Dela Cruz', 'Pedro Reyes'], ['Eduardo Santos', 'Clara Reyes']],
            [$parishioners[4], 'marriage',         '1995-06-17', $priest, $venue, 'M-1995-007', '2', '7', null, ['Alfonso Aquino', 'Rosario Mendez', 'Ramon Torres'], ['Dante Cruz', 'Elvira Bautista']],
            [$parishioners[6], 'marriage',         '2005-11-26', $priest, $venue, 'M-2005-011', '3', '2', null, ['Carlos Villanueva', 'Nena Cruz', 'Jose Bautista'], ['Rene Torres', 'Alma Flores']],
            // Death/Burial — 2 records
            [$parishioners[2], 'death_burial',     $this->m1->copy()->addDays(3)->toDateString(), $priest, $venue, 'D-' . $this->m1->format('Ym') . '-001', '1', '1', null, null, null],
            [$parishioners[9], 'death_burial',     $this->m2->copy()->addDays(8)->toDateString(), $priest, $venue, 'D-' . $this->m2->format('Ym') . '-002', '1', '2', null, null, null],
            // Recent — for current period testing
            [$parishioners[5], 'baptism',          $this->m3->copy()->addDays(5)->toDateString(), $priest, $venue, 'B-' . $this->m3->format('Ym') . '-001', '1', '1', ['Demo Godfather', 'Demo Godmother'], null, null],
            [$parishioners[7], 'first_communion',  $this->m3->copy()->addDays(12)->toDateString(), $priest, $venue, 'FC-' . $this->m3->format('Ym') . '-001', '1', '1', null, null, null],
        ];

        foreach ($records as [$par, $type, $date, $celebrant, $venue2, $regNum, $page, $line, $godparents, $sponsors, $witnesses]) {
            SacramentalRecord::firstOrCreate(
                ['parishioner_id' => $par->id, 'type' => $type, 'register_number' => $regNum],
                [
                    'date_administered' => $date,
                    'celebrant'         => $celebrant,
                    'venue'             => $venue2,
                    'page_number'       => $page,
                    'line_number'       => $line,
                    'godparents'        => $godparents,
                    'sponsors'          => $sponsors,
                    'witnesses'         => $witnesses,
                    'notes'             => $tag,
                    'recorded_by'       => $secId,
                    'verified_by'       => $adminId,
                    'verified_at'       => now()->subMonths(rand(1, 6)),
                ]
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BOOKINGS (22 records)
    // Multiple months, all statuses: pending/confirmed/completed/cancelled
    // ─────────────────────────────────────────────────────────────────────────
    private function seedBookings(array $parishioners): array
    {
        $this->command?->line('  Creating demo bookings...');

        $tag     = self::TAG . ' Demo booking — safe to delete.';
        $adminId = $this->admin->id;
        $secId   = $this->secretary?->id ?? $adminId;

        // [parishioner_idx, booking_type, date, time, status, fee, notes_extra]
        $specs = [
            // ── 3 months ago ──────────────────────────────────────────────
            [0, 'baptism',           $this->m1->copy()->addDays(5),  '09:00', 'completed', 500.00,  'Demo baptism — month 1'],
            [1, 'mass_intention',    $this->m1->copy()->addDays(8),  '06:00', 'completed', 200.00,  'Demo mass intention — month 1'],
            [2, 'house_blessing',    $this->m1->copy()->addDays(12), '10:00', 'completed', 300.00,  'Demo house blessing — month 1'],
            [3, 'pre_baptismal',     $this->m1->copy()->addDays(15), '14:00', 'cancelled', 150.00,  'Demo pre-baptismal seminar — month 1'],
            [4, 'wedding',           $this->m1->copy()->addDays(20), '10:00', 'completed', 3000.00, 'Demo wedding — month 1'],
            // ── 2 months ago ──────────────────────────────────────────────
            [5, 'confirmation_catechesis', $this->m2->copy()->addDays(2),  '08:00', 'completed', 250.00,  'Demo confirmation catechesis — month 2'],
            [6, 'baptism',           $this->m2->copy()->addDays(6),  '09:00', 'completed', 500.00,  'Demo baptism — month 2'],
            [7, 'mass_intention',    $this->m2->copy()->addDays(9),  '06:00', 'completed', 200.00,  'Demo mass intention — month 2'],
            [8, 'sick_call',         $this->m2->copy()->addDays(14), '15:00', 'completed', 0.00,    'Demo sick call — month 2'],
            [9, 'car_blessing',      $this->m2->copy()->addDays(18), '09:00', 'cancelled', 200.00,  'Demo car blessing — month 2'],
            [0, 'funeral_mass',      $this->m2->copy()->addDays(22), '08:00', 'completed', 500.00,  'Demo funeral mass — month 2'],
            // ── Last month ────────────────────────────────────────────────
            [1, 'wedding',           $this->m3->copy()->addDays(3),  '10:00', 'completed', 3000.00, 'Demo wedding — month 3'],
            [2, 'house_blessing',    $this->m3->copy()->addDays(7),  '10:00', 'confirmed', 300.00,  'Demo house blessing — month 3'],
            [3, 'baptism',           $this->m3->copy()->addDays(10), '09:00', 'confirmed', 500.00,  'Demo baptism — month 3'],
            [4, 'pre_marriage',      $this->m3->copy()->addDays(14), '14:00', 'pending',   200.00,  'Demo pre-marriage seminar — month 3'],
            [5, 'mass_intention',    $this->m3->copy()->addDays(16), '06:00', 'completed', 200.00,  'Demo mass intention — month 3'],
            [6, 'business_blessing', $this->m3->copy()->addDays(18), '09:00', 'pending',   400.00,  'Demo business blessing — month 3'],
            // ── Upcoming (current + future) ────────────────────────────────
            [7, 'baptism',           Carbon::now()->addDays(4),  '09:00', 'confirmed', 500.00,  'Demo upcoming baptism'],
            [8, 'wedding',           Carbon::now()->addDays(10), '10:00', 'confirmed', 3000.00, 'Demo upcoming wedding'],
            [9, 'house_blessing',    Carbon::now()->addDays(6),  '10:00', 'pending',   300.00,  'Demo upcoming house blessing'],
            [0, 'pre_baptismal',     Carbon::now()->addDays(15), '14:00', 'pending',   150.00,  'Demo upcoming pre-baptismal'],
            [1, 'mass_intention',    Carbon::now()->addDays(2),  '06:00', 'confirmed', 200.00,  'Demo upcoming mass intention'],
        ];

        $bookings = [];
        foreach ($specs as [$idx, $type, $date, $time, $status, $fee, $extra]) {
            $booking = Booking::firstOrCreate(
                [
                    'parishioner_id' => $parishioners[$idx]->id,
                    'booking_type'   => $type,
                    'scheduled_date' => $date->toDateString(),
                    'notes'          => $tag . ' ' . $extra,
                ],
                [
                    'scheduled_time' => $time . ':00',
                    'status'         => $status,
                    'service_fee'    => $fee,
                    'confirmed_by'   => in_array($status, ['confirmed', 'completed']) ? $secId : null,
                    'confirmed_at'   => in_array($status, ['confirmed', 'completed']) ? $date->copy()->subDays(2) : null,
                    'cancelled_by'   => $status === 'cancelled' ? $adminId : null,
                    'cancelled_at'   => $status === 'cancelled' ? $date->copy()->subDay() : null,
                    'cancellation_reason' => $status === 'cancelled' ? '[DUMMY] Demo cancellation reason.' : null,
                    'reminder_sent'  => $status === 'completed',
                ]
            );

            $bookings[] = $booking;
        }

        return $bookings;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PAYMENTS (20 records)
    // All methods, all statuses, spread across months
    // NO PayMongo calls — all local demo records
    // ─────────────────────────────────────────────────────────────────────────
    private function seedPayments(array $parishioners, array $bookings): void
    {
        $this->command?->line('  Creating demo payments...');

        $tag     = self::TAG . ' Demo payment — no real transaction.';
        $adminId = $this->admin->id;

        // Only create payments for bookings that don't already have one
        // [booking_idx, method, status, paid_offset_days_from_booking, amount]
        $specs = [
            // ── Completed bookings → paid ──────────────────────────────────
            [0,  'cash',  'paid',    1,   500.00],
            [1,  'gcash', 'paid',    1,   200.00],
            [2,  'maya',  'paid',    0,   300.00],
            [4,  'cash',  'paid',    2,   3000.00],
            [5,  'bank',  'paid',    1,   250.00],
            [6,  'gcash', 'paid',    1,   500.00],
            [7,  'maya',  'paid',    0,   200.00],
            [10, 'cash',  'paid',    1,   500.00],
            [11, 'gcash', 'paid',    2,   3000.00],
            [15, 'maya',  'paid',    0,   200.00],
            // ── Confirmed bookings → pending (awaiting verification) ────────
            [12, 'cash',  'pending', 0,   300.00],
            [13, 'gcash', 'pending', 0,   500.00],
            // ── Pending bookings → pending payment ─────────────────────────
            [17, 'cash',  'pending', 0,   500.00],
            [18, 'gcash', 'pending', 0,   3000.00],
            // ── Cancelled bookings → failed ─────────────────────────────────
            [3,  'gcash', 'failed',  0,   150.00],
            [9,  'maya',  'failed',  0,   200.00],
            // ── Refunded (for credit/debit report testing) ──────────────────
            [8,  'cash',  'refunded', 1,  0.00],  // sick call was free but refund adjustment
            // ── Upcoming bookings → pending ─────────────────────────────────
            [19, 'cash',  'pending', 0,   300.00],
            [20, 'gcash', 'pending', 0,   150.00],
            [21, 'maya',  'pending', 0,   200.00],
        ];

        foreach ($specs as [$bookingIdx, $method, $status, $paidOffsetDays, $amount]) {
            if (!isset($bookings[$bookingIdx])) continue;

            $booking = $bookings[$bookingIdx];

            // Idempotency: skip if this booking already has a payment
            if (Payment::where('booking_id', $booking->id)->exists()) continue;

            $paidAt = null;
            if ($status === 'paid') {
                $paidAt = Carbon::parse($booking->scheduled_date)->addDays($paidOffsetDays);
            }

            Payment::create([
                'parishioner_id'     => $booking->parishioner_id,
                'booking_id'         => $booking->id,
                'amount'             => $amount,
                'payment_method'     => $method,
                'transaction_type'   => $status === 'refunded' ? 'credit' : 'debit',
                'status'             => $status,
                'paid_at'            => $paidAt,
                'verified_by'        => $status === 'paid' ? $adminId : null,
                'verified_at'        => $status === 'paid' ? $paidAt : null,
                'notes'              => $tag,
                'refund_reason'      => $status === 'refunded' ? '[DUMMY] Demo refund adjustment.' : null,
                'refunded_by'        => $status === 'refunded' ? $adminId : null,
                'refunded_at'        => $status === 'refunded' ? Carbon::parse($booking->scheduled_date)->addDays(2) : null,
                // No real gateway IDs — demo records only
                'gateway_reference'  => null,
                'gateway_response'   => null,
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CERTIFICATES (10 records)
    // Statuses: draft, issued, released
    // ─────────────────────────────────────────────────────────────────────────
    private function seedCertificates(array $parishioners): void
    {
        $this->command?->line('  Creating demo certificates...');

        $tag     = self::TAG . ' Demo certificate — safe to delete.';
        $adminId = $this->admin->id;

        // [parishioner_idx, cert_type, issued_date, status, purpose]
        $specs = [
            [0, 'baptism',         $this->m1->copy()->addDays(10), 'released', 'For school enrollment'],
            [1, 'baptism',         $this->m1->copy()->addDays(15), 'released', 'For confirmation requirement'],
            [0, 'confirmation',    $this->m2->copy()->addDays(5),  'issued',   'For marriage application'],
            [2, 'baptism',         $this->m2->copy()->addDays(10), 'issued',   'For personal record'],
            [4, 'marriage',        $this->m2->copy()->addDays(18), 'released', 'For legal requirement'],
            [6, 'membership',      $this->m3->copy()->addDays(3),  'issued',   'For parish membership proof'],
            [1, 'first_communion', $this->m3->copy()->addDays(8),  'issued',   'For confirmation requirement'],
            [3, 'baptism',         $this->m3->copy()->addDays(12), 'draft',    'Pending release'],
            [5, 'membership',      Carbon::now()->subDays(5),      'draft',    'Pending admin review'],
            [7, 'baptism',         Carbon::now()->subDays(2),      'draft',    'Recently requested'],
        ];

        foreach ($specs as [$idx, $type, $date, $status, $purpose]) {
            // Idempotency: skip if this parishioner already has this cert type with this tag
            $exists = Certificate::where('parishioner_id', $parishioners[$idx]->id)
                ->where('type', $type)
                ->where('notes', $tag)
                ->exists();

            if ($exists) continue;

            Certificate::create([
                'parishioner_id'      => $parishioners[$idx]->id,
                'sacramental_record_id' => null, // not linking to avoid complex dependency
                'type'                => $type,
                'issued_date'         => $date,
                'issued_by'           => $adminId,
                'purpose'             => $purpose,
                'status'              => $status,
                'notes'               => $tag,
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LEDGER ENTRIES (24 records)
    // Credit (income) and Debit (expense) across 3 months
    // ─────────────────────────────────────────────────────────────────────────
    private function seedLedgerEntries(): void
    {
        $this->command?->line('  Creating demo ledger entries...');

        $tag     = self::TAG . ' Demo ledger entry — safe to delete.';
        $adminId = $this->admin->id;

        // [type, category, description, amount, date_offset_from_month]
        $creditsM1 = [
            ['credit', 'Collection',      '[DUMMY] Sunday Collection — Week 1',      12500.00, 0],
            ['credit', 'Collection',      '[DUMMY] Sunday Collection — Week 2',      11800.00, 7],
            ['credit', 'Collection',      '[DUMMY] Sunday Collection — Week 3',      13200.00, 14],
            ['credit', 'Baptism Fee',     '[DUMMY] Baptism Fee — 3 families',         1500.00, 5],
            ['credit', 'Mass Stipend',    '[DUMMY] Mass Stipend — Week 1',            1200.00, 3],
            ['credit', 'Donation',        '[DUMMY] Anonymous Donation',               5000.00, 10],
        ];

        $creditsM2 = [
            ['credit', 'Collection',      '[DUMMY] Sunday Collection — Week 1',      14100.00, 0],
            ['credit', 'Collection',      '[DUMMY] Sunday Collection — Week 2',      12600.00, 7],
            ['credit', 'Wedding Fee',     '[DUMMY] Wedding Fee — Demo Couple',        3000.00, 3],
            ['credit', 'Certificate Fee', '[DUMMY] Certificate Fees — 5 requests',     750.00, 12],
            ['credit', 'Mass Stipend',    '[DUMMY] Mass Stipend — Week 1',            1400.00, 5],
            ['credit', 'House Blessing',  '[DUMMY] House Blessing Fees — 2 homes',     600.00, 18],
        ];

        $creditsM3 = [
            ['credit', 'Collection',      '[DUMMY] Sunday Collection — Week 1',      13800.00, 0],
            ['credit', 'Collection',      '[DUMMY] Sunday Collection — Week 2',      11400.00, 7],
            ['credit', 'Baptism Fee',     '[DUMMY] Baptism Fee — 2 families',         1000.00, 10],
            ['credit', 'Mass Stipend',    '[DUMMY] Mass Stipend — Week 2',            1100.00, 12],
        ];

        $debitsM1 = [
            ['debit', 'Utilities',       '[DUMMY] Electric Bill — Demo Month 1',     8500.00, 2],
            ['debit', 'Salary',          '[DUMMY] Parish Staff Honorarium',         18000.00, 1],
            ['debit', 'Sacramentals',    '[DUMMY] Liturgical supplies purchase',     3200.00, 15],
        ];

        $debitsM2 = [
            ['debit', 'Utilities',       '[DUMMY] Electric Bill — Demo Month 2',     9100.00, 2],
            ['debit', 'Salary',          '[DUMMY] Parish Staff Honorarium',         18000.00, 1],
            ['debit', 'Maintenance',     '[DUMMY] Church roof repair',               7500.00, 10],
        ];

        $debitsM3 = [
            ['debit', 'Utilities',       '[DUMMY] Electric Bill — Demo Month 3',     8700.00, 2],
            ['debit', 'Salary',          '[DUMMY] Parish Staff Honorarium',         18000.00, 1],
            ['debit', 'Office Supplies', '[DUMMY] Office supplies restocking',       1800.00, 8],
        ];

        $allEntries = [
            [$this->m1, array_merge($creditsM1, $debitsM1)],
            [$this->m2, array_merge($creditsM2, $debitsM2)],
            [$this->m3, array_merge($creditsM3, $debitsM3)],
        ];

        foreach ($allEntries as [$monthStart, $entries]) {
            foreach ($entries as [$type, $category, $description, $amount, $dayOffset]) {
                LedgerEntry::firstOrCreate(
                    [
                        'description' => $description,
                        'entry_date'  => $monthStart->copy()->addDays($dayOffset)->toDateString(),
                    ],
                    [
                        'type'        => $type,
                        'category'    => $category,
                        'amount'      => $amount,
                        'notes'       => $tag,
                        'recorded_by' => $adminId,
                    ]
                );
            }
        }
    }
}
