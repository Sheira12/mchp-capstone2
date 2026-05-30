<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Certificate;
use App\Models\Family;
use App\Models\MassSchedule;
use App\Models\Parishioner;
use App\Models\Payment;
use App\Models\SacramentalRecord;
use App\Models\User;
use App\Services\QrCodeService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * AnalyticsDataSeeder
 *
 * Creates 12 months of realistic historical data to populate:
 *  - Dashboard charts (sacrament trend, revenue trend)
 *  - Payment reports (daily/monthly breakdown by method)
 *  - Booking calendar (spread across months)
 *  - Parishioner growth stats
 *  - Certificate issuance history
 *
 * Run: php artisan db:seed --class=AnalyticsDataSeeder
 */
class AnalyticsDataSeeder extends Seeder
{
    private QrCodeService $qrService;
    private ?User $secretary;
    private ?User $admin;
    private ?User $finance;

    // Filipino names pool
    private array $firstNames = [
        'Jose','Maria','Juan','Ana','Pedro','Rosa','Carlos','Elena',
        'Miguel','Lourdes','Antonio','Celia','Roberto','Nena','Manuel',
        'Teresita','Eduardo','Marilou','Ricardo','Cristina','Fernando',
        'Maricel','Ernesto','Rowena','Alfredo','Josephine','Renato',
        'Evelyn','Danilo','Rosario','Rodrigo','Melinda','Arturo','Cecilia',
        'Domingo','Florencia','Gregorio','Herminia','Isidro','Juliana',
        'Kevin','Lorena','Marco','Natividad','Oscar','Patricia','Quirino',
        'Remedios','Salvador','Teofilo','Ulysses','Veronica','Wilfredo',
        'Ximena','Yolanda','Zenaida','Ariel','Bernadette','Cesar','Diana',
    ];

    private array $lastNames = [
        'Santos','Reyes','Cruz','Garcia','Dela Cruz','Mendoza','Torres',
        'Flores','Villanueva','Bautista','Ramos','Aquino','Castillo',
        'Morales','Gonzales','Hernandez','Diaz','Perez','Lopez','Martinez',
        'Fernandez','Ramirez','Soriano','Pascual','Aguilar','Navarro',
        'Salazar','Ocampo','Lim','Tan','Uy','Sy','Co','Chua','Ang',
        'Manalo','Tolentino','Macaraeg','Buenaventura','Evangelista',
        'Macapagal','Magno','Palma','Quizon','Romualdez','Sison','Tinio',
    ];

    private array $barangays = [
        'Niugan','Banay-banay','Bigaa','Butong','Casile','Diezmo',
        'Gulod','Mamatid','Marinig','Pittland','Pulo','Sala','Sucol',
    ];

    private array $bookingTypes = [
        'baptism','wedding','funeral_mass','house_blessing','car_blessing',
        'business_blessing','sick_call','pre_baptismal','pre_marriage',
        'confirmation_catechesis','mass_intention',
    ];

    private array $bookingFees = [
        'baptism' => 500,'wedding' => 3000,'funeral_mass' => 1500,
        'house_blessing' => 300,'car_blessing' => 200,'business_blessing' => 300,
        'sick_call' => 0,'pre_baptismal' => 100,'pre_marriage' => 500,
        'confirmation_catechesis' => 200,'mass_intention' => 200,
    ];

