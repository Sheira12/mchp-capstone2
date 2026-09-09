<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Certificate;
use App\Models\Parishioner;
use App\Models\Payment;
use App\Models\SacramentalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = $this->getStats();
        return view('admin.dashboard', compact('stats'));
    }

    public function stats(Request $request)
    {
        return response()->json($this->getStats($request->get('period', 'month')));
    }

    private function getStats(string $period = 'month'): array
    {
        $now   = now();
        $start = match ($period) {
            'week'  => $now->copy()->startOfWeek(),
            'year'  => $now->copy()->startOfYear(),
            default => $now->copy()->startOfMonth(),
        };

        $driver = DB::getDriverName();

        // ── CACHED STABLE STATS (60 seconds) ──────────────────────────────────
        // These values are counts/sums that change infrequently; caching for
        // 60 seconds eliminates 13 extra DB round-trips on every admin page load.
        // The cache key includes the period start date so it refreshes on period change.
        $cacheKey = 'dashboard_stats_' . $period . '_' . $start->toDateString();

        $cached = Cache::remember($cacheKey, 60, function () use ($start, $driver) {

            // ── Sacrament counts for "Sacraments This Month" chart ────────────────
            // Uses BOTH sources:
            //   1. sacramental_records (official records, date_administered this period)
            //   2. bookings (current scheduled services, for real-time dashboard accuracy)
            // Both are merged so the chart reflects what actually happened this period.

            // Source 1: actual sacramental records administered this period
            $sacramentFromRecords = SacramentalRecord::select('type', DB::raw('count(*) as total'))
                ->where('date_administered', '>=', $start)
                ->groupBy('type')
                ->pluck('total', 'type')
                ->toArray();

            // Source 2: bookings scheduled this period — map booking_type → sacrament category
            // This ensures the chart updates in real-time with new bookings even before
            // the formal sacramental record is entered.
            $sacramentBookingMap = [
                'baptism'          => 'baptism',
                'wedding'          => 'marriage',
                'marriage'         => 'marriage',
                'funeral_mass'     => 'death_burial',
                'funeral_service'  => 'death_burial',
                'death_burial'     => 'death_burial',
                'first_communion'  => 'first_communion',
                'confirmation'     => 'confirmation',
                'confirmation_catechesis' => 'confirmation',
            ];

            $sacramentFromBookings = Booking::select('booking_type', DB::raw('count(*) as total'))
                ->where('scheduled_date', '>=', $start)
                ->whereIn('booking_type', array_keys($sacramentBookingMap))
                ->whereIn('status', ['confirmed', 'completed', 'pending'])
                ->whereNull('deleted_at')
                ->groupBy('booking_type')
                ->pluck('total', 'booking_type')
                ->toArray();

            // Merge: use sacramental records as base, add booking counts where records are missing or lower
            $sacramentCounts = [
                'baptism'        => 0,
                'first_communion'=> 0,
                'confirmation'   => 0,
                'marriage'       => 0,
                'death_burial'   => 0,
            ];

            // Add from sacramental records
            foreach ($sacramentFromRecords as $type => $count) {
                if (array_key_exists($type, $sacramentCounts)) {
                    $sacramentCounts[$type] = max($sacramentCounts[$type], (int) $count);
                }
            }

            // Add from bookings (take the higher of the two sources)
            foreach ($sacramentFromBookings as $bookingType => $count) {
                $category = $sacramentBookingMap[$bookingType] ?? null;
                if ($category && array_key_exists($category, $sacramentCounts)) {
                    $sacramentCounts[$category] = max($sacramentCounts[$category], (int) $count);
                }
            }

            // Sacrament stats (frequency + percentage)
            $totalSacraments = array_sum($sacramentCounts);
            $sacramentStats  = [];
            foreach ($sacramentCounts as $type => $count) {
                $sacramentStats[$type] = [
                    'count'      => $count,
                    'percentage' => $totalSacraments > 0 ? round(($count / $totalSacraments) * 100, 1) : 0,
                ];
            }

            // Parishioner counts
            $totalParishioners = Parishioner::count();
            $newParishioners   = Parishioner::where('created_at', '>=', $start)->count();

            // Booking counts — use single aggregated query instead of 3 separate COUNT queries
            $bookingCounts = Booking::select([
                DB::raw("SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed"),
                DB::raw("SUM(CASE WHEN status = 'completed' AND updated_at >= '{$start->toDateTimeString()}' THEN 1 ELSE 0 END) as completed"),
            ])->whereNull('deleted_at')->first();

            $pendingBookings   = (int) ($bookingCounts->pending   ?? 0);
            $confirmedBookings = (int) ($bookingCounts->confirmed ?? 0);
            $completedBookings = (int) ($bookingCounts->completed ?? 0);

            // Revenue — single aggregated query for total + by-method
            $totalRevenue = Payment::paid()->where('paid_at', '>=', $start)->sum('amount');

            $revenueByMethod = Payment::paid()
                ->where('paid_at', '>=', $start)
                ->select('payment_method', DB::raw('sum(amount) as total'))
                ->groupBy('payment_method')
                ->pluck('total', 'payment_method')
                ->toArray();

            // Monthly sacrament trend (last 12 months)
            $yearExpr  = $driver === 'pgsql' ? "EXTRACT(YEAR FROM date_administered)::integer"  : "YEAR(date_administered)";
            $monthExpr = $driver === 'pgsql' ? "EXTRACT(MONTH FROM date_administered)::integer" : "MONTH(date_administered)";

            $monthlyTrend = SacramentalRecord::select(
                DB::raw("$yearExpr as year"),
                DB::raw("$monthExpr as month"),
                DB::raw('count(*) as total')
            )
                ->where('date_administered', '>=', now()->subMonths(12))
                ->groupBy('year', 'month')
                ->orderBy('year')->orderBy('month')
                ->get();

            // Monthly revenue trend (last 12 months)
            $yearPaidExpr  = $driver === 'pgsql' ? "EXTRACT(YEAR FROM paid_at)::integer"  : "YEAR(paid_at)";
            $monthPaidExpr = $driver === 'pgsql' ? "EXTRACT(MONTH FROM paid_at)::integer" : "MONTH(paid_at)";

            $revenueTrend = Payment::paid()
                ->select(
                    DB::raw("$yearPaidExpr as year"),
                    DB::raw("$monthPaidExpr as month"),
                    DB::raw('sum(amount) as total')
                )
                ->where('paid_at', '>=', now()->subMonths(12))
                ->groupBy('year', 'month')
                ->orderBy('year')->orderBy('month')
                ->get();

            // Pending certificates
            $pendingCertificates = Certificate::where('status', 'draft')->count();

            // Booking frequency by type this period
            $bookingByType = Booking::select('booking_type', DB::raw('count(*) as total'))
                ->where('created_at', '>=', $start)
                ->groupBy('booking_type')
                ->pluck('total', 'booking_type')
                ->toArray();

            $totalBookingsByType = array_sum($bookingByType);
            $bookingTypeStats    = [];
            foreach ($bookingByType as $type => $count) {
                $bookingTypeStats[$type] = [
                    'count'      => $count,
                    'percentage' => $totalBookingsByType > 0 ? round(($count / $totalBookingsByType) * 100, 1) : 0,
                ];
            }

            // ── Median payment — SQL PERCENTILE_CONT (PostgreSQL) ─────────────
            // Replaces the old approach that loaded ALL payments into PHP memory.
            // PERCENTILE_CONT(0.5) computes an exact median entirely in the DB.
            // Falls back to AVG on non-PostgreSQL drivers.
            if ($driver === 'pgsql') {
                $medianRow = DB::table('payments')
                    ->where('status', 'paid')
                    ->where('paid_at', '>=', now()->subMonths(12))
                    ->selectRaw('PERCENTILE_CONT(0.5) WITHIN GROUP (ORDER BY amount) AS median')
                    ->first();
                $medianPayment = (float) ($medianRow->median ?? 0);
            } else {
                $medianPayment = (float) Payment::paid()
                    ->where('paid_at', '>=', now()->subMonths(12))
                    ->avg('amount');
            }

            // Monthly booking frequency (last 12 months)
            $yearSchedExpr  = $driver === 'pgsql' ? "EXTRACT(YEAR FROM scheduled_date)::integer"  : "YEAR(scheduled_date)";
            $monthSchedExpr = $driver === 'pgsql' ? "EXTRACT(MONTH FROM scheduled_date)::integer" : "MONTH(scheduled_date)";

            $monthlyBookings = Booking::select(
                DB::raw("$yearSchedExpr as year"),
                DB::raw("$monthSchedExpr as month"),
                DB::raw('count(*) as total')
            )
                ->where('scheduled_date', '>=', now()->subMonths(12))
                ->whereNull('deleted_at')
                ->groupBy('year', 'month')
                ->orderBy('year')->orderBy('month')
                ->get();

            $avgMonthlyBookings = $monthlyBookings->count() > 0
                ? round($monthlyBookings->avg('total'), 1)
                : 0;

            return compact(
                'totalParishioners', 'newParishioners',
                'sacramentCounts', 'sacramentStats',
                'pendingBookings', 'confirmedBookings', 'completedBookings',
                'totalRevenue', 'revenueByMethod',
                'monthlyTrend', 'revenueTrend',
                'pendingCertificates',
                'bookingTypeStats',
                'medianPayment', 'monthlyBookings', 'avgMonthlyBookings'
            );
        });

        // ── REAL-TIME: recent bookings NOT cached (changes frequently) ─────────
        $recentBookings = Booking::with('parishioner')
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        return array_merge($cached, ['recentBookings' => $recentBookings]);
    }

    public function exportReport(Request $request)
    {
        $validated = $request->validate([
            'type'       => ['required', 'in:pdf,excel'],
            'period'     => ['required', 'in:week,month,year,custom'],
            'date_from'  => ['nullable', 'required_if:period,custom', 'date'],
            'date_to'    => ['nullable', 'required_if:period,custom', 'date', 'after_or_equal:date_from'],
            'category'   => ['nullable', 'string'],
        ]);

        $reportService = app(\App\Services\ReportService::class);
        return $reportService->generate($validated);
    }

    /**
     * Clear all dashboard caches — called when admin wants fresh stats.
     */
    public function clearCache()
    {
        $periods = ['month', 'week', 'year'];
        $dates   = [
            now()->startOfMonth()->toDateString(),
            now()->startOfWeek()->toDateString(),
            now()->startOfYear()->toDateString(),
        ];
        foreach ($periods as $i => $period) {
            Cache::forget('dashboard_stats_' . $period . '_' . $dates[$i]);
        }
        return back()->with('success', 'Dashboard cache cleared. Statistics will refresh on next load.');
    }
}
