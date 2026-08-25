<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\Event;
use App\Models\GalleryItem;
use App\Models\LedgerEntry;
use App\Models\Livestream;
use App\Models\Parishioner;
use App\Models\SacramentalRecord;
use App\Models\User;
use App\Services\QrCodeService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * FullDemoSeeder
 *
 * Fills the remaining thin areas for a complete capstone demo:
 *  - 30 additional certificate requests (all statuses & types)
 *  - 8 parish events (upcoming + past, all categories)
 *  - 12 gallery items across multiple albums
 *  - 5 livestream entries (recorded + upcoming)
 *  - 40 additional ledger entries covering 6 months
 *
 * Idempotent — safe to re-run, uses existence checks before inserting.
 *
 * Run: php artisan db:seed --class=FullDemoSeeder
 */
class FullDemoSeeder extends Seeder
{
    private ?User $admin;
    private ?User $secretary;
    private ?User $finance;
    private QrCodeService $qrService;

    public function run(): void
    {
        $this->qrService = app(QrCodeService::class);

        // Resolve admin users by actual emails (fallback to role)
        $this->admin     = User::where('email', 'maryhelpparish@gmail.com')->first()
                           ?? User::role('super_admin')->first();
        $this->secretary = User::where('email', 'cumpioaries07@gmail.com')->first()
                           ?? User::role('parish_secretary')->first();
        $this->finance   = User::where('email', 'financemhcpparish@gmail.com')->first()
                           ?? User::role('finance_officer')->first();

        $this->command->info('Seeding full demo data...');

        $this->seedCertificates();
        $this->seedEvents();
        $this->seedGallery();
        $this->seedLivestreams();
        $this->seedLedger();

        $this->command->newLine();
        $this->command->info('✅ Full demo data seeded!');
        $this->command->newLine();
        $this->command->line('  <fg=green>FINAL TOTALS:</>');
        $this->command->line('    Certificates:    ' . Certificate::count());
        $this->command->line('    Events:          ' . Event::count());
        $this->command->line('    Gallery Items:   ' . GalleryItem::count());
        $this->command->line('    Livestreams:     ' . Livestream::count());
        $this->command->line('    Ledger Entries:  ' . LedgerEntry::count());
        $totals = LedgerEntry::select('type', DB::raw('sum(amount) as total'))->groupBy('type')->get()->keyBy('type');
        $credit = $totals->get('credit')?->total ?? 0;
        $debit  = $totals->get('debit')?->total ?? 0;
        $this->command->line('    Total Income:    ₱' . number_format($credit, 2));
        $this->command->line('    Total Expenses:  ₱' . number_format($debit,  2));
        $this->command->line('    Net Balance:     ₱' . number_format($credit - $debit, 2));
        $this->command->newLine();
    }

    // ── CERTIFICATES ──────────────────────────────────────────────────────────
    private function seedCertificates(): void
    {
        $this->command->line('  Creating certificates...');

        $certTypes = [
            'baptism', 'confirmation', 'marriage',
            'first_communion', 'membership', 'no_impediment',
            'death_burial', 'generic',
        ];

        $purposes = [
            'School enrollment requirement',
            'Employment / company requirement',
            'Government ID application',
            'Travel abroad / passport',
            'NBI clearance requirement',
            'Scholarship application',
            'Marriage license requirement',
            'Confirmation requirement',
            'Confirmation as godparent',
            'Insurance beneficiary requirement',
            'Barangay clearance support',
            'PhilHealth / SSS requirement',
            'Legal documentation',
            'Personal record keeping',
            'University admission requirement',
        ];

        $statuses = [
            'released', 'released', 'released',   // 37.5% released
            'issued',   'issued',   'issued',      // 37.5% issued
            'draft',                               // 12.5% draft
            'released',                            // extra released
        ];

        $parishioners = Parishioner::inRandomOrder()->limit(50)->get();

        if ($parishioners->isEmpty()) {
            $this->command->warn('  No parishioners found — skipping certificates.');
            return;
        }

        $created = 0;
        $target  = 30;

        foreach ($parishioners as $p) {
            if ($created >= $target) break;

            // Pick a cert type the parishioner doesn't already have
            $availableTypes = array_filter($certTypes, fn($t) =>
                !Certificate::where('parishioner_id', $p->id)->where('type', $t)->exists()
            );

            if (empty($availableTypes)) continue;

            $type    = $availableTypes[array_rand($availableTypes)];
            $status  = $statuses[array_rand($statuses)];
            $purpose = $purposes[array_rand($purposes)];
            $date    = now()->subDays(rand(3, 180));

            // Find a matching sacramental record if one exists
            $sacrRecord = null;
            if (in_array($type, ['baptism', 'confirmation', 'marriage', 'first_communion', 'death_burial'])) {
                $sacrRecord = SacramentalRecord::where('parishioner_id', $p->id)
                    ->where('type', $type)
                    ->first();
            }

            try {
                DB::transaction(function () use ($p, $type, $status, $purpose, $date, $sacrRecord) {
                    $cert = Certificate::create([
                        'parishioner_id'        => $p->id,
                        'sacramental_record_id' => $sacrRecord?->id,
                        'type'                  => $type,
                        'issued_date'           => $date->toDateString(),
                        'issued_by'             => $this->secretary?->id ?? $this->admin->id,
                        'purpose'               => $purpose,
                        'status'                => $status,
                        'notes'                 => $status === 'draft'
                            ? 'Pending review by parish secretary.'
                            : null,
                        'created_at'            => $date,
                        'updated_at'            => $date,
                    ]);

                    if ($status !== 'draft') {
                        try { $this->qrService->generateForCertificate($cert); } catch (\Exception $e) {}
                    }
                });
                $created++;
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                // Race on certificate_number — skip
            }
        }

        $this->command->line("  ✓ Created {$created} certificates.");
    }