    public function run(): void
    {
        $this->qrService = app(QrCodeService::class);
        $this->secretary = User::where('email','secretary@mhcparish.ph')->first();
        $this->admin     = User::where('email','admin@mhcparish.ph')->first();
        $this->finance   = User::where('email','finance@mhcparish.ph')->first();

        $this->command->info('Seeding analytics data (12 months)...');

        // ── 1. Create 60 additional parishioners spread over 12 months ──
        $this->command->line('  Creating parishioners...');
        $parishioners = $this->createParishioners(60);

        // ── 2. Create sacramental records (historical) ──
        $this->command->line('  Creating sacramental records...');
        $this->createSacramentalRecords($parishioners);

        // ── 3. Create bookings across 12 months ──
        $this->command->line('  Creating bookings...');
        $bookings = $this->createBookings($parishioners);

        // ── 4. Create payments for completed bookings ──
        $this->command->line('  Creating payments...');
        $this->createPayments($bookings);

        // ── 5. Create certificates ──
        $this->command->line('  Creating certificates...');
        $this->createCertificates($parishioners);

        // ── 6. Create additional announcements ──
        $this->command->line('  Creating announcements...');
        $this->createAnnouncements();

        // ── 7. Summary ──
        $this->command->newLine();
        $this->command->info('✅ Analytics data seeded!');
        $this->command->newLine();
        $this->command->line('  <fg=green>TOTALS:</>');
        $this->command->line('    Parishioners:        ' . Parishioner::count());
        $this->command->line('    Sacramental Records: ' . SacramentalRecord::count());
        $this->command->line('    Bookings:            ' . Booking::count());
        $this->command->line('    Payments:            ' . Payment::count());
        $this->command->line('    Revenue (paid):      ₱' . number_format(Payment::paid()->sum('amount'), 2));
        $this->command->line('    Certificates:        ' . Certificate::count());
        $this->command->line('    Announcements:       ' . Announcement::count());
        $this->command->newLine();
    }

    // ── PARISHIONERS ─────────────────────────────────────────────────────────
    private function createParishioners(int $count): array
    {
        $parishioners = [];
        $families     = Family::all();

        // Create some new families too
        $newFamilies = [];
        for ($f = 0; $f < 15; $f++) {
            $ln = $this->lastNames[array_rand($this->lastNames)];
            $newFamilies[] = Family::create([
                'family_name'    => $ln . ' Family',
                'barangay'       => $this->barangays[array_rand($this->barangays)],
                'city'           => 'Cabuyao',
                'province'       => 'Laguna',
                'contact_number' => '09' . rand(100,999) . rand(1000000,9999999),
            ]);
        }
        $allFamilies = $families->concat(collect($newFamilies));

        for ($i = 0; $i < $count; $i++) {
            $fn = $this->firstNames[array_rand($this->firstNames)];
            $ln = $this->lastNames[array_rand($this->lastNames)];
            $gender = (rand(0,1) === 0) ? 'male' : 'female';

            // Spread creation dates over 12 months
            $createdAt = now()->subMonths(rand(0, 11))->subDays(rand(0, 28));

            $p = Parishioner::create([
                'family_id'           => $allFamilies->random()->id,
                'first_name'          => $fn,
                'middle_name'         => $this->lastNames[array_rand($this->lastNames)],
                'last_name'           => $ln,
                'birthdate'           => now()->subYears(rand(5, 80))->subDays(rand(0, 365))->toDateString(),
                'gender'              => $gender,
                'civil_status'        => $this->randomCivilStatus(),
                'address'             => 'Blk ' . rand(1,20) . ' Lot ' . rand(1,30) . ', Phase ' . rand(1,5),
                'barangay'            => $this->barangays[array_rand($this->barangays)],
                'city'                => 'Cabuyao',
                'province'            => 'Laguna',
                'postal_code'         => '4025',
                'contact_number'      => '09' . rand(100,999) . rand(1000000,9999999),
                'email'               => strtolower($fn) . '.' . strtolower($ln) . rand(10,99) . '@gmail.com',
                'is_active'           => true,
                'created_at'          => $createdAt,
                'updated_at'          => $createdAt,
            ]);

            $parishioners[] = $p;
        }

        return $parishioners;
    }

