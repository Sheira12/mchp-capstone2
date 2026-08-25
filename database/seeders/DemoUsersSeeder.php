<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Certificate;
use App\Models\Family;
use App\Models\Parishioner;
use App\Models\Payment;
use App\Models\SacramentalRecord;
use App\Models\User;
use App\Services\QrCodeService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DemoUsersSeeder
 *
 * Creates 5 fully-linked parishioner user accounts for demo/presentation purposes.
 * Each account has:
 *   - A User record (login credentials)
 *   - A complete Parishioner profile (address, birthdate, photo placeholder, etc.)
 *   - Sacramental records
 *   - Bookings in various statuses
 *   - Payment records
 *   - Certificate requests
 *
 * Credentials (all use the same password):
 *   aries.cumpio@gmail.com     / Password@123
 *   maricel.santos@gmail.com   / Password@123
 *   roberto.garcia@gmail.com   / Password@123
 *   lourdes.villanueva@gmail.com / Password@123
 *   danilo.mendoza@gmail.com   / Password@123
 *
 * Run standalone:  php artisan db:seed --class=DemoUsersSeeder
 */
class DemoUsersSeeder extends Seeder
{
    private QrCodeService $qrService;
    private ?User $admin;
    private ?User $secretary;

    public function run(): void
    {
        $this->qrService = app(QrCodeService::class);
        $this->admin     = User::where('email', 'maryhelpparish@gmail.com')
                               ->orWhere('email', 'admin@mhcparish.ph')
                               ->first()
                           ?? User::role('super_admin')->first();

        $this->secretary = User::where('email', 'cumpioaries07@gmail.com')
                               ->orWhere('email', 'secretary@mhcparish.ph')
                               ->first()
                           ?? User::role('parish_secretary')->first();

        $this->command->info('Seeding demo user accounts...');

        // ── 1. FAMILIES ──────────────────────────────────────────────────────
        $familyCumpio = Family::firstOrCreate(
            ['family_name' => 'Cumpio Family'],
            [
                'address'        => 'Blk 5 Lot 10, Southville 1',
                'barangay'       => 'Niugan',
                'city'           => 'Cabuyao',
                'province'       => 'Laguna',
                'contact_number' => '09369454812',
            ]
        );

        $familySantos2 = Family::firstOrCreate(
            ['family_name' => 'Santos-Reyes Family'],
            [
                'address'        => 'Blk 8 Lot 3, Phase 2',
                'barangay'       => 'Banay-banay',
                'city'           => 'Cabuyao',
                'province'       => 'Laguna',
                'contact_number' => '09181234567',
            ]
        );

        $familyGarcia = Family::firstOrCreate(
            ['family_name' => 'Garcia Family'],
            [
                'address'        => 'Blk 2 Lot 15, Phase 4',
                'barangay'       => 'Pulo',
                'city'           => 'Cabuyao',
                'province'       => 'Laguna',
                'contact_number' => '09271234567',
            ]
        );

        $familyVillanueva = Family::firstOrCreate(
            ['family_name' => 'Villanueva Family'],
            [
                'address'        => 'Blk 14 Lot 6, Phase 1',
                'barangay'       => 'Sala',
                'city'           => 'Cabuyao',
                'province'       => 'Laguna',
                'contact_number' => '09481234567',
            ]
        );

        $familyMendoza = Family::firstOrCreate(
            ['family_name' => 'Mendoza Family'],
            [
                'address'        => 'Blk 9 Lot 22, Phase 3',
                'barangay'       => 'Marinig',
                'city'           => 'Cabuyao',
                'province'       => 'Laguna',
                'contact_number' => '09571234567',
            ]
        );

        // ── 2. PARISHIONERS + USERS ──────────────────────────────────────────
        $this->command->line('  Creating parishioner profiles and user accounts...');

        // ── ACCOUNT 1: Aries Caña Cumpio ─────────────────────────────────────
        $aries = Parishioner::firstOrCreate(
            ['email' => 'aries.cumpio@gmail.com'],
            [
                'family_id'            => $familyCumpio->id,
                'first_name'           => 'Aries',
                'middle_name'          => 'Caña',
                'last_name'            => 'Cumpio',
                'birthdate'            => '1998-07-04',
                'gender'               => 'male',
                'civil_status'         => 'single',
                'address'              => 'Blk 5 Lot 10, Southville 1',
                'barangay'             => 'Niugan',
                'city'                 => 'Cabuyao',
                'province'             => 'Laguna',
                'postal_code'          => '4025',
                'contact_number'       => '09369454812',
                'is_head_of_family'    => true,
                'relationship_to_head' => 'Head',
                'is_active'            => true,
            ]
        );
        $userAries = $this->createUser('Aries Cumpio', 'aries.cumpio@gmail.com', $aries);

        // ── ACCOUNT 2: Maricel Santos ─────────────────────────────────────────
        $maricel = Parishioner::firstOrCreate(
            ['email' => 'maricel.santos@gmail.com'],
            [
                'family_id'            => $familySantos2->id,
                'first_name'           => 'Maricel',
                'middle_name'          => 'Reyes',
                'last_name'            => 'Santos',
                'birthdate'            => '1990-03-18',
                'gender'               => 'female',
                'civil_status'         => 'married',
                'address'              => 'Blk 8 Lot 3, Phase 2',
                'barangay'             => 'Banay-banay',
                'city'                 => 'Cabuyao',
                'province'             => 'Laguna',
                'postal_code'          => '4025',
                'contact_number'       => '09181234567',
                'is_head_of_family'    => false,
                'relationship_to_head' => 'Spouse',
                'is_active'            => true,
            ]
        );
        $userMaricel = $this->createUser('Maricel Santos', 'maricel.santos@gmail.com', $maricel);

        // ── ACCOUNT 3: Roberto Garcia ─────────────────────────────────────────
        $roberto = Parishioner::firstOrCreate(
            ['email' => 'roberto.garcia@gmail.com'],
            [
                'family_id'            => $familyGarcia->id,
                'first_name'           => 'Roberto',
                'middle_name'          => 'Cruz',
                'last_name'            => 'Garcia',
                'birthdate'            => '1975-11-22',
                'gender'               => 'male',
                'civil_status'         => 'married',
                'address'              => 'Blk 2 Lot 15, Phase 4',
                'barangay'             => 'Pulo',
                'city'                 => 'Cabuyao',
                'province'             => 'Laguna',
                'postal_code'          => '4025',
                'contact_number'       => '09271234567',
                'is_head_of_family'    => true,
                'relationship_to_head' => 'Head',
                'is_active'            => true,
            ]
        );
        $userRoberto = $this->createUser('Roberto Garcia', 'roberto.garcia@gmail.com', $roberto);

        // ── ACCOUNT 4: Lourdes Villanueva ─────────────────────────────────────
        $lourdes = Parishioner::firstOrCreate(
            ['email' => 'lourdes.villanueva@gmail.com'],
            [
                'family_id'            => $familyVillanueva->id,
                'first_name'           => 'Lourdes',
                'middle_name'          => 'Bautista',
                'last_name'            => 'Villanueva',
                'birthdate'            => '1985-05-30',
                'gender'               => 'female',
                'civil_status'         => 'widowed',
                'address'              => 'Blk 14 Lot 6, Phase 1',
                'barangay'             => 'Sala',
                'city'                 => 'Cabuyao',
                'province'             => 'Laguna',
                'postal_code'          => '4025',
                'contact_number'       => '09481234567',
                'is_head_of_family'    => true,
                'relationship_to_head' => 'Head',
                'is_active'            => true,
            ]
        );
        $userLourdes = $this->createUser('Lourdes Villanueva', 'lourdes.villanueva@gmail.com', $lourdes);

        // ── ACCOUNT 5: Danilo Mendoza ─────────────────────────────────────────
        $danilo = Parishioner::firstOrCreate(
            ['email' => 'danilo.mendoza@gmail.com'],
            [
                'family_id'            => $familyMendoza->id,
                'first_name'           => 'Danilo',
                'middle_name'          => 'Flores',
                'last_name'            => 'Mendoza',
                'birthdate'            => '2001-09-14',
                'gender'               => 'male',
                'civil_status'         => 'single',
                'address'              => 'Blk 9 Lot 22, Phase 3',
                'barangay'             => 'Marinig',
                'city'                 => 'Cabuyao',
                'province'             => 'Laguna',
                'postal_code'          => '4025',
                'contact_number'       => '09571234567',
                'is_head_of_family'    => false,
                'relationship_to_head' => 'Child',
                'is_active'            => true,
            ]
        );
        $userDanilo = $this->createUser('Danilo Mendoza', 'danilo.mendoza@gmail.com', $danilo);

        // ── 3. SACRAMENTAL RECORDS ────────────────────────────────────────────
        $this->command->line('  Creating sacramental records...');

        $priest = 'Rev. Fr. Erwin S. Sanchez';

        // Aries — Baptism
        $ariesBaptism = SacramentalRecord::firstOrCreate(
            ['parishioner_id' => $aries->id, 'type' => 'baptism'],
            [
                'date_administered' => '1998-07-26',
                'celebrant'         => $priest,
                'venue'             => 'Mary Help of Christians Parish',
                'register_number'   => 'B-1998-064',
                'page_number'       => '16',
                'line_number'       => '8',
                'godparents'        => ['Eduardo Cumpio', 'Rosario Caña'],
                'notes'             => 'Rodolfo Caña Cumpio and Nenita Santos Cumpio',
                'recorded_by'       => $this->secretary?->id ?? $this->admin->id,
                'verified_by'       => $this->admin->id,
                'verified_at'       => now()->subMonths(4),
            ]
        );

        // Aries — Confirmation
        SacramentalRecord::firstOrCreate(
            ['parishioner_id' => $aries->id, 'type' => 'confirmation'],
            [
                'date_administered' => '2014-04-12',
                'celebrant'         => 'Most Rev. Bishop Rolando Tria Tirona',
                'venue'             => 'San Pablo Cathedral',
                'register_number'   => 'C-2014-031',
                'page_number'       => '8',
                'line_number'       => '3',
                'sponsors'          => ['Eduardo Cumpio'],
                'recorded_by'       => $this->secretary?->id ?? $this->admin->id,
                'verified_by'       => $this->admin->id,
                'verified_at'       => now()->subMonths(4),
            ]
        );

        // Maricel — Baptism
        $maricelBaptism = SacramentalRecord::firstOrCreate(
            ['parishioner_id' => $maricel->id, 'type' => 'baptism'],
            [
                'date_administered' => '1990-04-01',
                'celebrant'         => 'Rev. Fr. Antonio Villanueva',
                'venue'             => 'Sto. Nino Parish, Cabuyao',
                'register_number'   => 'B-1990-038',
                'page_number'       => '9',
                'line_number'       => '12',
                'godparents'        => ['Manuel Reyes', 'Celia Torres'],
                'notes'             => 'Vicente Reyes Santos and Natividad Cruz Reyes',
                'recorded_by'       => $this->secretary?->id ?? $this->admin->id,
                'verified_by'       => $this->admin->id,
                'verified_at'       => now()->subMonths(6),
            ]
        );

        // Maricel — First Communion
        SacramentalRecord::firstOrCreate(
            ['parishioner_id' => $maricel->id, 'type' => 'first_communion'],
            [
                'date_administered' => '2000-05-14',
                'celebrant'         => 'Rev. Fr. Miguel Fernandez',
                'venue'             => 'Mary Help of Christians Parish',
                'register_number'   => 'FC-2000-015',
                'page_number'       => '4',
                'line_number'       => '7',
                'recorded_by'       => $this->secretary?->id ?? $this->admin->id,
            ]
        );

        // Roberto — Baptism
        $robertoBaptism = SacramentalRecord::firstOrCreate(
            ['parishioner_id' => $roberto->id, 'type' => 'baptism'],
            [
                'date_administered' => '1975-12-08',
                'celebrant'         => 'Rev. Fr. Jose Bautista',
                'venue'             => 'Mary Help of Christians Parish',
                'register_number'   => 'B-1975-112',
                'page_number'       => '28',
                'line_number'       => '4',
                'godparents'        => ['Alfredo Cruz', 'Herminia Diaz'],
                'notes'             => 'Ernesto Cruz Garcia and Florencia Diaz Garcia',
                'recorded_by'       => $this->secretary?->id ?? $this->admin->id,
                'verified_by'       => $this->admin->id,
                'verified_at'       => now()->subMonths(8),
            ]
        );

        // Roberto — Marriage
        SacramentalRecord::firstOrCreate(
            ['parishioner_id' => $roberto->id, 'type' => 'marriage'],
            [
                'date_administered' => '2003-06-14',
                'celebrant'         => 'Rev. Fr. Miguel Fernandez',
                'venue'             => 'Mary Help of Christians Parish',
                'register_number'   => 'M-2003-007',
                'page_number'       => '2',
                'line_number'       => '7',
                'sponsors'          => ['Alfredo Cruz', 'Herminia Diaz', 'Ramon Torres', 'Celia Bautista'],
                'witnesses'         => ['Danilo Cruz', 'Elisa Mendez'],
                'recorded_by'       => $this->secretary?->id ?? $this->admin->id,
                'verified_by'       => $this->admin->id,
                'verified_at'       => now()->subMonths(8),
            ]
        );

        // Lourdes — Baptism
        $lourdesBaptism = SacramentalRecord::firstOrCreate(
            ['parishioner_id' => $lourdes->id, 'type' => 'baptism'],
            [
                'date_administered' => '1985-06-24',
                'celebrant'         => 'Rev. Fr. Antonio Villanueva',
                'venue'             => 'Mary Help of Christians Parish',
                'register_number'   => 'B-1985-071',
                'page_number'       => '18',
                'line_number'       => '2',
                'godparents'        => ['Ricardo Bautista', 'Cristina Lim'],
                'notes'             => 'Bernardo Bautista Villanueva and Evelyn Cruz Villanueva',
                'recorded_by'       => $this->secretary?->id ?? $this->admin->id,
                'verified_by'       => $this->admin->id,
                'verified_at'       => now()->subMonths(5),
            ]
        );

        // Danilo — Baptism
        $daniloBaptism = SacramentalRecord::firstOrCreate(
            ['parishioner_id' => $danilo->id, 'type' => 'baptism'],
            [
                'date_administered' => '2001-10-07',
                'celebrant'         => $priest,
                'venue'             => 'Mary Help of Christians Parish',
                'register_number'   => 'B-2001-099',
                'page_number'       => '24',
                'line_number'       => '11',
                'godparents'        => ['Oscar Flores', 'Patricia Mendoza'],
                'notes'             => 'Gregorio Flores Mendoza and Isidra Santos Mendoza',
                'recorded_by'       => $this->secretary?->id ?? $this->admin->id,
                'verified_by'       => $this->admin->id,
                'verified_at'       => now()->subMonths(2),
            ]
        );

        // ── 4. BOOKINGS ───────────────────────────────────────────────────────
        $this->command->line('  Creating bookings...');

        // Aries — Pending house blessing (upcoming)
        $bAries1 = Booking::firstOrCreate(
            ['parishioner_id' => $aries->id, 'booking_type' => 'house_blessing', 'status' => 'pending'],
            [
                'scheduled_date' => now()->addDays(5)->toDateString(),
                'scheduled_time' => '10:00:00',
                'service_fee'    => 300.00,
                'address'        => 'Blk 5 Lot 10, Southville 1, Niugan, Cabuyao',
                'notes'          => 'Blessing for our newly renovated home.',
                'reminder_sent'  => false,
            ]
        );
        if (!$bAries1->qrCode) { try { $this->qrService->generateForBooking($bAries1); } catch (\Exception $e) {} }

        // Aries — Completed mass intention
        $bAries2 = Booking::firstOrCreate(
            ['parishioner_id' => $aries->id, 'booking_type' => 'mass_intention', 'status' => 'completed'],
            [
                'scheduled_date' => now()->subDays(20)->toDateString(),
                'scheduled_time' => '06:00:00',
                'service_fee'    => 200.00,
                'notes'          => 'Thanksgiving Mass for family blessings.',
                'admin_notes'    => 'Mass offered. Certificate issued.',
                'confirmed_by'   => $this->secretary?->id ?? $this->admin->id,
                'confirmed_at'   => now()->subDays(22),
                'reminder_sent'  => true,
            ]
        );
        if (!$bAries2->qrCode) { try { $this->qrService->generateForBooking($bAries2); } catch (\Exception $e) {} }

        // Maricel — Confirmed baptism (upcoming)
        $bMaricel1 = Booking::firstOrCreate(
            ['parishioner_id' => $maricel->id, 'booking_type' => 'baptism', 'status' => 'confirmed'],
            [
                'scheduled_date' => now()->addDays(12)->toDateString(),
                'scheduled_time' => '09:00:00',
                'service_fee'    => 500.00,
                'notes'          => 'Baptism for our second child, Miguel Santos.',
                'admin_notes'    => 'Pre-baptismal seminar completed. Bring godparents IDs.',
                'confirmed_by'   => $this->secretary?->id ?? $this->admin->id,
                'confirmed_at'   => now()->subDays(3),
                'reminder_sent'  => false,
            ]
        );
        if (!$bMaricel1->qrCode) { try { $this->qrService->generateForBooking($bMaricel1); } catch (\Exception $e) {} }

        // Maricel — Completed wedding (past)
        $bMaricel2 = Booking::firstOrCreate(
            ['parishioner_id' => $maricel->id, 'booking_type' => 'wedding', 'status' => 'completed'],
            [
                'scheduled_date' => now()->subMonths(3)->toDateString(),
                'scheduled_time' => '10:00:00',
                'service_fee'    => 3000.00,
                'notes'          => 'Church wedding for Maricel Reyes Santos and Ramon Cruz Santos.',
                'confirmed_by'   => $this->secretary?->id ?? $this->admin->id,
                'confirmed_at'   => now()->subMonths(3)->subDays(14),
                'reminder_sent'  => true,
            ]
        );
        if (!$bMaricel2->qrCode) { try { $this->qrService->generateForBooking($bMaricel2); } catch (\Exception $e) {} }

        // Roberto — Pending car blessing
        $bRoberto1 = Booking::firstOrCreate(
            ['parishioner_id' => $roberto->id, 'booking_type' => 'car_blessing', 'status' => 'pending'],
            [
                'scheduled_date' => now()->addDays(8)->toDateString(),
                'scheduled_time' => '14:00:00',
                'service_fee'    => 200.00,
                'notes'          => 'New SUV — blessing requested.',
                'reminder_sent'  => false,
            ]
        );
        if (!$bRoberto1->qrCode) { try { $this->qrService->generateForBooking($bRoberto1); } catch (\Exception $e) {} }

        // Roberto — Completed funeral mass (past)
        $bRoberto2 = Booking::firstOrCreate(
            ['parishioner_id' => $roberto->id, 'booking_type' => 'funeral_mass', 'status' => 'completed'],
            [
                'scheduled_date' => now()->subMonths(1)->toDateString(),
                'scheduled_time' => '08:00:00',
                'service_fee'    => 1500.00,
                'notes'          => 'Funeral Mass for Ernesto Cruz Garcia.',
                'confirmed_by'   => $this->secretary?->id ?? $this->admin->id,
                'confirmed_at'   => now()->subMonths(1)->subDays(2),
                'reminder_sent'  => true,
            ]
        );
        if (!$bRoberto2->qrCode) { try { $this->qrService->generateForBooking($bRoberto2); } catch (\Exception $e) {} }

        // Lourdes — Pending pre-marriage seminar
        $bLourdes1 = Booking::firstOrCreate(
            ['parishioner_id' => $lourdes->id, 'booking_type' => 'pre_marriage', 'status' => 'pending'],
            [
                'scheduled_date' => now()->addDays(18)->toDateString(),
                'scheduled_time' => '08:00:00',
                'service_fee'    => 500.00,
                'notes'          => 'Pre-Cana seminar requirement for upcoming wedding.',
                'reminder_sent'  => false,
            ]
        );
        if (!$bLourdes1->qrCode) { try { $this->qrService->generateForBooking($bLourdes1); } catch (\Exception $e) {} }

        // Lourdes — Confirmed sick call (upcoming)
        $bLourdes2 = Booking::firstOrCreate(
            ['parishioner_id' => $lourdes->id, 'booking_type' => 'sick_call', 'status' => 'confirmed'],
            [
                'scheduled_date' => now()->addDays(1)->toDateString(),
                'scheduled_time' => '15:00:00',
                'service_fee'    => 0.00,
                'address'        => 'Blk 14 Lot 6, Phase 1, Sala, Cabuyao',
                'notes'          => 'Anointing for mother who is ill at home.',
                'admin_notes'    => 'Fr. Erwin will visit. No payment needed.',
                'confirmed_by'   => $this->secretary?->id ?? $this->admin->id,
                'confirmed_at'   => now()->subDay(),
                'reminder_sent'  => false,
            ]
        );
        if (!$bLourdes2->qrCode) { try { $this->qrService->generateForBooking($bLourdes2); } catch (\Exception $e) {} }

        // Danilo — Confirmed confirmation catechesis
        $bDanilo1 = Booking::firstOrCreate(
            ['parishioner_id' => $danilo->id, 'booking_type' => 'confirmation_catechesis', 'status' => 'confirmed'],
            [
                'scheduled_date' => now()->addDays(25)->toDateString(),
                'scheduled_time' => '09:00:00',
                'service_fee'    => 200.00,
                'notes'          => 'Catechesis sessions for confirmation preparation.',
                'admin_notes'    => 'Session starts next Saturday.',
                'confirmed_by'   => $this->secretary?->id ?? $this->admin->id,
                'confirmed_at'   => now()->subDays(1),
                'reminder_sent'  => false,
            ]
        );
        if (!$bDanilo1->qrCode) { try { $this->qrService->generateForBooking($bDanilo1); } catch (\Exception $e) {} }

        // Danilo — Cancelled booking
        Booking::firstOrCreate(
            ['parishioner_id' => $danilo->id, 'booking_type' => 'house_blessing', 'status' => 'cancelled'],
            [
                'scheduled_date'      => now()->subDays(7)->toDateString(),
                'scheduled_time'      => '10:00:00',
                'service_fee'         => 300.00,
                'notes'               => 'Blessing for new apartment.',
                'cancelled_by'        => $userDanilo->id,
                'cancelled_at'        => now()->subDays(8),
                'cancellation_reason' => 'Postponed — moved to new apartment date changed.',
                'reminder_sent'       => false,
            ]
        );

        // ── 5. PAYMENTS ───────────────────────────────────────────────────────
        $this->command->line('  Creating payments...');

        // Aries — Paid GCash for mass intention
        $this->makePayment($bAries2->id, $aries->id, 200.00, 'gcash', 'paid', now()->subDays(20));

        // Aries — Pending GCash for house blessing
        $this->makePayment($bAries1->id, $aries->id, 300.00, 'gcash', 'pending', now()->subDays(1));

        // Maricel — Paid cash for baptism (deposit)
        $this->makePayment($bMaricel1->id, $maricel->id, 500.00, 'cash', 'paid', now()->subDays(3));

        // Maricel — Paid Maya for wedding
        $this->makePayment($bMaricel2->id, $maricel->id, 3000.00, 'maya', 'paid', now()->subMonths(3)->subDays(7));

        // Roberto — Paid cash for funeral mass
        $this->makePayment($bRoberto2->id, $roberto->id, 1500.00, 'cash', 'paid', now()->subMonths(1)->subDays(1));

        // Roberto — Pending for car blessing
        $this->makePayment($bRoberto1->id, $roberto->id, 200.00, 'gcash', 'pending', now());

        // Danilo — Paid GCash for catechesis
        $this->makePayment($bDanilo1->id, $danilo->id, 200.00, 'gcash', 'paid', now()->subDays(2));

        // ── 6. CERTIFICATES ───────────────────────────────────────────────────
        $this->command->line('  Creating certificate requests...');

        // Aries — Baptism certificate released
        $this->makeCertificate($aries->id, 'baptism', $ariesBaptism->id, 'released',
            'School enrollment requirement', now()->subMonths(2));

        // Aries — Confirmation certificate issued
        $this->makeCertificate($aries->id, 'confirmation', null, 'issued',
            'Employment NBI clearance requirement', now()->subMonths(1));

        // Maricel — Baptism certificate released
        $this->makeCertificate($maricel->id, 'baptism', $maricelBaptism->id, 'released',
            'Government ID requirement', now()->subMonths(4));

        // Maricel — No-impediment certificate issued
        $this->makeCertificate($maricel->id, 'no_impediment', null, 'issued',
            'Marriage license requirement', now()->subMonths(4));

        // Roberto — Baptism certificate released
        $this->makeCertificate($roberto->id, 'baptism', $robertoBaptism->id, 'released',
            'Travel abroad requirement', now()->subMonths(3));

        // Lourdes — Baptism certificate draft
        $this->makeCertificate($lourdes->id, 'baptism', $lourdesBaptism->id, 'draft',
            'Scholarship application', now()->subDays(5));

        // Danilo — Baptism certificate released
        $this->makeCertificate($danilo->id, 'baptism', $daniloBaptism->id, 'released',
            'Confirmation requirement', now()->subMonths(1));

        // ── 7. SUMMARY ────────────────────────────────────────────────────────
        $this->command->newLine();
        $this->command->info('✅ Demo user accounts seeded!');
        $this->command->newLine();
        $this->command->line('  <fg=green>DEMO PARISHIONER LOGINS (all password: Password@123):</>');
        $this->command->table(
            ['Name', 'Email', 'Status'],
            [
                ['Aries Cumpio',       'aries.cumpio@gmail.com',       'Active'],
                ['Maricel Santos',     'maricel.santos@gmail.com',     'Active'],
                ['Roberto Garcia',     'roberto.garcia@gmail.com',     'Active'],
                ['Lourdes Villanueva', 'lourdes.villanueva@gmail.com', 'Active'],
                ['Danilo Mendoza',     'danilo.mendoza@gmail.com',     'Active'],
            ]
        );
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────

    private function createUser(string $name, string $email, Parishioner $parishioner): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'           => $name,
                'password'       => Hash::make('Password@123'),
                'parishioner_id' => $parishioner->id,
                'is_active'      => true,
                'last_login_at'  => now()->subDays(rand(1, 10)),
            ]
        );

        // Ensure the parishioner role is assigned
        if (!$user->hasRole('parishioner')) {
            $user->assignRole('parishioner');
        }

        // Ensure the user↔parishioner link is set both ways
        if (!$user->parishioner_id) {
            $user->update(['parishioner_id' => $parishioner->id]);
        }

        return $user;
    }

    private function makePayment(
        int $bookingId,
        int $parishionerId,
        float $amount,
        string $method,
        string $status,
        Carbon $date
    ): void {
        if (Payment::where('booking_id', $bookingId)->exists()) {
            return;
        }

        $data = [
            'parishioner_id'    => $parishionerId,
            'booking_id'        => $bookingId,
            'amount'            => $amount,
            'payment_method'    => $method,
            'transaction_type'  => 'debit',
            'status'            => $status,
            'created_at'        => $date,
            'updated_at'        => $date,
        ];

        if ($status === 'paid') {
            $data['paid_at']          = $date;
            $data['verified_by']      = $this->admin->id;
            $data['verified_at']      = $date;
            $data['notes']            = ucfirst($method) . ' payment — verified by admin.';

            if (in_array($method, ['gcash', 'maya'])) {
                $data['gateway_reference'] = strtoupper($method) . '-' . strtoupper(substr(uniqid(), -8));
            }
        }

        Payment::create($data);
    }

    private function makeCertificate(
        int $parishionerId,
        string $type,
        ?int $sacramentalRecordId,
        string $status,
        string $purpose,
        Carbon $date
    ): void {
        if (Certificate::where('parishioner_id', $parishionerId)->where('type', $type)->exists()) {
            return;
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use (
                $parishionerId, $type, $sacramentalRecordId, $status, $purpose, $date
            ) {
                $cert = Certificate::create([
                    'parishioner_id'        => $parishionerId,
                    'sacramental_record_id' => $sacramentalRecordId,
                    'type'                  => $type,
                    'issued_date'           => $date->toDateString(),
                    'issued_by'             => $this->secretary?->id ?? $this->admin->id,
                    'purpose'               => $purpose,
                    'status'                => $status,
                    'created_at'            => $date,
                    'updated_at'            => $date,
                ]);

                if ($status !== 'draft') {
                    try {
                        $this->qrService->generateForCertificate($cert);
                    } catch (\Exception $e) {
                        // QR generation is non-critical; continue seeding
                    }
                }
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Duplicate certificate_number race — skip silently
        }
    }
}