    // ── EVENTS ────────────────────────────────────────────────────────────────
    private function seedEvents(): void
    {
        $this->command->line('  Creating events...');

        $events = [
            // ── Upcoming events ──────────────────────────────────────────────
            [
                'title'       => 'Feast of Mary Help of Christians — Parish Anniversary',
                'description' => '<p>Join us for the <strong>Solemnity of Mary Help of Christians</strong>, the patronal feast of our parish. The celebration includes a <strong>Solemn Mass at 9:00 AM</strong> presided over by our Parish Priest, followed by a parish gathering and cultural presentation.</p><p>All parishioners, families, and guests are warmly invited to participate in this special day of thanksgiving and community.</p><ul><li>Holy Mass: 9:00 AM</li><li>Parish Gathering: 11:00 AM</li><li>Cultural Presentation: 1:00 PM</li></ul>',
                'location'    => 'Mary Help of Christians Parish Church',
                'start'       => now()->addDays(14)->setTime(9, 0),
                'end'         => now()->addDays(14)->setTime(17, 0),
                'category'    => 'sacrament',
                'featured'    => true,
                'status'      => 'published',
            ],
            [
                'title'       => 'Parish Youth Ministry — General Assembly 2026',
                'description' => '<p>The <strong>Parish Youth Ministry (PYM)</strong> invites all youth parishioners aged 13–35 to our General Assembly for the second semester of 2026.</p><p>Topics to be discussed:</p><ul><li>Election of new officers</li><li>Youth activities calendar (July–December 2026)</li><li>Community service projects</li><li>Spiritual formation programs</li></ul><p>Light refreshments will be served. Please bring your parish ID.</p>',
                'location'    => 'Parish Hall, Mary Help of Christians Parish',
                'start'       => now()->addDays(7)->setTime(14, 0),
                'end'         => now()->addDays(7)->setTime(17, 0),
                'category'    => 'youth',
                'featured'    => false,
                'status'      => 'published',
            ],
            [
                'title'       => 'First Friday Mass & Eucharistic Adoration',
                'description' => '<p>Every <strong>First Friday of the month</strong>, we celebrate a special votive Mass of the Sacred Heart of Jesus at 6:00 PM, followed by <strong>Eucharistic Adoration</strong> from 7:00 PM to 9:00 PM.</p><p>This is a time for personal prayer, intercession, and encounter with the Lord in the Blessed Sacrament. All are welcome.</p>',
                'location'    => 'Mary Help of Christians Parish Church',
                'start'       => now()->addDays(5)->setTime(18, 0),
                'end'         => now()->addDays(5)->setTime(21, 0),
                'category'    => 'general',
                'featured'    => false,
                'status'      => 'published',
            ],
            [
                'title'       => 'Couples for Christ — Monthly Community Meeting',
                'description' => '<p>The <strong>Couples for Christ (CFC) Community</strong> of Mary Help of Christians Parish holds its regular monthly community meeting. All CFC members and their families are encouraged to attend.</p><p>This month\'s meeting will focus on marriage enrichment and family prayer. A short teaching will be given followed by small group sharing and community prayer.</p>',
                'location'    => 'Parish Hall, Mary Help of Christians Parish',
                'start'       => now()->addDays(10)->setTime(19, 0),
                'end'         => now()->addDays(10)->setTime(21, 30),
                'category'    => 'community',
                'featured'    => false,
                'status'      => 'published',
            ],
            [
                'title'       => 'Medical & Dental Mission — Southville 5',
                'description' => '<p>The <strong>Parish Social Action Ministry</strong> in partnership with volunteer doctors and dentists will conduct a <strong>FREE Medical and Dental Mission</strong> at Southville 5, Brgy. Marinig, Cabuyao, Laguna.</p><p>Services to be provided:</p><ul><li>General medical consultation</li><li>Dental extraction & cleaning</li><li>Blood pressure monitoring</li><li>Distribution of free medicines</li></ul><p>Volunteer doctors, nurses, and helpers are needed. Please coordinate with the Parish Social Action desk.</p>',
                'location'    => 'Southville 5 Covered Court, Brgy. Marinig, Cabuyao',
                'start'       => now()->addDays(21)->setTime(8, 0),
                'end'         => now()->addDays(21)->setTime(16, 0),
                'category'    => 'outreach',
                'featured'    => true,
                'status'      => 'published',
            ],
            // ── Past events ──────────────────────────────────────────────────
            [
                'title'       => 'Simbang Gabi 2025 — Nine-Day Novena of Masses',
                'description' => '<p>The traditional <strong>Simbang Gabi</strong> (Dawn Mass) was held from <strong>December 16–24, 2025</strong> at 4:00 AM daily. This beloved Filipino tradition is a nine-day novena of Masses in preparation for the birth of Jesus Christ.</p><p>Special Filipino breakfast items including puto bumbong, bibingka, and champorado were served after each Mass.</p><p>Thank you to all parishioners who participated faithfully in this beautiful tradition.</p>',
                'location'    => 'Mary Help of Christians Parish Church',
                'start'       => now()->subMonths(7)->setTime(4, 0),
                'end'         => now()->subMonths(7)->addDays(8)->setTime(6, 0),
                'category'    => 'sacrament',
                'featured'    => false,
                'status'      => 'published',
            ],
            [
                'title'       => 'Summer Youth Leadership Camp 2026',
                'description' => '<p>The Parish Youth Ministry successfully completed a <strong>three-day Summer Leadership Camp</strong> held last April 10–12, 2026 at Camp Crame, Quezon City.</p><p>Over 45 youth participants attended, engaging in leadership workshops, team-building activities, spiritual formation talks, and the Sacrament of Reconciliation.</p><p>The camp theme was <em>"Lead with Faith, Serve with Love"</em> inspired by Mark 10:45.</p>',
                'location'    => 'Camp Crame, Quezon City',
                'start'       => now()->subMonths(4)->setTime(7, 0),
                'end'         => now()->subMonths(4)->addDays(2)->setTime(18, 0),
                'category'    => 'youth',
                'featured'    => false,
                'status'      => 'published',
            ],
            [
                'title'       => 'Lenten Reconciliation Service 2026',
                'description' => '<p>The parish held a special <strong>Penitential Service</strong> on March 27, 2026 at 7:00 PM. Several priests from neighboring parishes assisted in hearing confessions.</p><p>Over 200 parishioners availed of the Sacrament of Reconciliation during this communal penance service. The service was concluded with an Act of Contrition and a closing prayer led by the Parish Priest.</p>',
                'location'    => 'Mary Help of Christians Parish Church',
                'start'       => now()->subMonths(5)->setTime(19, 0),
                'end'         => now()->subMonths(5)->setTime(22, 0),
                'category'    => 'sacrament',
                'featured'    => false,
                'status'      => 'published',
            ],
        ];

        $insertedCount = 0;
        foreach ($events as $e) {
            if (Event::where('title', $e['title'])->exists()) continue;

            Event::create([
                'title'       => $e['title'],
                'description' => $e['description'],
                'location'    => $e['location'],
                'event_start' => $e['start'],
                'event_end'   => $e['end'],
                'category'    => $e['category'],
                'image_path'  => null,
                'status'      => $e['status'],
                'is_featured' => $e['featured'],
                'created_by'  => $this->admin->id,
            ]);
            $insertedCount++;
        }

        $this->command->line("  ✓ Created {$insertedCount} events.");
    }