    // ── SACRAMENTAL RECORDS ───────────────────────────────────────────────────
    private function createSacramentalRecords(array $parishioners): void
    {
        $types = [
            'baptism'        => 18,
            'first_communion'=> 8,
            'confirmation'   => 6,
            'marriage'       => 5,
            'death_burial'   => 4,
        ];

        $celebrants = [
            'Rev. Fr. Miguel Fernandez',
            'Rev. Fr. Antonio Villanueva',
            'Rev. Fr. Jose Bautista',
            'Rev. Fr. Erwin S. Sanchez',
        ];

        foreach ($types as $type => $targetCount) {
            $created = 0;
            $shuffled = $parishioners;
            shuffle($shuffled);

            foreach ($shuffled as $p) {
                if ($created >= $targetCount) break;

                // Skip if already has this type
                if (SacramentalRecord::where('parishioner_id', $p->id)->where('type', $type)->exists()) {
                    continue;
                }

                // Spread dates over 12 months
                $date = now()->subMonths(rand(0, 11))->subDays(rand(0, 28));

                $data = [
                    'parishioner_id'    => $p->id,
                    'type'              => $type,
                    'date_administered' => $date->toDateString(),
                    'celebrant'         => $celebrants[array_rand($celebrants)],
                    'venue'             => 'Mary Help of Christians Parish',
                    'register_number'   => strtoupper(substr($type, 0, 1)) . '-' . $date->format('Y') . '-' . str_pad(rand(1, 200), 3, '0', STR_PAD_LEFT),
                    'page_number'       => (string) rand(1, 50),
                    'line_number'       => (string) rand(1, 30),
                    'recorded_by'       => $this->secretary?->id ?? $this->admin->id,
                    'created_at'        => $date,
                    'updated_at'        => $date,
                ];

                if ($type === 'baptism') {
                    $data['godparents'] = [
                        $this->firstNames[array_rand($this->firstNames)] . ' ' . $this->lastNames[array_rand($this->lastNames)],
                        $this->firstNames[array_rand($this->firstNames)] . ' ' . $this->lastNames[array_rand($this->lastNames)],
                    ];
                    $data['notes'] = $this->firstNames[array_rand($this->firstNames)] . ' ' . $this->lastNames[array_rand($this->lastNames)]
                        . ' and ' . $this->firstNames[array_rand($this->firstNames)] . ' ' . $this->lastNames[array_rand($this->lastNames)];
                }

                if (in_array($type, ['confirmation', 'marriage'])) {
                    $data['sponsors'] = [
                        $this->firstNames[array_rand($this->firstNames)] . ' ' . $this->lastNames[array_rand($this->lastNames)],
                    ];
                }

                // 70% chance of being verified
                if (rand(1, 10) <= 7) {
                    $data['verified_by'] = $this->admin->id;
                    $data['verified_at'] = $date->copy()->addDays(rand(1, 5));
                }

                SacramentalRecord::create($data);
                $created++;
            }
        }
    }

