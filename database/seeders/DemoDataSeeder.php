<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Booking;
use App\Models\Certificate;
use App\Models\Family;
use App\Models\Parishioner;
use App\Models\Payment;
use App\Models\SacramentalRecord;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DemoDataSeeder — full-stack demo data for MHC Parish System
 *
 * Creates:
 *  - 3 families (10 parishioners total)
 *  - 3 parishioner user accounts (with 2FA-ready emails)
 *  - Sacramental records (baptism, confirmation, marriage, first communion)
 *  - Bookings in all statuses (pending, confirmed, completed, cancelled)
 *  - Payments (paid via GCash, Maya, cash; pending; failed)
 *  - Certificates (draft, issued, released)
 *  - Announcements (published + draft)
 *  - QR codes for bookings and certificates
 *
 * Run: php artisan db:seed --class=DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding demo data...');

        $qrService = app(QrCodeService::class);
        $secretary = User::where('email', 'secretary@mhcparish.ph')->first();
        $admin     = User::where('email', 'admin@mhcparish.ph')->first();

        // ── 1. FAMILIES ──────────────────────────────────────────────────────
        $this->command->line('  Creating families...');

        $familyDela = Family::firstOrCreate(['family_name' => 'Dela Cruz Family'], [
            'address'        => 'Blk 3 Lot 12, Phase 1',
            'barangay'       => 'Niugan',
            'city'           => 'Cabuyao',
            'province'       => 'Laguna',
            'contact_number' => '09171234567',
        ]);

        $familySantos = Family::firstOrCreate(['family_name' => 'Santos Family'], [
            'address'        => 'Blk 7 Lot 5, Phase 2',
            'barangay'       => 'Niugan',
            'city'           => 'Cabuyao',
            'province'       => 'Laguna',
            'contact_number' => '09281234567',
        ]);

        $familyReyes = Family::firstOrCreate(['family_name' => 'Reyes Family'], [
            'address'        => 'Blk 12 Lot 8, Phase 3',
            'barangay'       => 'Niugan',
            'city'           => 'Cabuyao',
            'province'       => 'Laguna',
            'contact_number' => '09391234567',
        ]);

        $this->command->line('  Creating parishioners...');

        // ── 2. PARISHIONERS ──────────────────────────────────────────────────
        // Family 1 — Dela Cruz
        $juan = Parishioner::firstOrCreate(
            ['email' => 'juan.delacruz@gmail.com'],
            [
                'family_id'           => $familyDela->id,
                'first_name'          => 'Juan',
                'middle_name'         => 'Santos',
                'last_name'           => 'Dela Cruz',
                'birthdate'           => '1985-03-15',
                'gender'              => 'male',
                'civil_status'        => 'married',
                'address'             => 'Blk 3 Lot 12, Phase 1',
                'barangay'            => 'Niugan',
                'city'                => 'Cabuyao',
                'province'            => 'Laguna',
                'postal_code'         => '4025',
                'contact_number'      => '09171234567',
                'is_head_of_family'   => true,
                'relationship_to_head'=> 'Head',
                'is_active'           => true,
            ]
        );

        $maria = Parishioner::firstOrCreate(
            ['email' => 'maria.delacruz@gmail.com'],
            [
                'family_id'           => $familyDela->id,
                'first_name'          => 'Maria',
                'middle_name'         => 'Reyes',
                'last_name'           => 'Dela Cruz',
                'birthdate'           => '1988-07-22',
                'gender'              => 'female',
                'civil_status'        => 'married',
                'address'             => 'Blk 3 Lot 12, Phase 1',
                'barangay'            => 'Niugan',
                'city'                => 'Cabuyao',
                'province'            => 'Laguna',
                'postal_code'         => '4025',
                'contact_number'      => '09172345678',
                'is_head_of_family'   => false,
                'relationship_to_head'=> 'Spouse',
                'is_active'           => true,
            ]
        );

        $pedro = Parishioner::firstOrCreate(
            ['email' => 'pedro.delacruz@gmail.com'],
            [
                'family_id'           => $familyDela->id,
                'first_name'          => 'Pedro',
                'middle_name'         => 'Juan',
                'last_name'           => 'Dela Cruz',
                'birthdate'           => '2010-11-05',
                'gender'              => 'male',
                'civil_status'        => 'single',
                'address'             => 'Blk 3 Lot 12, Phase 1',
                'barangay'            => 'Niugan',
                'city'                => 'Cabuyao',
                'province'            => 'Laguna',
                'postal_code'         => '4025',
                'contact_number'      => null,
                'is_head_of_family'   => false,
                'relationship_to_head'=> 'Child',
                'is_active'           => true,
            ]
        );

        // Family 2 — Santos
        $jose = Parishioner::firstOrCreate(
            ['email' => 'jose.santos@gmail.com'],
            [
                'family_id'           => $familySantos->id,
                'first_name'          => 'Jose',
                'middle_name'         => 'Manuel',
                'last_name'           => 'Santos',
                'birthdate'           => '1979-05-10',
                'gender'              => 'male',
                'civil_status'        => 'married',
                'address'             => 'Blk 7 Lot 5, Phase 2',
                'barangay'            => 'Niugan',
                'city'                => 'Cabuyao',
                'province'            => 'Laguna',
                'postal_code'         => '4025',
                'contact_number'      => '09281234567',
                'is_head_of_family'   => true,
                'relationship_to_head'=> 'Head',
                'is_active'           => true,
            ]
        );

        $ana = Parishioner::firstOrCreate(
            ['email' => 'ana.santos@gmail.com'],
            [
                'family_id'           => $familySantos->id,
                'first_name'          => 'Ana',
                'middle_name'         => 'Cruz',
                'last_name'           => 'Santos',
                'birthdate'           => '1982-09-18',
                'gender'              => 'female',
                'civil_status'        => 'married',
                'address'             => 'Blk 7 Lot 5, Phase 2',
                'barangay'            => 'Niugan',
                'city'                => 'Cabuyao',
                'province'            => 'Laguna',
                'postal_code'         => '4025',
                'contact_number'      => '09282345678',
                'is_head_of_family'   => false,
                'relationship_to_head'=> 'Spouse',
                'is_active'           => true,
            ]
        );

        $baby = Parishioner::firstOrCreate(
            ['email' => null, 'first_name' => 'Sofia', 'last_name' => 'Santos'],
            [
                'family_id'           => $familySantos->id,
                'first_name'          => 'Sofia',
                'middle_name'         => 'Ana',
                'last_name'           => 'Santos',
                'birthdate'           => '2023-02-14',
                'gender'              => 'female',
                'civil_status'        => 'single',
                'address'             => 'Blk 7 Lot 5, Phase 2',
                'barangay'            => 'Niugan',
                'city'                => 'Cabuyao',
                'province'            => 'Laguna',
                'postal_code'         => '4025',
                'contact_number'      => null,
                'is_head_of_family'   => false,
                'relationship_to_head'=> 'Child',
                'is_active'           => true,
            ]
        );

        // Family 3 — Reyes
        $carlos = Parishioner::firstOrCreate(
            ['email' => 'carlos.reyes@gmail.com'],
            [
                'family_id'           => $familyReyes->id,
                'first_name'          => 'Carlos',
                'middle_name'         => 'Bautista',
                'last_name'           => 'Reyes',
                'birthdate'           => '1975-12-01',
                'gender'              => 'male',
                'civil_status'        => 'widowed',
                'address'             => 'Blk 12 Lot 8, Phase 3',
                'barangay'            => 'Niugan',
                'city'                => 'Cabuyao',
                'province'            => 'Laguna',
                'postal_code'         => '4025',
                'contact_number'      => '09391234567',
                'is_head_of_family'   => true,
                'relationship_to_head'=> 'Head',
                'is_active'           => true,
            ]
        );

        $elena = Parishioner::firstOrCreate(
            ['email' => 'elena.reyes@gmail.com'],
            [
                'family_id'           => $familyReyes->id,
                'first_name'          => 'Elena',
                'middle_name'         => 'Garcia',
                'last_name'           => 'Reyes',
                'birthdate'           => '2000-06-30',
                'gender'              => 'female',
                'civil_status'        => 'single',
                'address'             => 'Blk 12 Lot 8, Phase 3',
                'barangay'            => 'Niugan',
                'city'                => 'Cabuyao',
                'province'            => 'Laguna',
                'postal_code'         => '4025',
                'contact_number'      => '09392345678',
                'is_head_of_family'   => false,
                'relationship_to_head'=> 'Child',
                'is_active'           => true,
            ]
        );

        $lolo = Parishioner::firstOrCreate(
            ['email' => 'andres.reyes@gmail.com'],
            [
                'family_id'           => $familyReyes->id,
                'first_name'          => 'Andres',
                'middle_name'         => 'Cruz',
                'last_name'           => 'Reyes',
                'birthdate'           => '1945-04-20',
                'gender'              => 'male',
                'civil_status'        => 'widowed',
                'address'             => 'Blk 12 Lot 8, Phase 3',
                'barangay'            => 'Niugan',
                'city'                => 'Cabuyao',
                'province'            => 'Laguna',
                'postal_code'         => '4025',
                'contact_number'      => '09393456789',
                'is_head_of_family'   => false,
                'relationship_to_head'=> 'Parent',
                'is_active'           => true,
            ]
        );

        $this->command->line('  Creating parishioner user accounts...');

        // ── 3. PARISHIONER USER ACCOUNTS ─────────────────────────────────────
        $userJuan = User::firstOrCreate(
            ['email' => 'juan.delacruz@gmail.com'],
            [
                'name'           => 'Juan Dela Cruz',
                'password'       => Hash::make('Password@123'),
                'parishioner_id' => $juan->id,
                'is_active'      => true,
                'last_login_at'  => now()->subDays(2),
            ]
        );
        $userJuan->assignRole('parishioner');
        $juan->update(['email' => 'juan.delacruz@gmail.com']);

        $userJose = User::firstOrCreate(
            ['email' => 'jose.santos@gmail.com'],
            [
                'name'           => 'Jose Santos',
                'password'       => Hash::make('Password@123'),
                'parishioner_id' => $jose->id,
                'is_active'      => true,
                'last_login_at'  => now()->subDays(5),
            ]
        );
        $userJose->assignRole('parishioner');
        $jose->update(['email' => 'jose.santos@gmail.com']);

        $userElena = User::firstOrCreate(
            ['email' => 'elena.reyes@gmail.com'],
            [
                'name'           => 'Elena Reyes',
                'password'       => Hash::make('Password@123'),
                'parishioner_id' => $elena->id,
                'is_active'      => true,
                'last_login_at'  => now()->subDay(),
            ]
        );
        $userElena->assignRole('parishioner');
        $elena->update(['email' => 'elena.reyes@gmail.com']);

        $this->command->line('  Creating sacramental records...');

        // ── 4. SACRAMENTAL RECORDS ────────────────────────────────────────────
        // Juan — Baptism
        $juanBaptism = SacramentalRecord::firstOrCreate(
            ['parishioner_id' => $juan->id, 'type' => 'baptism'],
            [
                'date_administered' => '1985-04-07',
                'celebrant'         => 'Rev. Fr. Antonio Villanueva',
                'venue'             => 'Mary Help of Christians Parish',
                'register_number'   => 'B-1985-042',
                'page_number'       => '12',
                'line_number'       => '5',
                'godparents'        => ['Roberto Santos', 'Lourdes Garcia'],
                'notes'             => 'Ernesto Santos and Caridad Dela Cruz',
                'recorded_by'       => $secretary?->id ?? $admin->id,
                'verified_by'       => $admin->id,
                'verified_at'       => now()->subMonths(3),
            ]
        );

        // Juan — Confirmation
        $juanConfirmation = SacramentalRecord::firstOrCreate(
            ['parishioner_id' => $juan->id, 'type' => 'confirmation'],
            [
                'date_administered' => '1999-05-15',
                'celebrant'         => 'Most Rev. Bishop Ramon Santos',
                'venue'             => 'San Pablo Cathedral',
                'register_number'   => 'C-1999-018',
                'page_number'       => '4',
                'line_number'       => '11',
                'sponsors'          => ['Roberto Santos'],
                'recorded_by'       => $secretary?->id ?? $admin->id,
                'verified_by'       => $admin->id,
                'verified_at'       => now()->subMonths(3),
            ]
        );

        // Juan & Maria — Marriage
        $juanMarriage = SacramentalRecord::firstOrCreate(
            ['parishioner_id' => $juan->id, 'type' => 'marriage'],
            [
                'spouse_parishioner_id' => $maria->id,
                'date_administered'     => '2012-02-14',
                'celebrant'             => 'Rev. Fr. Miguel Fernandez',
                'venue'                 => 'Mary Help of Christians Parish',
                'register_number'       => 'M-2012-003',
                'page_number'           => '1',
                'line_number'           => '3',
                'sponsors'              => ['Roberto Santos', 'Lourdes Garcia', 'Manuel Reyes', 'Celia Cruz'],
                'witnesses'             => ['Pedro Villanueva', 'Rosa Mendoza'],
                'recorded_by'           => $secretary?->id ?? $admin->id,
                'verified_by'           => $admin->id,
                'verified_at'           => now()->subMonths(3),
            ]
        );

        // Maria — Baptism
        SacramentalRecord::firstOrCreate(
            ['parishioner_id' => $maria->id, 'type' => 'baptism'],
            [
                'date_administered' => '1988-08-15',
                'celebrant'         => 'Rev. Fr. Jose Bautista',
                'venue'             => 'Sto. Nino Parish, Cabuyao',
                'register_number'   => 'B-1988-091',
                'page_number'       => '22',
                'line_number'       => '7',
                'godparents'        => ['Manuel Reyes', 'Celia Cruz'],
                'notes'             => 'Roberto Reyes and Natividad Garcia',
                'recorded_by'       => $secretary?->id ?? $admin->id,
            ]
        );

        // Pedro — Baptism (child)
        $pedroBaptism = SacramentalRecord::firstOrCreate(
            ['parishioner_id' => $pedro->id, 'type' => 'baptism'],
            [
                'date_administered' => '2010-12-12',
                'celebrant'         => 'Rev. Fr. Miguel Fernandez',
                'venue'             => 'Mary Help of Christians Parish',
                'register_number'   => 'B-2010-156',
                'page_number'       => '38',
                'line_number'       => '2',
                'godparents'        => ['Roberto Santos', 'Ana Villanueva'],
                'notes'             => 'Juan Santos Dela Cruz and Maria Reyes Dela Cruz',
                'recorded_by'       => $secretary?->id ?? $admin->id,
                'verified_by'       => $admin->id,
                'verified_at'       => now()->subMonths(1),
            ]
        );

        // Pedro — First Communion
        SacramentalRecord::firstOrCreate(
            ['parishioner_id' => $pedro->id, 'type' => 'first_communion'],
            [
                'date_administered' => '2018-05-20',
                'celebrant'         => 'Rev. Fr. Miguel Fernandez',
                'venue'             => 'Mary Help of Christians Parish',
                'register_number'   => 'FC-2018-022',
                'page_number'       => '6',
                'line_number'       => '14',
                'godparents'        => ['Roberto Santos'],
                'recorded_by'       => $secretary?->id ?? $admin->id,
            ]
        );

        // Sofia Santos — Baptism (infant)
        $sofiaBaptism = SacramentalRecord::firstOrCreate(
            ['parishioner_id' => $baby->id, 'type' => 'baptism'],
            [
                'date_administered' => '2023-03-19',
                'celebrant'         => 'Rev. Fr. Miguel Fernandez',
                'venue'             => 'Mary Help of Christians Parish',
                'register_number'   => 'B-2023-011',
                'page_number'       => '3',
                'line_number'       => '1',
                'godparents'        => ['Carlos Reyes', 'Elena Reyes'],
                'notes'             => 'Jose Manuel Santos and Ana Cruz Santos',
                'recorded_by'       => $secretary?->id ?? $admin->id,
                'verified_by'       => $admin->id,
                'verified_at'       => now()->subMonths(6),
            ]
        );

        // Elena — Baptism
        $elenaBaptism = SacramentalRecord::firstOrCreate(
            ['parishioner_id' => $elena->id, 'type' => 'baptism'],
            [
                'date_administered' => '2000-07-16',
                'celebrant'         => 'Rev. Fr. Antonio Villanueva',
                'venue'             => 'Mary Help of Christians Parish',
                'register_number'   => 'B-2000-078',
                'page_number'       => '19',
                'line_number'       => '6',
                'godparents'        => ['Andres Reyes', 'Lourdes Bautista'],
                'notes'             => 'Carlos Bautista Reyes and Nena Garcia Reyes',
                'recorded_by'       => $secretary?->id ?? $admin->id,
            ]
        );

        // Andres Reyes — Death/Burial (elderly)
        SacramentalRecord::firstOrCreate(
            ['parishioner_id' => $lolo->id, 'type' => 'death_burial'],
            [
                'date_administered' => now()->subMonths(2)->toDateString(),
                'celebrant'         => 'Rev. Fr. Miguel Fernandez',
                'venue'             => 'Mary Help of Christians Parish',
                'register_number'   => 'D-' . date('Y') . '-004',
                'page_number'       => '1',
                'line_number'       => '4',
                'recorded_by'       => $secretary?->id ?? $admin->id,
                'verified_by'       => $admin->id,
                'verified_at'       => now()->subMonths(2),
            ]
        );

        $this->command->line('  Creating bookings...');

        // ── 5. BOOKINGS ───────────────────────────────────────────────────────
        // Pending — Juan books a house blessing
        $b1 = Booking::firstOrCreate(
            ['parishioner_id' => $juan->id, 'booking_type' => 'house_blessing', 'status' => 'pending'],
            [
                'scheduled_date' => now()->addDays(7)->toDateString(),
                'scheduled_time' => '10:00:00',
                'service_fee'    => 300.00,
                'address'        => 'Blk 3 Lot 12, Phase 1, Niugan, Cabuyao',
                'notes'          => 'New house blessing for our recently renovated home.',
                'reminder_sent'  => false,
            ]
        );
        if (!$b1->qrCode) $qrService->generateForBooking($b1);

        // Confirmed — Jose books a baptism
        $b2 = Booking::firstOrCreate(
            ['parishioner_id' => $jose->id, 'booking_type' => 'baptism', 'status' => 'confirmed'],
            [
                'scheduled_date' => now()->addDays(14)->toDateString(),
                'scheduled_time' => '09:00:00',
                'service_fee'    => 500.00,
                'notes'          => 'Baptism for our daughter Sofia.',
                'admin_notes'    => 'Please bring all required documents. Seminar completed.',
                'confirmed_by'   => $secretary?->id ?? $admin->id,
                'confirmed_at'   => now()->subDays(2),
                'reminder_sent'  => false,
            ]
        );
        if (!$b2->qrCode) $qrService->generateForBooking($b2);

        // Completed — Elena had a car blessing
        $b3 = Booking::firstOrCreate(
            ['parishioner_id' => $elena->id, 'booking_type' => 'car_blessing', 'status' => 'completed'],
            [
                'scheduled_date' => now()->subDays(10)->toDateString(),
                'scheduled_time' => '14:00:00',
                'service_fee'    => 200.00,
                'notes'          => 'Blessing for my new car.',
                'confirmed_by'   => $secretary?->id ?? $admin->id,
                'confirmed_at'   => now()->subDays(12),
                'reminder_sent'  => true,
            ]
        );
        if (!$b3->qrCode) $qrService->generateForBooking($b3);

        // Cancelled — Juan cancelled a mass intention
        $b4 = Booking::firstOrCreate(
            ['parishioner_id' => $juan->id, 'booking_type' => 'mass_intention', 'status' => 'cancelled'],
            [
                'scheduled_date'      => now()->subDays(5)->toDateString(),
                'scheduled_time'      => '06:00:00',
                'service_fee'         => 200.00,
                'notes'               => 'Mass for the soul of my father.',
                'cancelled_by'        => $userJuan->id,
                'cancelled_at'        => now()->subDays(6),
                'cancellation_reason' => 'Schedule conflict, will rebook next week.',
                'reminder_sent'       => false,
            ]
        );

        // Pending — Elena books a pre-baptismal seminar
        $b5 = Booking::firstOrCreate(
            ['parishioner_id' => $elena->id, 'booking_type' => 'pre_baptismal', 'status' => 'pending'],
            [
                'scheduled_date' => now()->addDays(21)->toDateString(),
                'scheduled_time' => '08:00:00',
                'service_fee'    => 100.00,
                'notes'          => 'Pre-baptismal seminar for upcoming baptism.',
                'reminder_sent'  => false,
            ]
        );
        if (!$b5->qrCode) $qrService->generateForBooking($b5);

        // Confirmed — Carlos books a sick call
        $b6 = Booking::firstOrCreate(
            ['parishioner_id' => $carlos->id, 'booking_type' => 'sick_call', 'status' => 'confirmed'],
            [
                'scheduled_date' => now()->addDays(2)->toDateString(),
                'scheduled_time' => '15:00:00',
                'service_fee'    => 0.00,
                'address'        => 'Blk 12 Lot 8, Phase 3, Niugan, Cabuyao',
                'notes'          => 'Anointing for elderly father who is ill.',
                'admin_notes'    => 'Fr. Miguel will visit.',
                'confirmed_by'   => $secretary?->id ?? $admin->id,
                'confirmed_at'   => now()->subDay(),
                'reminder_sent'  => false,
            ]
        );
        if (!$b6->qrCode) $qrService->generateForBooking($b6);

        // Completed — Jose had a wedding booking
        $b7 = Booking::firstOrCreate(
            ['parishioner_id' => $jose->id, 'booking_type' => 'wedding', 'status' => 'completed'],
            [
                'scheduled_date' => now()->subMonths(2)->toDateString(),
                'scheduled_time' => '10:00:00',
                'service_fee'    => 3000.00,
                'notes'          => 'Wedding ceremony.',
                'confirmed_by'   => $secretary?->id ?? $admin->id,
                'confirmed_at'   => now()->subMonths(3),
                'reminder_sent'  => true,
            ]
        );
        if (!$b7->qrCode) $qrService->generateForBooking($b7);

        $this->command->line('  Creating payments...');

        // ── 6. PAYMENTS ───────────────────────────────────────────────────────
        // Paid via GCash — Elena's car blessing
        $p1 = Payment::firstOrCreate(
            ['booking_id' => $b3->id],
            [
                'parishioner_id'  => $elena->id,
                'amount'          => 200.00,
                'payment_method'  => 'gcash',
                'status'          => 'paid',
                'gateway_reference' => 'GC-' . strtoupper(uniqid()),
                'paid_at'         => now()->subDays(10),
                'notes'           => 'GCash payment for car blessing.',
            ]
        );

        // Paid via Cash — Jose's baptism booking deposit
        $p2 = Payment::firstOrCreate(
            ['booking_id' => $b2->id],
            [
                'parishioner_id' => $jose->id,
                'amount'         => 500.00,
                'payment_method' => 'cash',
                'status'         => 'paid',
                'paid_at'        => now()->subDays(2),
                'notes'          => 'Cash payment received at parish office.',
            ]
        );

        // Paid via Maya — Jose's wedding
        $p3 = Payment::firstOrCreate(
            ['booking_id' => $b7->id],
            [
                'parishioner_id'    => $jose->id,
                'amount'            => 3000.00,
                'payment_method'    => 'maya',
                'status'            => 'paid',
                'gateway_reference' => 'MY-' . strtoupper(uniqid()),
                'paid_at'           => now()->subMonths(2),
                'notes'             => 'Maya payment for wedding ceremony.',
            ]
        );

        // Pending — Juan's house blessing (not yet paid)
        $p4 = Payment::firstOrCreate(
            ['booking_id' => $b1->id],
            [
                'parishioner_id' => $juan->id,
                'amount'         => 300.00,
                'payment_method' => 'gcash',
                'status'         => 'pending',
                'notes'          => 'Awaiting GCash payment.',
            ]
        );

        // Failed — Elena's seminar payment attempt
        Payment::firstOrCreate(
            ['booking_id' => $b5->id],
            [
                'parishioner_id'    => $elena->id,
                'amount'            => 100.00,
                'payment_method'    => 'gcash',
                'status'            => 'failed',
                'gateway_reference' => 'GC-FAIL-' . strtoupper(uniqid()),
                'notes'             => 'Payment failed — insufficient balance.',
            ]
        );

        // Refunded — a previous payment
        Payment::firstOrCreate(
            ['parishioner_id' => $juan->id, 'status' => 'refunded'],
            [
                'amount'         => 200.00,
                'payment_method' => 'cash',
                'status'         => 'refunded',
                'paid_at'        => now()->subMonths(1),
                'refund_reason'  => 'Booking cancelled by parishioner.',
                'refunded_by'    => $admin->id,
                'refunded_at'    => now()->subMonths(1)->addDays(2),
                'notes'          => 'Refund for cancelled mass intention.',
            ]
        );

        $this->command->line('  Creating certificates...');

        // ── 7. CERTIFICATES ───────────────────────────────────────────────────
        // Juan — Baptism certificate (released, with PDF)
        $cert1 = Certificate::firstOrCreate(
            ['parishioner_id' => $juan->id, 'type' => 'baptism'],
            [
                'sacramental_record_id' => $juanBaptism->id,
                'issued_date'           => now()->subMonths(1)->toDateString(),
                'issued_by'             => $secretary?->id ?? $admin->id,
                'purpose'               => 'Employment requirement',
                'status'                => 'released',
                'payment_id'            => null,
            ]
        );
        if (!$cert1->qrCode) $qrService->generateForCertificate($cert1);

        // Juan — Marriage certificate (issued)
        $cert2 = Certificate::firstOrCreate(
            ['parishioner_id' => $juan->id, 'type' => 'marriage'],
            [
                'sacramental_record_id' => $juanMarriage->id,
                'issued_date'           => now()->subWeeks(2)->toDateString(),
                'issued_by'             => $secretary?->id ?? $admin->id,
                'purpose'               => 'Legal documentation',
                'status'                => 'issued',
            ]
        );
        if (!$cert2->qrCode) $qrService->generateForCertificate($cert2);

        // Pedro — Baptism certificate (released)
        $cert3 = Certificate::firstOrCreate(
            ['parishioner_id' => $pedro->id, 'type' => 'baptism'],
            [
                'sacramental_record_id' => $pedroBaptism->id,
                'issued_date'           => now()->subWeeks(3)->toDateString(),
                'issued_by'             => $secretary?->id ?? $admin->id,
                'purpose'               => 'School enrollment',
                'status'                => 'released',
            ]
        );
        if (!$cert3->qrCode) $qrService->generateForCertificate($cert3);

        // Sofia — Baptism certificate (issued)
        $cert4 = Certificate::firstOrCreate(
            ['parishioner_id' => $baby->id, 'type' => 'baptism'],
            [
                'sacramental_record_id' => $sofiaBaptism->id,
                'issued_date'           => now()->subMonths(5)->toDateString(),
                'issued_by'             => $secretary?->id ?? $admin->id,
                'purpose'               => 'Record keeping',
                'status'                => 'issued',
            ]
        );
        if (!$cert4->qrCode) $qrService->generateForCertificate($cert4);

        // Elena — Baptism certificate (draft — requested by parishioner)
        $cert5 = Certificate::firstOrCreate(
            ['parishioner_id' => $elena->id, 'type' => 'baptism'],
            [
                'sacramental_record_id' => $elenaBaptism->id,
                'issued_date'           => now()->toDateString(),
                'purpose'               => 'Confirmation requirement',
                'status'                => 'draft',
                'notes'                 => 'Requested online by parishioner.',
            ]
        );

        // Elena — Membership certificate (issued)
        $cert6 = Certificate::firstOrCreate(
            ['parishioner_id' => $elena->id, 'type' => 'membership'],
            [
                'issued_date' => now()->subWeeks(1)->toDateString(),
                'issued_by'   => $secretary?->id ?? $admin->id,
                'purpose'     => 'Scholarship application',
                'status'      => 'issued',
            ]
        );
        if (!$cert6->qrCode) $qrService->generateForCertificate($cert6);

        $this->command->line('  Creating announcements...');

        // ── 8. ANNOUNCEMENTS ─────────────────────────────────────────────────
        $announcements = [
            [
                'title'        => 'Holy Week Schedule 2026',
                'content'      => '<p>Dear parishioners, please take note of our Holy Week schedule for 2026.</p><p><strong>Palm Sunday:</strong> April 5 — 6:00 AM, 8:00 AM, 10:00 AM, 6:00 PM</p><p><strong>Holy Monday to Wednesday:</strong> 6:00 AM and 6:00 PM daily</p><p><strong>Holy Thursday (Mass of the Lord\'s Supper):</strong> 6:00 PM</p><p><strong>Good Friday (Stations of the Cross):</strong> 8:00 AM; Veneration of the Cross: 3:00 PM</p><p><strong>Easter Vigil:</strong> April 11 — 8:00 PM</p><p><strong>Easter Sunday:</strong> April 12 — 6:00 AM, 8:00 AM, 10:00 AM, 6:00 PM</p><p>God bless everyone!</p>',
                'category'     => 'Schedule',
                'is_published' => true,
                'published_at' => now()->subDays(10),
                'expires_at'   => now()->addMonths(2),
                'created_by'   => $admin->id,
            ],
            [
                'title'        => 'Pre-Baptismal Seminar — June 2026',
                'content'      => '<p>The next Pre-Baptismal Seminar will be held on <strong>June 7, 2026 (Saturday)</strong> at 8:00 AM in the Parish Hall.</p><p>This seminar is required for all parents and godparents of children to be baptized. Please register at the parish office at least one week before the seminar date.</p><p><strong>Requirements to bring:</strong></p><ul><li>Birth Certificate of the child</li><li>Marriage Certificate of parents (if applicable)</li><li>Valid ID of parents and godparents</li></ul><p>Seminar fee: ₱100.00 per family</p>',
                'category'     => 'Seminar',
                'is_published' => true,
                'published_at' => now()->subDays(5),
                'expires_at'   => now()->addMonths(1),
                'created_by'   => $secretary?->id ?? $admin->id,
            ],
            [
                'title'        => 'Parish Fiesta Celebration 2026',
                'content'      => '<p>Join us in celebrating the Feast of Mary Help of Christians on <strong>May 24, 2026</strong>!</p><p>Schedule of activities:</p><ul><li>May 23 (Saturday) — Novena Mass at 6:00 PM, followed by Salubong</li><li>May 24 (Sunday) — Solemn Fiesta Mass at 9:00 AM, Parish Procession at 4:00 PM, Evening Program at 7:00 PM</li></ul><p>All parishioners are invited to participate. Donations for the fiesta celebration are welcome at the parish office.</p>',
                'category'     => 'Event',
                'is_published' => true,
                'published_at' => now()->subDays(3),
                'expires_at'   => now()->addMonths(1),
                'created_by'   => $admin->id,
            ],
            [
                'title'        => 'Online Booking System Now Available',
                'content'      => '<p>We are pleased to announce that our new <strong>Online Parish Services Booking System</strong> is now available!</p><p>You can now book the following services online:</p><ul><li>Baptism</li><li>House, Car, and Business Blessings</li><li>Mass Intentions</li><li>Pre-Baptismal and Pre-Marriage Seminars</li><li>Sick Call / Anointing of the Sick</li></ul><p>To get started, simply <a href="/register">create an account</a> or <a href="/login">log in</a> to the parish portal.</p><p>For assistance, please visit the parish office during office hours.</p>',
                'category'     => 'Announcement',
                'is_published' => true,
                'published_at' => now()->subDays(15),
                'expires_at'   => null,
                'created_by'   => $admin->id,
            ],
            [
                'title'        => 'Confirmation Class Registration Open',
                'content'      => '<p>Registration for the 2026 Confirmation Catechesis Program is now open for young parishioners aged 13 and above.</p><p>Classes will begin on <strong>July 5, 2026</strong> every Saturday from 8:00 AM to 12:00 PM.</p><p>Requirements for registration:</p><ul><li>Baptismal Certificate</li><li>First Communion Certificate</li><li>2x2 ID photo (2 copies)</li><li>Registration fee: ₱200.00</li></ul><p>Register at the parish office from Monday to Friday, 8:00 AM to 5:00 PM.</p>',
                'category'     => 'Announcement',
                'is_published' => true,
                'published_at' => now()->subDays(1),
                'expires_at'   => now()->addMonths(2),
                'created_by'   => $secretary?->id ?? $admin->id,
            ],
            [
                'title'        => 'Draft: Christmas Schedule 2026',
                'content'      => '<p>Draft announcement for Christmas 2026 schedule. To be published in November.</p>',
                'category'     => 'Schedule',
                'is_published' => false,
                'published_at' => null,
                'expires_at'   => null,
                'created_by'   => $admin->id,
            ],
        ];

        foreach ($announcements as $ann) {
            \App\Models\Announcement::firstOrCreate(
                ['title' => $ann['title']],
                $ann
            );
        }

        // ── 9. AUDIT LOGS ─────────────────────────────────────────────────────
        $this->command->line('  Creating audit logs...');

        $auditEntries = [
            ['action' => 'create', 'model' => $juan,  'user' => $secretary ?? $admin, 'desc' => 'Parishioner profile created'],
            ['action' => 'create', 'model' => $maria, 'user' => $secretary ?? $admin, 'desc' => 'Parishioner profile created'],
            ['action' => 'create', 'model' => $jose,  'user' => $secretary ?? $admin, 'desc' => 'Parishioner profile created'],
            ['action' => 'create', 'model' => $elena, 'user' => $secretary ?? $admin, 'desc' => 'Parishioner profile created'],
            ['action' => 'create', 'model' => $b1,    'user' => $userJuan,            'desc' => 'Booking created by parishioner'],
            ['action' => 'confirm','model' => $b2,    'user' => $secretary ?? $admin, 'desc' => 'Booking confirmed'],
            ['action' => 'create', 'model' => $cert1, 'user' => $secretary ?? $admin, 'desc' => 'Certificate issued'],
            ['action' => 'release','model' => $cert1, 'user' => $secretary ?? $admin, 'desc' => 'Certificate released to parishioner'],
            ['action' => 'create', 'model' => $p2,    'user' => $secretary ?? $admin, 'desc' => 'Cash payment recorded'],
            ['action' => 'create', 'model' => $juanBaptism, 'user' => $secretary ?? $admin, 'desc' => 'Sacramental record created'],
        ];

        foreach ($auditEntries as $entry) {
            \App\Models\AuditLog::firstOrCreate(
                [
                    'auditable_type' => get_class($entry['model']),
                    'auditable_id'   => $entry['model']->id,
                    'action'         => $entry['action'],
                ],
                [
                    'user_id'     => $entry['user']->id,
                    'old_values'  => [],
                    'new_values'  => [],
                    'ip_address'  => '127.0.0.1',
                    'user_agent'  => 'DemoDataSeeder/1.0',
                    'description' => $entry['desc'],
                ]
            );
        }

        // ── 10. PROFILE CHANGE LOGS ───────────────────────────────────────────
        \App\Models\ProfileChangeLog::firstOrCreate(
            ['parishioner_id' => $juan->id, 'field_name' => 'contact_number'],
            [
                'changed_by' => $secretary?->id ?? $admin->id,
                'old_value'  => '09170000000',
                'new_value'  => '09171234567',
                'reason'     => 'Updated contact number at parishioner request.',
            ]
        );

        \App\Models\ProfileChangeLog::firstOrCreate(
            ['parishioner_id' => $elena->id, 'field_name' => 'profile_update'],
            [
                'changed_by' => $userElena->id,
                'old_value'  => null,
                'new_value'  => 'Self-service profile update',
                'reason'     => null,
            ]
        );

        // ── 11. SUMMARY ───────────────────────────────────────────────────────
        $this->command->newLine();
        $this->command->info('✅ Demo data seeded successfully!');
        $this->command->newLine();
        $this->command->line('  <fg=yellow>PARISHIONER ACCOUNTS (login with 2FA):</>');
        $this->command->line('    juan.delacruz@gmail.com  / Password@123');
        $this->command->line('    jose.santos@gmail.com    / Password@123');
        $this->command->line('    elena.reyes@gmail.com    / Password@123');
        $this->command->newLine();
        $this->command->line('  <fg=cyan>ADMIN ACCOUNTS (no 2FA):</>');
        $this->command->line('    admin@mhcparish.ph       / Admin@1234');
        $this->command->line('    secretary@mhcparish.ph   / Secretary@1234');
        $this->command->line('    finance@mhcparish.ph     / Finance@1234');
        $this->command->newLine();
        $this->command->line('  <fg=green>DATA CREATED:</>');
        $this->command->line('    Families:            3');
        $this->command->line('    Parishioners:        ' . \App\Models\Parishioner::count());
        $this->command->line('    Sacramental Records: ' . \App\Models\SacramentalRecord::count());
        $this->command->line('    Bookings:            ' . \App\Models\Booking::count());
        $this->command->line('    Payments:            ' . \App\Models\Payment::count());
        $this->command->line('    Certificates:        ' . \App\Models\Certificate::count());
        $this->command->line('    Announcements:       ' . \App\Models\Announcement::count());
        $this->command->line('    QR Codes:            ' . \App\Models\QrCode::count());
        $this->command->newLine();
    }
}
