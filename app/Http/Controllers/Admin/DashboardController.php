<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Certificate;
use App\Models\Parishioner;
use App\Models\Payment;
use App\Models\SacramentalRecord;
use Illuminate\Http\Request;
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

        // Parishioner counts
        $totalParishioners  = Parishioner::count();
        $newParishioners    = Parishioner::where('created_at', '>=', $start)->count();

        // Sacrament breakdown
        $sacramentCounts = SacramentalRecord::select('type', DB::raw('count(*) as total'))
            ->where('created_at', '>=', $start)
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        // Bookings
        $pendingBookings    = Booking::pending()->count();
        $confirmedBookings  = Booking::confirmed()->count();
        $completedBookings  = Booking::where('status', 'completed')
            ->where('updated_at', '>=', $start)->count();

        // Revenue
        $totalRevenue = Payment::paid()
            ->where('paid_at', '>=', $start)
            ->sum('amount');

        $revenueByMethod = Payment::paid()
            ->where('paid_at', '>=', $start)
            ->select('payment_method', DB::raw('sum(amount) as total'))
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->toArray();

        // Monthly sacrament trend (last 12 months)
        $monthlyTrend = SacramentalRecord::select(
            DB::raw('YEAR(date_administered) as year'),
            DB::raw('MONTH(date_administered) as month'),
            DB::raw('count(*) as total')
        )
            ->where('date_administered', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Monthly revenue trend
        $revenueTrend = Payment::paid()
            ->select(
                DB::raw('YEAR(paid_at) as year'),
                DB::raw('MONTH(paid_at) as month'),
                DB::raw('sum(amount) as total')
            )
            ->where('paid_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Pending certificates
        $pendingCertificates = Certificate::where('status', 'draft')->count();

        // Recent bookings
        $recentBookings = Booking::with('parishioner')
            ->latest()
            ->take(5)
            ->get();

        return compact(
            'totalParishioners',
            'newParishioners',
            'sacramentCounts',
            'pendingBookings',
            'confirmedBookings',
            'completedBookings',
            'totalRevenue',
            'revenueByMethod',
            'monthlyTrend',
            'revenueTrend',
            'pendingCertificates',
            'recentBookings'
        );
    }

    public function exportReport(Request $request)
    {
        $validated = $request->validate([
            'type'       => ['required', 'in:pdf,excel'],
            'period'     => ['required', 'in:week,month,year,custom'],
            'date_from'  => ['required_if:period,custom', 'date'],
            'date_to'    => ['required_if:period,custom', 'date', 'after_or_equal:date_from'],
            'category'   => ['nullable', 'string'],
        ]);

        // Export logic handled by service
        $reportService = app(\App\Services\ReportService::class);
        return $reportService->generate($validated);
    }
}