    // ── BOOKINGS ──────────────────────────────────────────────────────────────
    private function createBookings(array $parishioners): array
    {
        $bookings = [];

        // Distribution: more bookings in recent months, fewer in older months
        $monthlyTargets = [
            11 => 4,  // 11 months ago
            10 => 5,
            9  => 6,
            8  => 7,
            7  => 8,
            6  => 9,
            5  => 10,
            4  => 11,
            3  => 12,
            2  => 13,
            1  => 14,
            0  => 8,  // current month (partial)
        ];

        foreach ($monthlyTargets as $monthsAgo => $target) {
            for ($i = 0; $i < $target; $i++) {
                $p    = $parishioners[array_rand($parishioners)];
                $type = $this->bookingTypes[array_rand($this->bookingTypes)];
                $fee  = $this->bookingFees[$type] ?? 200;

                // Date within that month
                $baseDate = now()->subMonths($monthsAgo);
                $day      = rand(1, min(28, $baseDate->daysInMonth));
                $date     = Carbon::create($baseDate->year, $baseDate->month, $day);

                // Status based on age
                if ($monthsAgo >= 2) {
                    $status = (rand(1,10) <= 8) ? 'completed' : 'cancelled';
                } elseif ($monthsAgo === 1) {
                    $statuses = ['completed','completed','completed','confirmed','cancelled'];
                    $status   = $statuses[array_rand($statuses)];
                } else {
                    $statuses = ['pending','pending','confirmed','confirmed','completed'];
                    $status   = $statuses[array_rand($statuses)];
                }

                $bookingData = [
                    'parishioner_id' => $p->id,
                    'booking_type'   => $type,
                    'scheduled_date' => $date->toDateString(),
                    'scheduled_time' => $this->randomTime(),
                    'service_fee'    => $fee,
                    'status'         => $status,
                    'notes'          => $this->randomNote($type),
                    'reminder_sent'  => $status !== 'pending',
                    'created_at'     => $date->copy()->subDays(rand(3, 14)),
                    'updated_at'     => $date,
                ];

                if (in_array($status, ['confirmed','completed'])) {
                    $bookingData['confirmed_by'] = $this->secretary?->id ?? $this->admin->id;
                    $bookingData['confirmed_at'] = $date->copy()->subDays(rand(1, 5));
                    $bookingData['admin_notes']  = 'Confirmed. Please bring required documents.';
                }

                if ($status === 'cancelled') {
                    $bookingData['cancelled_by']        = $p->user?->id ?? $this->admin->id;
                    $bookingData['cancelled_at']        = $date->copy()->subDays(rand(1, 3));
                    $bookingData['cancellation_reason'] = $this->randomCancelReason();
                }

                $booking = Booking::create($bookingData);

                // Generate QR for non-cancelled bookings
                if ($status !== 'cancelled' && !$booking->qrCode) {
                    try { $this->qrService->generateForBooking($booking); } catch (\Exception $e) {}
                }

                $bookings[] = $booking;
            }
        }

        return $bookings;
    }

    // ── PAYMENTS ──────────────────────────────────────────────────────────────
    private function createPayments(array $bookings): void
    {
        $methods = ['gcash','gcash','gcash','maya','maya','cash','cash','cash'];

        foreach ($bookings as $booking) {
            if ($booking->service_fee <= 0) continue;
            if ($booking->status === 'cancelled') continue;
            if ($booking->payment) continue; // already has payment

            // Completed bookings: 90% paid
            // Confirmed bookings: 60% paid
            // Pending bookings: 20% paid
            $payChance = match($booking->status) {
                'completed' => 90,
                'confirmed' => 60,
                'pending'   => 20,
                default     => 0,
            };

            if (rand(1, 100) > $payChance) {
                // Create pending payment
                if (rand(1,3) === 1) {
                    Payment::create([
                        'parishioner_id' => $booking->parishioner_id,
                        'booking_id'     => $booking->id,
                        'amount'         => $booking->service_fee,
                        'payment_method' => $methods[array_rand($methods)],
                        'status'         => 'pending',
                        'created_at'     => $booking->scheduled_date,
                        'updated_at'     => $booking->scheduled_date,
                    ]);
                }
                continue;
            }

            $method  = $methods[array_rand($methods)];
            $paidAt  = Carbon::parse($booking->scheduled_date)->subDays(rand(0, 3));

            Payment::create([
                'parishioner_id'    => $booking->parishioner_id,
                'booking_id'        => $booking->id,
                'amount'            => $booking->service_fee,
                'payment_method'    => $method,
                'status'            => 'paid',
                'gateway_reference' => in_array($method, ['gcash','maya'])
                    ? strtoupper($method) . '-' . strtoupper(uniqid())
                    : null,
                'paid_at'           => $paidAt,
                'notes'             => ucfirst($method) . ' payment for ' . $booking->getTypeLabel(),
                'created_at'        => $paidAt,
                'updated_at'        => $paidAt,
            ]);
        }
    }

