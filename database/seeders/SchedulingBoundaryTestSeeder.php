<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Parishioner;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

/**
 * SchedulingBoundaryTestSeeder
 * ============================================================
 * Boundary Value Analysis (BVA) + Stress test for the
 * scheduling / conflict-management module.
 *
 * PURPOSE
 * -------
 * Generates a controlled dataset so the panel can verify that:
 *   1. The booking calendar API (/api/booked-dates) correctly
 *      classifies dates as available (green), busy (yellow),
 *      or fully booked / locked (red).
 *   2. The server-side guard in BookingController::store()
 *      rejects any submission when the date already has ≥ 5
 *      active bookings for the same service type.
 *   3. The time-slot conflict detector rejects overlapping
 *      appointments within the same service type on the same day.
 *
 * CAPACITY RULE (from /api/booked-dates)
 *   cnt  = 1–4  → BUSY   (yellow)  — still bookable
 *   cnt  ≥ 5    → BOOKED (red)     — locked, no new bookings accepted
 *
 * BVT SCENARIOS (all use 'house_blessing' unless noted)
 * ─────────────────────────────────────────────────────
 * SCENARIO | DATE      | BOOKINGS | EXPECTED STATE | TEST INTENT
 * ─────────|───────────|──────────|────────────────|─────────────────────────────
 *    A     | BVT_MON   |    0     | Available      | Lower bound — empty date
 *    B     | BVT_TUE   |    1     | Available      | Min+1 — just below busy
 *    C     | BVT_WED   |    4     | Busy           | Max-1 below capacity
 *    D     | BVT_THU   |    5     | Fully Booked   | AT threshold — must lock
 *    E     | BVT_FRI   |    6     | Fully Booked   | Over capacity — stress test
 *    F     | BVT_SAT   |   2+2    | Time conflict  | Same day, overlapping slots
 *           (wedding, timed)
 *
 * USAGE
 * -----
 *   php artisan db:seed --class=SchedulingBoundaryTestSeeder
 *
 * To RESET / clean up:
 *   php artisan db:seed --class=SchedulingBoundaryTestSeeder --reset
 *   (pass reset=1 as env or re-run; seeder deletes its own rows by reference tag)
 *
 * The seeder tags every booking it creates with
 *   admin_notes = '[BVT-TEST]'
 * so they can be identified and removed without touching real data.
 */
class SchedulingBoundaryTestSeeder extends Seeder
{
    /** Tag embedded in admin_notes so test rows are easy to find and delete. */
    const TAG = '[BVT-TEST]';

    /** Capacity threshold — must match /api/booked-dates logic in routes/web.php */
    const CAPACITY = 5;