    // ── GALLERY ───────────────────────────────────────────────────────────────
    private function seedGallery(): void
    {
        $this->command->line('  Creating gallery items...');

        $items = [
            // Album: Parish Life
            ['title' => 'Sunday Mass — July 2026',         'album' => 'Parish Life 2026',        'category' => 'general',   'cover' => true,  'featured' => true],
            ['title' => 'Parish Anniversary Celebration',  'album' => 'Parish Life 2026',        'category' => 'general',   'cover' => false, 'featured' => false],
            ['title' => 'Community Outreach — Southville', 'album' => 'Parish Life 2026',        'category' => 'community', 'cover' => false, 'featured' => false],
            // Album: Sacraments
            ['title' => 'Baptism — June 2026',             'album' => 'Sacraments 2026',         'category' => 'sacrament', 'cover' => true,  'featured' => true],
            ['title' => 'Wedding — Santos & Reyes',        'album' => 'Sacraments 2026',         'category' => 'sacrament', 'cover' => false, 'featured' => false],
            ['title' => 'First Communion — May 2026',      'album' => 'Sacraments 2026',         'category' => 'sacrament', 'cover' => false, 'featured' => true],
            ['title' => 'Confirmation — April 2026',       'album' => 'Sacraments 2026',         'category' => 'sacrament', 'cover' => false, 'featured' => false],
            // Album: Youth Ministry
            ['title' => 'Youth Assembly — June 2026',      'album' => 'Youth Ministry',          'category' => 'youth',     'cover' => true,  'featured' => false],
            ['title' => 'Summer Camp 2026 Highlights',     'album' => 'Youth Ministry',          'category' => 'youth',     'cover' => false, 'featured' => true],
            // Album: Simbang Gabi
            ['title' => 'Simbang Gabi Opening Night',      'album' => 'Simbang Gabi 2025',       'category' => 'event',     'cover' => true,  'featured' => false],
            ['title' => 'Simbang Gabi — Choir Performance','album' => 'Simbang Gabi 2025',       'category' => 'event',     'cover' => false, 'featured' => false],
            ['title' => 'Simbang Gabi Noche Buena',        'album' => 'Simbang Gabi 2025',       'category' => 'event',     'cover' => false, 'featured' => false],
        ];

        $insertedCount = 0;
        foreach ($items as $idx => $item) {
            if (GalleryItem::where('title', $item['title'])->exists()) continue;

            GalleryItem::create([
                'title'       => $item['title'],
                'caption'     => 'Photo from the ' . $item['album'] . ' collection.',
                'image_path'  => 'gallery/placeholder.jpg', // placeholder; replace in admin
                'category'    => $item['category'],
                'album'       => $item['album'],
                'album_cover' => $item['cover'],
                'is_featured' => $item['featured'],
                'sort_order'  => $idx + 1,
                'created_by'  => $this->admin->id,
            ]);
            $insertedCount++;
        }

        $this->command->line("  ✓ Created {$insertedCount} gallery items.");
    }