    // ── CERTIFICATES ──────────────────────────────────────────────────────────
    private function createCertificates(array $parishioners): void
    {
        $certTypes = ['baptism','confirmation','marriage','first_communion','membership','no_impediment'];
        $target    = 25;
        $created   = 0;

        shuffle($parishioners);

        foreach ($parishioners as $p) {
            if ($created >= $target) break;

            $type    = $certTypes[array_rand($certTypes)];
            $issuedAt = now()->subMonths(rand(0, 11))->subDays(rand(0, 28));

            // Find matching sacramental record
            $record = SacramentalRecord::where('parishioner_id', $p->id)
                ->where('type', $type)
                ->first();

            $statuses = ['draft','issued','issued','released','released'];
            $status   = $statuses[array_rand($statuses)];

            // Use DB transaction to avoid race condition on certificate_number
            try {
                $cert = DB::transaction(function () use ($p, $type, $issuedAt, $record, $status) {
                    return Certificate::create([
                        'parishioner_id'        => $p->id,
                        'sacramental_record_id' => $record?->id,
                        'type'                  => $type,
                        'issued_date'           => $issuedAt->toDateString(),
                        'issued_by'             => $this->secretary?->id ?? $this->admin->id,
                        'purpose'               => $this->randomPurpose(),
                        'status'                => $status,
                        'created_at'            => $issuedAt,
                        'updated_at'            => $issuedAt,
                    ]);
                });

                if ($status !== 'draft') {
                    try { $this->qrService->generateForCertificate($cert); } catch (\Exception $e) {}
                }

                $created++;

            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                // Certificate number collision — skip and continue
                continue;
            }
        }
    }