    public function run(): void
    {
        // ── Clean up any previous BVT run first ──────────────────────────────
        $deleted = Booking::where('admin_notes', 'like', '%' . self::TAG . '%')->delete();
        if ($deleted > 0) {
            $this->command->info("Removed $deleted stale BVT bookings from previous run.");
        }

        // ── Resolve a real parishioner to satisfy FK constraint ───────────────
        $parishioner = Parishioner::first();
        if (!$parishioner) {
            $this->command->error('No parishioner found. Run the main seeders first.');
            return;
        }

        // ── Anchor dates: next Monday through Saturday ────────────────────────
        $monday = Carbon::now()->next(Carbon::MONDAY)->startOfDay();
        $dates  = [
            'A_empty'    => $monday->copy(),                  // Scenario A — 0 bookings
            'B_min1'     => $monday->copy()->addDay(),        // Scenario B — 1 booking
            'C_max_minus1' => $monday->copy()->addDays(2),    // Scenario C — 4 bookings
            'D_at_cap'   => $monday->copy()->addDays(3),      // Scenario D — 5 bookings
            'E_over_cap' => $monday->copy()->addDays(4),      // Scenario E — 6 bookings
            'F_conflict' => $monday->copy()->addDays(5),      // Scenario F — time conflict
        ];

        $this->command->line('');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('  Scheduling BVT — Boundary Value Analysis Test Data  ');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->line('');

        // ── SCENARIO A: Empty date (lower bound) ─────────────────────────────
        // No bookings seeded. The calendar should show this date as Available (green).
        $this->command->line('SCENARIO A — ' . $dates['A_empty']->format('D M d') . ': 0 bookings on house_blessing');
        $this->command->line('  Expected → Available (green) | Booking ALLOWED');
        $this->command->line('  No rows seeded — baseline empty date.');
        $this->command->line('');

        // ── SCENARIO B: One booking (min+1, just above zero) ─────────────────
        $this->seed($parishioner->id, 'house_blessing', $dates['B_min1'], 1, '08:00');
        $this->command->line('SCENARIO B — ' . $dates['B_min1']->format('D M d') . ': 1 booking on house_blessing');
        $this->command->line('  Expected → Available/green (cnt=1, below busy threshold of 5)');
        $this->command->line('  New bookings on this date: ALLOWED');
        $this->command->line('');

        // ── SCENARIO C: 4 bookings (MAX-1, boundary just before capacity) ─────
        $this->seed($parishioner->id, 'house_blessing', $dates['C_max_minus1'], 4, '08:00', 30);
        $this->command->line('SCENARIO C — ' . $dates['C_max_minus1']->format('D M d') . ': 4 bookings on house_blessing');
        $this->command->line('  Expected → Busy/yellow (cnt=4, one slot below capacity)');
        $this->command->line('  New bookings on this date: ALLOWED (but warned as busy)');
        $this->command->line('');

        // ── SCENARIO D: Exactly 5 bookings (AT capacity — must lock) ──────────
        $this->seed($parishioner->id, 'house_blessing', $dates['D_at_cap'], 5, '08:00', 30);
        $this->command->line('SCENARIO D — ' . $dates['D_at_cap']->format('D M d') . ': 5 bookings on house_blessing');
        $this->command->line('  Expected → Fully Booked/red (cnt=5 = CAPACITY threshold)');
        $this->command->line('  New bookings on this date: REJECTED by server-side guard');
        $this->command->line('');

        // ── SCENARIO E: 6 bookings (over capacity — stress test) ─────────────
        // Seeded directly via Eloquent to simulate a race condition or admin override.
        // The server guard must still reject any 7th booking attempt.
        $this->seed($parishioner->id, 'house_blessing', $dates['E_over_cap'], 6, '08:00', 30);
        $this->command->line('SCENARIO E — ' . $dates['E_over_cap']->format('D M d') . ': 6 bookings on house_blessing (stress)');
        $this->command->line('  Expected → Fully Booked/red (cnt=6 > CAPACITY)');
        $this->command->line('  New bookings on this date: REJECTED — over-capacity stress case');
        $this->command->line('');

        // ── SCENARIO F: Time-slot conflict (same day, overlapping windows) ────
        // Wedding has duration_minutes=90. Two weddings 60 minutes apart = overlap.
        // Booking 1: 09:00  window 09:00–10:30+buffer
        // Booking 2: 09:45  overlaps with booking 1 — conflict
        // Booking 3: 11:30  no overlap — allowed
        $this->seedTimed($parishioner->id, 'wedding', $dates['F_conflict'], '09:00');
        $this->seedTimed($parishioner->id, 'wedding', $dates['F_conflict'], '11:30');
        $this->command->line('SCENARIO F — ' . $dates['F_conflict']->format('D M d') . ': Time-slot conflict (wedding)');
        $this->command->line('  Seeded bookings: 09:00 and 11:30 (non-overlapping)');
        $this->command->line('  A 3rd booking at 09:45 should be REJECTED (overlaps 09:00 window)');
        $this->command->line('  A 3rd booking at 11:00 should be REJECTED (overlaps 11:30 window)');
        $this->command->line('  A 3rd booking at 13:00 should be ALLOWED');
        $this->command->line('');

        // ── Summary ──────────────────────────────────────────────────────────
        $total = Booking::where('admin_notes', 'like', '%' . self::TAG . '%')->count();
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info("  BVT data seeded: $total bookings tagged [BVT-TEST]");
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->line('');
        $this->command->line('VERIFICATION STEPS:');
        $this->command->line('  1. Open the booking calendar for next week.');
        $this->command->line('  2. Confirm date colours:');
        $this->command->line('       ' . $dates['A_empty']->format('M d (D)') . ' → Green  (0 bookings)');
        $this->command->line('       ' . $dates['B_min1']->format('M d (D)') . ' → Green  (1 booking)');
        $this->command->line('       ' . $dates['C_max_minus1']->format('M d (D)') . ' → Yellow (4 bookings)');
        $this->command->line('       ' . $dates['D_at_cap']->format('M d (D)') . ' → Red    (5 bookings = capacity)');
        $this->command->line('       ' . $dates['E_over_cap']->format('M d (D)') . ' → Red    (6 bookings > capacity)');
        $this->command->line('       ' . $dates['F_conflict']->format('M d (D)') . ' → Yellow (2 weddings, no colour lock)');
        $this->command->line('');
        $this->command->line('  3. Try to submit a booking on ' . $dates['D_at_cap']->format('M d') . ' → must be REJECTED.');
        $this->command->line('  4. Try to submit a booking on ' . $dates['E_over_cap']->format('M d') . ' → must be REJECTED.');
        $this->command->line('  5. Try a wedding at 09:45 on ' . $dates['F_conflict']->format('M d') . ' → must be REJECTED.');
        $this->command->line('  6. Try a wedding at 13:00 on ' . $dates['F_conflict']->format('M d') . ' → must be ALLOWED.');
        $this->command->line('');
        $this->command->line('  To clean up: php artisan db:seed --class=SchedulingBoundaryTestSeeder');
        $this->command->line('  (re-running deletes old BVT rows then re-seeds fresh ones)');
        $this->command->line('');
    }