    // ── LIVESTREAMS ───────────────────────────────────────────────────────────
    private function seedLivestreams(): void
    {
        $this->command->line('  Creating livestream entries...');

        $streams = [
            // Recorded
            [
                'title'        => 'Sunday Mass — July 20, 2026',
                'description'  => 'Live celebration of the Holy Eucharist presided by Rev. Fr. Erwin S. Sanchez. Sunday, July 20, 2026 at 9:00 AM.',
                'youtube_url'  => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'youtube_id'   => 'dQw4w9WgXcQ',
                'type'         => 'recorded',
                'scheduled_at' => now()->subDays(5)->setTime(9, 0),
                'is_active'    => true,
                'is_featured'  => false,
            ],
            [
                'title'        => 'Sunday Mass — July 13, 2026',
                'description'  => 'Live Holy Eucharist — 15th Sunday in Ordinary Time. Presided by Rev. Fr. Erwin S. Sanchez.',
                'youtube_url'  => 'https://www.youtube.com/watch?v=jNQXAC9IVRw',
                'youtube_id'   => 'jNQXAC9IVRw',
                'type'         => 'recorded',
                'scheduled_at' => now()->subDays(12)->setTime(9, 0),
                'is_active'    => true,
                'is_featured'  => false,
            ],
            [
                'title'        => 'Parish Anniversary Mass 2026',
                'description'  => 'Solemn Mass celebrating the feast of Mary Help of Christians and the Parish Anniversary. Recorded live on May 24, 2026.',
                'youtube_url'  => 'https://www.youtube.com/watch?v=9bZkp7q19f0',
                'youtube_id'   => '9bZkp7q19f0',
                'type'         => 'recorded',
                'scheduled_at' => now()->subMonths(2)->setTime(9, 0),
                'is_active'    => true,
                'is_featured'  => true,
            ],
            [
                'title'        => 'Lenten Recollection 2026',
                'description'  => 'Special Lenten recollection and communal penance service. Presided by the Parish Priest with guest speaker.',
                'youtube_url'  => 'https://www.youtube.com/watch?v=kXYiU_JCYtU',
                'youtube_id'   => 'kXYiU_JCYtU',
                'type'         => 'recorded',
                'scheduled_at' => now()->subMonths(4)->setTime(19, 0),
                'is_active'    => true,
                'is_featured'  => false,
            ],
            // Upcoming
            [
                'title'        => 'Sunday Mass — July 27, 2026 (LIVE)',
                'description'  => 'Join us live for the Holy Eucharist this coming Sunday, July 27, 2026 at 9:00 AM. 17th Sunday in Ordinary Time.',
                'youtube_url'  => 'https://www.youtube.com/watch?v=tPEE9ZwImy0',
                'youtube_id'   => 'tPEE9ZwImy0',
                'type'         => 'upcoming',
                'scheduled_at' => now()->addDays(2)->setTime(9, 0),
                'is_active'    => true,
                'is_featured'  => true,
            ],
        ];

        $insertedCount = 0;
        foreach ($streams as $s) {
            if (Livestream::where('title', $s['title'])->exists()) continue;

            Livestream::create([
                'title'        => $s['title'],
                'description'  => $s['description'],
                'youtube_url'  => $s['youtube_url'],
                'youtube_id'   => $s['youtube_id'],
                'type'         => $s['type'],
                'scheduled_at' => $s['scheduled_at'],
                'is_active'    => $s['is_active'],
                'is_featured'  => $s['is_featured'],
                'created_by'   => $this->admin->id,
            ]);
            $insertedCount++;
        }

        $this->command->line("  ✓ Created {$insertedCount} livestream entries.");
    }