    // ── ANNOUNCEMENTS ─────────────────────────────────────────────────────────
    private function createAnnouncements(): void
    {
        $announcements = [
            [
                'title'    => 'Simbang Gabi 2025 Schedule',
                'content'  => '<p>Join us for the traditional <strong>Simbang Gabi</strong> (Dawn Mass) from December 16–24, 2025 at 4:00 AM daily. This nine-day novena of Masses is a beloved Filipino tradition in preparation for Christmas.</p><p>Special activities after each Mass including traditional Filipino breakfast and fellowship.</p>',
                'category' => 'Schedule',
                'months_ago' => 5,
            ],
            [
                'title'    => 'Parish Anniversary Mass — October 2025',
                'content'  => '<p>Celebrate our parish anniversary with a solemn Mass on <strong>October 15, 2025</strong> at 9:00 AM. All parishioners are invited to join this special celebration of our community\'s faith journey.</p>',
                'category' => 'Event',
                'months_ago' => 7,
            ],
            [
                'title'    => 'All Souls Day Mass Schedule',
                'content'  => '<p>In remembrance of our departed loved ones, we will celebrate special Masses on <strong>November 1–2, 2025</strong>.</p><ul><li>November 1 (All Saints Day): 6AM, 8AM, 10AM, 6PM</li><li>November 2 (All Souls Day): 6AM, 8AM, 6PM with cemetery blessing at 3PM</li></ul>',
                'category' => 'Schedule',
                'months_ago' => 6,
            ],
            [
                'title'    => 'Youth Ministry Summer Camp 2026',
                'content'  => '<p>The Parish Youth Ministry invites all young parishioners aged 13–25 to join our <strong>Summer Faith Camp</strong> on April 10–12, 2026 at Camp Crame, Quezon City.</p><p>Registration fee: ₱800 (inclusive of meals and accommodation). Limited slots available!</p>',
                'category' => 'Event',
                'months_ago' => 2,
            ],
            [
                'title'    => 'Lenten Season Schedule 2026',
                'content'  => '<p>Special Lenten activities for 2026:</p><ul><li><strong>Ash Wednesday</strong> (Feb 18): 6AM, 12NN, 6PM</li><li><strong>Stations of the Cross</strong>: Every Friday of Lent at 6PM</li><li><strong>Reconciliation Services</strong>: March 20 & 27 at 7PM</li></ul>',
                'category' => 'Schedule',
                'months_ago' => 3,
            ],
            [
                'title'    => 'New Parish Hall Renovation Complete',
                'content'  => '<p>We are pleased to announce that the renovation of our Parish Hall is now complete! The newly renovated hall can accommodate up to 300 guests and is available for booking for parish events, seminars, and community gatherings.</p><p>Contact the parish office for reservations.</p>',
                'category' => 'Announcement',
                'months_ago' => 4,
            ],
            [
                'title'    => 'Couples for Christ Anniversary Celebration',
                'content'  => '<p>The Couples for Christ community of Mary Help of Christians Parish celebrates its <strong>10th Anniversary</strong> on March 8, 2026 with a thanksgiving Mass at 9AM followed by a fellowship lunch.</p>',
                'category' => 'Event',
                'months_ago' => 2,
            ],
            [
                'title'    => 'First Friday Mass and Adoration',
                'content'  => '<p>Join us every <strong>First Friday</strong> of the month for a special Mass at 6PM followed by Eucharistic Adoration until 9PM. This month\'s First Friday is dedicated to the Sacred Heart of Jesus.</p>',
                'category' => 'Schedule',
                'months_ago' => 1,
            ],
            [
                'title'    => 'Parish Outreach Program — Southville 5',
                'content'  => '<p>Our Social Action Ministry will conduct a medical and dental mission at Southville 5 on <strong>June 14, 2026</strong>. Volunteers are needed! Please sign up at the parish office.</p>',
                'category' => 'Announcement',
                'months_ago' => 0,
            ],
            [
                'title'    => 'Baptism Schedule — Every 3rd Sunday',
                'content'  => '<p>Baptisms are scheduled every <strong>3rd Sunday of the month</strong> at 11:00 AM. Parents must attend the Pre-Baptismal Seminar before the baptism date.</p><p>Next baptism date: June 21, 2026. Register at the parish office.</p>',
                'category' => 'Announcement',
                'months_ago' => 0,
            ],
        ];

        foreach ($announcements as $ann) {
            $publishedAt = now()->subMonths($ann['months_ago'])->subDays(rand(1, 10));
            Announcement::firstOrCreate(
                ['title' => $ann['title']],
                [
                    'content'      => $ann['content'],
                    'category'     => $ann['category'],
                    'is_published' => true,
                    'published_at' => $publishedAt,
                    'expires_at'   => null,
                    'created_by'   => $this->admin->id,
                    'created_at'   => $publishedAt,
                    'updated_at'   => $publishedAt,
                ]
            );
        }
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────
    private function randomCivilStatus(): string
    {
        $statuses = ['single','single','married','married','married','widowed','separated'];
        return $statuses[array_rand($statuses)];
    }

    private function randomTime(): string
    {
        $times = ['06:00:00','08:00:00','09:00:00','10:00:00','14:00:00','15:00:00','16:00:00'];
        return $times[array_rand($times)];
    }

    private function randomNote(string $type): string
    {
        $notes = [
            'baptism'        => ['Baptism for our newborn.','First child baptism.','Delayed baptism.'],
            'wedding'        => ['Church wedding ceremony.','Simple wedding.','Garden wedding reception after.'],
            'funeral_mass'   => ['Funeral Mass for our beloved.','Memorial Mass.','Requiem Mass.'],
            'house_blessing' => ['New house blessing.','Renovation blessing.','Annual house blessing.'],
            'car_blessing'   => ['New car blessing.','Annual vehicle blessing.'],
            'mass_intention' => ['For the soul of my father.','For healing of my mother.','Thanksgiving Mass.'],
            'sick_call'      => ['Anointing for elderly parent.','Sick call for hospitalized family member.'],
        ];
        $pool = $notes[$type] ?? ['Parish service booking.'];
        return $pool[array_rand($pool)];
    }

    private function randomCancelReason(): string
    {
        $reasons = [
            'Schedule conflict, will rebook.',
            'Family emergency.',
            'Postponed to next month.',
            'Change of plans.',
            'Priest unavailable on requested date.',
        ];
        return $reasons[array_rand($reasons)];
    }

    private function randomPurpose(): string
    {
        $purposes = [
            'School enrollment requirement',
            'Employment requirement',
            'Travel abroad',
            'Legal documentation',
            'Scholarship application',
            'Marriage preparation',
            'Confirmation requirement',
            'Record keeping',
            'Government requirement',
        ];
        return $purposes[array_rand($purposes)];
    }
}