    /**
     * Seed N bookings on a date, spacing them by $gapMinutes intervals.
     */
    private function seed(
        int    $parishionerId,
        string $serviceSlug,
        Carbon $date,
        int    $count,
        string $startTime,
        int    $gapMinutes = 0
    ): void {
        $time = Carbon::createFromFormat('H:i', $startTime);

        for ($i = 0; $i < $count; $i++) {
            Booking::create([
                'parishioner_id'  => $parishionerId,
                'booking_type'    => $serviceSlug,
                'scheduled_date'  => $date->toDateString(),
                'scheduled_time'  => $gapMinutes > 0 ? $time->copy()->addMinutes($i * $gapMinutes)->format('H:i') : null,
                'status'          => ($i % 2 === 0) ? 'pending' : 'confirmed',
                'service_fee'     => \App\Models\Service::where('slug', $serviceSlug)->value('fee') ?? 0,
                'admin_notes'     => self::TAG . ' Scenario ' . $this->scenarioLabel($count) . ' #' . ($i + 1),
            ]);
        }
    }

    /** Seed a single timed booking (for conflict scenario). */
    private function seedTimed(
        int    $parishionerId,
        string $serviceSlug,
        Carbon $date,
        string $time
    ): void {
        Booking::create([
            'parishioner_id' => $parishionerId,
            'booking_type'   => $serviceSlug,
            'scheduled_date' => $date->toDateString(),
            'scheduled_time' => $time,
            'status'         => 'confirmed',
            'service_fee'    => \App\Models\Service::where('slug', $serviceSlug)->value('fee') ?? 0,
            'admin_notes'    => self::TAG . ' Scenario F conflict@' . $time,
        ]);
    }

    /** Return scenario letter based on booking count. */
    private function scenarioLabel(int $count): string
    {
        return match ($count) {
            1 => 'B',
            4 => 'C',
            5 => 'D',
            6 => 'E',
            default => 'X',
        };
    }
}