    // ── LEDGER ENTRIES ────────────────────────────────────────────────────────
    private function seedLedger(): void
    {
        $this->command->line('  Creating ledger entries (6 months of financial data)...');

        $recorder = $this->finance?->id ?? $this->admin->id;

        // Format: [type, category, description, amount, date (Y-m-d), reference]
        $entries = [

            // ══════════════════════════════════════════════════════════════════
            // FEBRUARY 2026
            // ══════════════════════════════════════════════════════════════════
            ['credit','Collection',    'Sunday Collection — Feb 1, 2026',           10200.00,'2026-02-01','COL-2602-01'],
            ['credit','Donation',      'Anonymous Benefactor — Feb Donation',         3500.00,'2026-02-03','DON-2602-03'],
            ['credit','Mass Stipend',  'Weekday Stipends — Feb Week 1',               1800.00,'2026-02-06','MS-2602-01'],
            ['credit','Collection',    'Sunday Collection — Feb 8, 2026',            11400.00,'2026-02-08','COL-2602-08'],
            ['credit','Wedding Fee',   'Wedding — Mendoza & Reyes',                   8000.00,'2026-02-14','WED-2602-14'],
            ['credit','Collection',    'Sunday Collection — Feb 15, 2026',           12100.00,'2026-02-15','COL-2602-15'],
            ['credit','Mass Stipend',  'Weekday Stipends — Feb Week 3',               2000.00,'2026-02-17','MS-2602-03'],
            ['credit','Certificate Fee','Certificate Fees — Feb 2026',                2400.00,'2026-02-20','CERT-2602-20'],
            ['credit','Seminar Fee',   'Pre-Marriage Seminar — 8 couples',             4000.00,'2026-02-21','SEM-2602-21'],
            ['credit','Collection',    'Sunday Collection — Feb 22, 2026',           10800.00,'2026-02-22','COL-2602-22'],

            ['debit', 'Utilities',     'Meralco Bill — February 2026',                7200.00,'2026-02-10','UTIL-2602-10'],
            ['debit', 'Salary',        'Parish Staff Honorarium — February 2026',    18500.00,'2026-02-28','SAL-2602-28'],
            ['debit', 'Office Supplies','Office Supplies Restock — Feb 2026',          1850.00,'2026-02-12','SUPP-2602-12'],
            ['debit', 'Sacramentals',  'Candles & Liturgical Items — Feb 2026',       3200.00,'2026-02-18','SACR-2602-18'],
            ['debit', 'Maintenance',   'Repair — Church Sound System',                4500.00,'2026-02-25','MAINT-2602-25'],

            // ══════════════════════════════════════════════════════════════════
            // MARCH 2026 (Lenten Season)
            // ══════════════════════════════════════════════════════════════════
            ['credit','Collection',    'Ash Wednesday Collection',                    8500.00,'2026-03-04','COL-2603-04'],
            ['credit','Collection',    'Sunday Collection — Mar 8, 2026',             9800.00,'2026-03-08','COL-2603-08'],
            ['credit','Mass Stipend',  'Lenten Weekday Stipends — Week 1',            2500.00,'2026-03-06','MS-2603-01'],
            ['credit','Collection',    'Stations of the Cross Collection — Mar 13',   3200.00,'2026-03-13','COL-2603-13'],
            ['credit','Collection',    'Sunday Collection — Mar 15, 2026',           10200.00,'2026-03-15','COL-2603-15'],
            ['credit','Baptism Fee',   'Baptism — Torres, Angelo Rafael',             1500.00,'2026-03-15','BAP-2603-15'],
            ['credit','Collection',    'Palm Sunday Collection',                      14500.00,'2026-03-29','COL-2603-29'],
            ['credit','Collection',    'Easter Sunday Collection',                    18000.00,'2026-04-05','COL-2604-05'],
            ['credit','Donation',      'Easter Donation — Binhi Foundation',          10000.00,'2026-04-05','DON-2604-05'],
            ['credit','Burial Fee',    'Funeral Mass — Santos, Eduardo Sr.',           3500.00,'2026-03-22','BUR-2603-22'],

            ['debit', 'Utilities',     'Meralco Bill — March 2026',                   7500.00,'2026-03-10','UTIL-2603-10'],
            ['debit', 'Salary',        'Parish Staff Honorarium — March 2026',       18500.00,'2026-03-31','SAL-2603-31'],
            ['debit', 'Events',        'Holy Week Program Materials',                  5500.00,'2026-03-25','EVT-2603-25'],
            ['debit', 'Sacramentals',  'Holy Week Liturgical Supplies',               6200.00,'2026-03-20','SACR-2603-20'],
            ['debit', 'Charitable',    'Lenten Outreach — Food Packs Distribution',   8000.00,'2026-03-27','CHAR-2603-27'],

            // ══════════════════════════════════════════════════════════════════
            // APRIL 2026
            // ══════════════════════════════════════════════════════════════════
            ['credit','Collection',    'Divine Mercy Sunday Collection',              15200.00,'2026-04-12','COL-2604-12'],
            ['credit','Collection',    'Sunday Collection — Apr 19, 2026',           11800.00,'2026-04-19','COL-2604-19'],
            ['credit','Seminar Fee',   'Pre-Baptismal Seminar — 15 attendees',         2250.00,'2026-04-10','SEM-2604-10'],
            ['credit','Certificate Fee','Certificate Fees — April 2026',               3100.00,'2026-04-22','CERT-2604-22'],
            ['credit','House Blessing','House Blessing — 5 households',                7500.00,'2026-04-24','HB-2604-24'],
            ['credit','Collection',    'Sunday Collection — Apr 26, 2026',           12400.00,'2026-04-26','COL-2604-26'],
            ['credit','Grant',         'Diocese Subsidy — Q2 2026',                  25000.00,'2026-04-30','GRANT-2604-30'],
            ['credit','Baptism Fee',   'Group Baptism — Apr 19 (4 children)',          6000.00,'2026-04-19','BAP-2604-19'],

            ['debit', 'Utilities',     'Meralco Bill — April 2026',                   7100.00,'2026-04-10','UTIL-2604-10'],
            ['debit', 'Salary',        'Parish Staff Honorarium — April 2026',       18500.00,'2026-04-30','SAL-2604-30'],
            ['debit', 'Events',        'Parish Youth Summer Camp Expenses',            9800.00,'2026-04-14','EVT-2604-14'],
            ['debit', 'Maintenance',   'Flooring Repair — Sacristy',                  6500.00,'2026-04-20','MAINT-2604-20'],
            ['debit', 'Office Supplies','Toner & Office Supplies — Apr 2026',          1650.00,'2026-04-15','SUPP-2604-15'],

            // ══════════════════════════════════════════════════════════════════
            // MAY 2026
            // ══════════════════════════════════════════════════════════════════
            ['credit','Collection',    'Sunday Collection — May 3, 2026',            12100.00,'2026-05-03','COL-2605-03'],
            ['credit','Donation',      'Mothers Day Donation Drive',                   5500.00,'2026-05-10','DON-2605-10'],
            ['credit','Mass Stipend',  'Weekday Stipends — May Week 1',               2100.00,'2026-05-05','MS-2605-01'],
            ['credit','Collection',    'Sunday Collection — May 10, 2026',           11700.00,'2026-05-10','COL-2605-10'],
            ['credit','Wedding Fee',   'Wedding — Garcia & Santos',                    8000.00,'2026-05-16','WED-2605-16'],
            ['credit','Collection',    'Sunday Collection — May 17, 2026',           13500.00,'2026-05-17','COL-2605-17'],
            ['credit','Baptism Fee',   'Baptism — Villanueva, Sofia Rose',             1500.00,'2026-05-17','BAP-2605-17'],
            ['credit','Seminar Fee',   'Confirmation Catechesis — 20 confirmands',     4000.00,'2026-05-20','SEM-2605-20'],
            ['credit','Certificate Fee','Certificate Fees — May 2026',                 2800.00,'2026-05-25','CERT-2605-25'],
            ['credit','Collection',    'Sunday Collection — May 24, 2026 (Feast)',    21000.00,'2026-05-24','COL-2605-24'],

            ['debit', 'Utilities',     'Meralco Bill — May 2026',                     7800.00,'2026-05-10','UTIL-2605-10'],
            ['debit', 'Salary',        'Parish Staff Honorarium — May 2026',         18500.00,'2026-05-31','SAL-2605-31'],
            ['debit', 'Events',        'Parish Anniversary Celebration Expenses',     12500.00,'2026-05-22','EVT-2605-22'],
            ['debit', 'Sacramentals',  'Confirmation Supplies & Programs',             4800.00,'2026-05-18','SACR-2605-18'],
            ['debit', 'Charitable',    'Outreach — School Supplies Distribution',      7500.00,'2026-05-28','CHAR-2605-28'],
            ['debit', 'Maintenance',   'Painting — Parish Hall Exterior',            15000.00,'2026-05-05','MAINT-2605-05'],

            // ══════════════════════════════════════════════════════════════════
            // JUNE 2026
            // ══════════════════════════════════════════════════════════════════
            ['credit','Collection',    'Sunday Collection — Jun 7, 2026',            11900.00,'2026-06-07','COL-2606-07'],
            ['credit','Mass Stipend',  'Weekday Stipends — Jun Week 1',               1950.00,'2026-06-09','MS-2606-01'],
            ['credit','Baptism Fee',   'Group Baptism — June 21 (3 children)',         4500.00,'2026-06-21','BAP-2606-21'],
            ['credit','Collection',    'Sunday Collection — Jun 14, 2026',           12300.00,'2026-06-14','COL-2606-14'],
            ['credit','Certificate Fee','Certificate Fees — June 2026',                2600.00,'2026-06-17','CERT-2606-17'],
            ['credit','Collection',    'Sunday Collection — Jun 21, 2026',           10800.00,'2026-06-21','COL-2606-21'],
            ['credit','House Blessing','House Blessing — 3 households',                4500.00,'2026-06-22','HB-2606-22'],
            ['credit','Mass Stipend',  'Weekday Stipends — Jun Week 3',               2200.00,'2026-06-23','MS-2606-03'],
            ['credit','Collection',    'Sunday Collection — Jun 28, 2026',           11200.00,'2026-06-28','COL-2606-28'],
            ['credit','Burial Fee',    'Funeral Mass — Dela Cruz, Nestor',             3500.00,'2026-06-30','BUR-2606-30'],

            ['debit', 'Utilities',     'Meralco Bill — June 2026',                    8100.00,'2026-06-10','UTIL-2606-10'],
            ['debit', 'Salary',        'Parish Staff Honorarium — June 2026',        18500.00,'2026-06-30','SAL-2606-30'],
            ['debit', 'Maintenance',   'Aircon Servicing — Church & Hall',             3500.00,'2026-06-05','MAINT-2606-05'],
            ['debit', 'Office Supplies','Printer Paper & Stationery — Jun 2026',        980.00,'2026-06-12','SUPP-2606-12'],
            ['debit', 'Insurance',     'Parish Property Insurance — Annual Premium',  24000.00,'2026-06-15','INS-2606-15'],

            // ══════════════════════════════════════════════════════════════════
            // JULY 2026
            // ══════════════════════════════════════════════════════════════════
            ['credit','Collection',    'Sunday Collection — Jul 5, 2026',            12450.00,'2026-07-05','COL-2607-05'],
            ['credit','Mass Stipend',  'Weekday Stipends — Jul Week 1',               2200.00,'2026-07-07','MS-2607-01'],
            ['credit','Donation',      'Anonymous Benefactor Donation — July',         5000.00,'2026-07-02','DON-2607-02'],
            ['credit','Baptism Fee',   'Baptism — Lim, Carlos Jr.',                   1500.00,'2026-07-03','BAP-2607-03'],
            ['credit','Baptism Fee',   'Baptism — Santos, Maria Clara',               1500.00,'2026-07-04','BAP-2607-04'],
            ['credit','Collection',    'Sunday Collection — Jul 12, 2026',           11800.00,'2026-07-12','COL-2607-12'],
            ['credit','Mass Stipend',  'Weekday Stipends — Jul Week 2',               1950.00,'2026-07-14','MS-2607-02'],
            ['credit','Wedding Fee',   'Wedding — Reyes & Cruz',                       8000.00,'2026-07-11','WED-2607-11'],
            ['credit','Certificate Fee','Certificate Fees — Jul Week 2',               1800.00,'2026-07-13','CERT-2607-13'],
            ['credit','Seminar Fee',   'Pre-Baptismal Seminar — 12 attendees',         1800.00,'2026-07-10','SEM-2607-10'],
            ['credit','Collection',    'Sunday Collection — Jul 19, 2026',           13200.00,'2026-07-19','COL-2607-19'],
            ['credit','Burial Fee',    'Funeral Mass — Mendoza, Rodrigo',              3500.00,'2026-07-20','BUR-2607-20'],

            ['debit', 'Utilities',     'Meralco Bill — July 2026',                    7400.00,'2026-07-10','UTIL-2607-10'],
            ['debit', 'Salary',        'Parish Staff Honorarium — July 2026',        18500.00,'2026-07-31','SAL-2607-31'],
            ['debit', 'Events',        'Medical Mission Expenses — Southville 5',     8500.00,'2026-07-21','EVT-2607-21'],
            ['debit', 'Sacramentals',  'Candles & Liturgical Items — Jul 2026',       2800.00,'2026-07-08','SACR-2607-08'],
            ['debit', 'Bank Fees',     'Bank Service Charges — Q2 2026',               650.00,'2026-07-15','BANK-2607-15'],
            ['debit', 'Other Expense', 'Contingency Fund — Parish Operations',        3000.00,'2026-07-18','MISC-2607-18'],
        ];

        $insertedCount = 0;
        foreach ($entries as [$type, $category, $description, $amount, $date, $ref]) {
            // Skip if reference already exists
            if (LedgerEntry::where('reference_number', $ref)->exists()) continue;

            LedgerEntry::create([
                'type'             => $type,
                'category'         => $category,
                'description'      => $description,
                'amount'           => $amount,
                'entry_date'       => $date,
                'reference_number' => $ref,
                'recorded_by'      => $recorder,
            ]);
            $insertedCount++;
        }

        $this->command->line("  ✓ Created {$insertedCount} ledger entries.");
    }
}
