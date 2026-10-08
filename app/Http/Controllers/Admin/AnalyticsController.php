<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingRequirement;
use App\Models\Parishioner;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalyticsController extends Controller
{
    /** True when running on PostgreSQL (Supabase/Render). */
    private bool $isPg;

    public function __construct()
    {
        $this->isPg = DB::getDriverName() === 'pgsql';
    }

    public function index()
    {
        return view('admin.analytics.index');
    }

    /**
     * Main data endpoint — returns all analytics data as JSON.
     * Cached for 5 minutes. Can be busted via ?bust=1 by admins.
     * Every sub-method is individually try/catch so one bad query
     * doesn't kill the whole response.
     */
    public function data(Request $request)
    {
        if ($request->get('bust')) {
            Cache::forget('analytics_full');
        }

        $data = Cache::remember('analytics_full', 300, function () {
            $methods = [
                'heatmap'            => fn() => $this->heatmap(),
                'sacrament_trends'   => fn() => $this->sacramentTrends(),
                'service_demand'     => fn() => $this->serviceDemand(),
                'revenue_by_service' => fn() => $this->revenueByService(),
                'demographics_age'   => fn() => $this->demographicsAge(),
                'demographics_brgy'  => fn() => $this->demographicsBarangay(),
                'monthly_bookings'   => fn() => $this->monthlyBookings(),
                'forecast'           => fn() => $this->forecast(),
                'insights'           => fn() => $this->insights(),
                'req_turnaround'     => fn() => $this->requirementTurnaround(),
            ];

            $result = [];
            foreach ($methods as $key => $fn) {
                try {
                    $result[$key] = $fn();
                } catch (\Throwable $e) {
                    Log::warning("Analytics [{$key}] failed: " . $e->getMessage());
                    $result[$key] = [];
                }
            }
            return $result;
        });

        return response()->json($data);
    }

    /**
     * CSV export of booking data.
     */
    public function export()
    {
        $bookings = Booking::with('parishioner')
            ->orderByDesc('scheduled_date')
            ->limit(2000)
            ->get();

        $headers = ['ID','Reference','Type','Parishioner','Date','Time','Status','Fee','Location'];

        return response()->streamDownload(function () use ($bookings, $headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($bookings as $b) {
                fputcsv($handle, [
                    $b->id,
                    $b->reference_number,
                    $b->getTypeLabel(),
                    $b->parishioner?->full_name ?? '—',
                    $b->scheduled_date?->format('Y-m-d') ?? '—',
                    $b->scheduled_time ?? '—',
                    $b->status,
                    $b->service_fee,
                    $b->location_type ?? 'in_church',
                ]);
            }
            fclose($handle);
        }, 'bookings-export-' . now()->format('Ymd') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  DATA METHODS — all dual MySQL/PostgreSQL
    // ─────────────────────────────────────────────────────────────────────────

    /** Booking heatmap: weekday × hour bucket → count. */
    private function heatmap(): array
    {
        if ($this->isPg) {
            $rows = Booking::whereNotNull('scheduled_time')
                ->whereIn('status', ['confirmed', 'completed', 'pending'])
                ->whereNotNull('scheduled_date')
                ->selectRaw("EXTRACT(DOW FROM scheduled_date)::int AS dow, EXTRACT(HOUR FROM scheduled_time::time)::int AS hour, COUNT(*) AS cnt")
                ->groupByRaw('dow, hour')
                ->orderByRaw('dow, hour')
                ->get();
        } else {
            // MySQL: DAYOFWEEK returns 1=Sun..7=Sat; we want 0=Sun..6=Sat
            $rows = Booking::whereNotNull('scheduled_time')
                ->whereIn('status', ['confirmed', 'completed', 'pending'])
                ->whereNotNull('scheduled_date')
                ->selectRaw("(DAYOFWEEK(scheduled_date) - 1) AS dow, HOUR(scheduled_time) AS hour, COUNT(*) AS cnt")
                ->groupByRaw('dow, hour')
                ->orderByRaw('dow, hour')
                ->get();
        }

        $grid = [];
        for ($d = 0; $d < 7; $d++) {
            for ($h = 0; $h < 24; $h++) {
                $grid[$d][$h] = 0;
            }
        }
        foreach ($rows as $r) {
            $grid[(int)$r->dow][(int)$r->hour] = (int)$r->cnt;
        }

        $max = max(1, collect($rows)->max('cnt') ?? 1);

        return ['grid' => $grid, 'max' => $max];
    }

    /** Sacrament trends: monthly counts for last 24 months. */
    private function sacramentTrends(): array
    {
        $monthExpr = $this->isPg
            ? "TO_CHAR(scheduled_date, 'YYYY-MM')"
            : "DATE_FORMAT(scheduled_date, '%Y-%m')";

        $rows = Booking::whereIn('status', ['confirmed', 'completed'])
            ->where('scheduled_date', '>=', now()->subMonths(24))
            ->selectRaw("{$monthExpr} AS month, booking_type, COUNT(*) AS cnt")
            ->groupByRaw("month, booking_type")
            ->orderByRaw("month")
            ->get();

        $months = [];
        $types  = [];
        foreach ($rows as $r) {
            $months[$r->month] = true;
            $types[$r->booking_type] = Booking::TYPES[$r->booking_type] ?? $r->booking_type;
        }

        $labels   = array_keys($months);
        $datasets = [];
        foreach ($types as $slug => $label) {
            $values = [];
            foreach ($labels as $m) {
                $found    = $rows->first(fn($r) => $r->month === $m && $r->booking_type === $slug);
                $values[] = $found ? (int) $found->cnt : 0;
            }
            $datasets[] = ['label' => $label, 'slug' => $slug, 'data' => $values];
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    /** Service demand ranking: all-time bookings by type. */
    private function serviceDemand(): array
    {
        $rows = Booking::selectRaw("booking_type, COUNT(*) AS cnt, SUM(CASE WHEN status IN ('confirmed','completed') THEN 1 ELSE 0 END) AS confirmed_cnt")
            ->groupBy('booking_type')
            ->orderByDesc('cnt')
            ->get();

        return $rows->map(fn($r) => [
            'type'      => $r->booking_type,
            'label'     => Booking::TYPES[$r->booking_type] ?? $r->booking_type,
            'total'     => (int) $r->cnt,
            'confirmed' => (int) $r->confirmed_cnt,
        ])->values()->all();
    }

    /** Revenue by booking service type. */
    private function revenueByService(): array
    {
        $rows = Payment::where('payments.status', 'paid')
            ->where('payments.transaction_type', 'debit')
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->selectRaw("bookings.booking_type, SUM(payments.amount) AS total")
            ->groupBy('bookings.booking_type')
            ->orderByDesc('total')
            ->get();

        return $rows->map(fn($r) => [
            'type'  => $r->booking_type,
            'label' => Booking::TYPES[$r->booking_type] ?? $r->booking_type,
            'total' => (float) $r->total,
        ])->values()->all();
    }

    /** Parishioner age group demographics. */
    private function demographicsAge(): array
    {
        if ($this->isPg) {
            $ageExpr = "DATE_PART('year', AGE(birthdate))";
        } else {
            $ageExpr = "TIMESTAMPDIFF(YEAR, birthdate, CURDATE())";
        }

        $rows = Parishioner::whereNotNull('birthdate')
            ->where('is_active', true)
            ->selectRaw("
                CASE
                    WHEN {$ageExpr} < 13 THEN 'Children (0-12)'
                    WHEN {$ageExpr} < 18 THEN 'Teens (13-17)'
                    WHEN {$ageExpr} < 30 THEN 'Young Adults (18-29)'
                    WHEN {$ageExpr} < 45 THEN 'Adults (30-44)'
                    WHEN {$ageExpr} < 60 THEN 'Middle-aged (45-59)'
                    ELSE 'Senior (60+)'
                END AS age_group,
                COUNT(*) AS cnt
            ")
            ->groupByRaw('age_group')
            ->get();

        $order = ['Children (0-12)','Teens (13-17)','Young Adults (18-29)','Adults (30-44)','Middle-aged (45-59)','Senior (60+)'];

        return collect($order)->map(fn($g) => [
            'label' => $g,
            'count' => (int) ($rows->firstWhere('age_group', $g)?->cnt ?? 0),
        ])->all();
    }

    /** Top 10 barangays by parishioner count. */
    private function demographicsBarangay(): array
    {
        $rows = Parishioner::whereNotNull('barangay')
            ->where('is_active', true)
            ->selectRaw("barangay, COUNT(*) AS cnt")
            ->groupBy('barangay')
            ->orderByDesc('cnt')
            ->limit(10)
            ->get();

        return $rows->map(fn($r) => [
            'barangay' => $r->barangay,
            'count'    => (int) $r->cnt,
        ])->all();
    }

    /** Monthly booking counts for last 15 months (for forecast). */
    private function monthlyBookings(): array
    {
        $monthExpr = $this->isPg
            ? "TO_CHAR(scheduled_date, 'YYYY-MM')"
            : "DATE_FORMAT(scheduled_date, '%Y-%m')";

        $rows = Booking::whereIn('status', ['confirmed', 'completed', 'pending'])
            ->where('scheduled_date', '>=', now()->subMonths(15))
            ->selectRaw("{$monthExpr} AS month, COUNT(*) AS cnt")
            ->groupByRaw('month')
            ->orderByRaw('month')
            ->get();

        return $rows->map(fn($r) => ['month' => $r->month, 'count' => (int)$r->cnt])->all();
    }

    /**
     * 3-month moving average forecast for the next 3 months.
     */
    private function forecast(): array
    {
        $monthly  = collect($this->monthlyBookings());
        if ($monthly->count() < 3) return [];

        $last3avg = $monthly->takeLast(3)->avg('count');

        $forecasts = [];
        for ($i = 1; $i <= 3; $i++) {
            $month       = now()->addMonths($i)->format('Y-m');
            $forecasts[] = ['month' => $month, 'forecast' => round($last3avg)];
        }

        return $forecasts;
    }

    /**
     * Plain-language insights derived from data.
     */
    private function insights(): array
    {
        $insights = [];

        // Peak month
        $monthlyBookings = collect($this->monthlyBookings());
        if ($monthlyBookings->count() > 1) {
            $peak = $monthlyBookings->sortByDesc('count')->first();
            if ($peak) {
                $insights[] = [
                    'icon'  => '📅',
                    'title' => 'Peak Booking Month',
                    'text'  => "Your busiest month in the last 15 months was <strong>{$peak['month']}</strong> with {$peak['count']} bookings.",
                ];
            }
        }

        // Top service
        $demand = collect($this->serviceDemand());
        if ($demand->isNotEmpty()) {
            $top = $demand->first();
            $insights[] = [
                'icon'  => '⭐',
                'title' => 'Most Requested Service',
                'text'  => "<strong>{$top['label']}</strong> is the most requested service with {$top['total']} bookings total.",
            ];
        }

        // Completion rate
        $total     = Booking::count();
        $completed = Booking::where('status', 'completed')->count();
        $cancelled = Booking::where('status', 'cancelled')->count();
        if ($total > 0) {
            $compRate = round(($completed / $total) * 100);
            $canxRate = round(($cancelled / $total) * 100);
            $insights[] = [
                'icon'  => '✅',
                'title' => 'Booking Completion Rate',
                'text'  => "{$compRate}% of all bookings were completed. {$canxRate}% were cancelled.",
            ];
        }

        // Revenue total
        $totalRevenue = Payment::where('status', 'paid')->where('transaction_type', 'debit')->sum('amount');
        if ($totalRevenue > 0) {
            $insights[] = [
                'icon'  => '💰',
                'title' => 'Total Revenue Collected',
                'text'  => '₱' . number_format($totalRevenue, 2) . ' in total payments received.',
            ];
        }

        // Requirements turnaround
        $turnaround = $this->requirementTurnaround();
        if ($turnaround['avg_hours'] > 0) {
            $insights[] = [
                'icon'  => '⏱️',
                'title' => 'Avg. Requirements Approval Time',
                'text'  => "Requirements are approved in an average of <strong>{$turnaround['avg_hours']} hours</strong> after submission.",
            ];
        }

        return $insights;
    }

    /** Average hours from requirement submission to approval. */
    private function requirementTurnaround(): array
    {
        try {
            if ($this->isPg) {
                $avg = BookingRequirement::where('status', 'approved')
                    ->whereNotNull('submitted_at')
                    ->whereNotNull('reviewed_at')
                    ->selectRaw("AVG(EXTRACT(EPOCH FROM (reviewed_at - submitted_at)) / 3600) AS avg_hours")
                    ->value('avg_hours');
            } else {
                $avg = BookingRequirement::where('status', 'approved')
                    ->whereNotNull('submitted_at')
                    ->whereNotNull('reviewed_at')
                    ->selectRaw("AVG(TIMESTAMPDIFF(SECOND, submitted_at, reviewed_at) / 3600) AS avg_hours")
                    ->value('avg_hours');
            }

            return ['avg_hours' => $avg ? round((float)$avg, 1) : 0];
        } catch (\Exception $e) {
            return ['avg_hours' => 0];
        }
    }
}
