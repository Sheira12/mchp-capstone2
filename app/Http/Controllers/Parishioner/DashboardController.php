<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\MassSchedule;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user        = auth()->user();
        $parishioner = $user->parishioner;

        if (!$parishioner) {
            return view('parishioner.dashboard', [
                'parishioner'     => null,
                'recentBookings'  => collect(),
                'recentPayments'  => collect(),
                'certificates'    => collect(),
                'stats'           => [],
                'upcomingBookings'=> collect(),
                'announcements'   => Announcement::published()->orderByDesc('published_at')->take(3)->get(),
                'massSchedules'   => MassSchedule::where('is_active', true)->whereNull('special_date')
                                        ->orderBy('day_of_week')->orderBy('time')->get(),
            ]);
        }

        $pid = $parishioner->id;

        // ── Single aggregated stats query instead of 8 separate COUNT/SUM calls ──
        $statsRaw = DB::table('bookings')
            ->select([
                DB::raw('COUNT(*) as total_bookings'),
                DB::raw("SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) as pending_bookings"),
                DB::raw("SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings"),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings"),
            ])
            ->where('parishioner_id', $pid)
            ->whereNull('deleted_at')
            ->first();

        $paymentStats = DB::table('payments')
            ->select([
                DB::raw("SUM(CASE WHEN status = 'paid'    THEN 1 ELSE 0 END) as paid_payments"),
                DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_payments"),
                DB::raw("SUM(CASE WHEN status = 'paid'    THEN amount ELSE 0 END) as total_paid_amount"),
            ])
            ->where('parishioner_id', $pid)
            ->first();

        $certCount = DB::table('certificates')
            ->where('parishioner_id', $pid)
            ->count();

        $stats = [
            'total_bookings'     => (int) ($statsRaw->total_bookings ?? 0),
            'pending_bookings'   => (int) ($statsRaw->pending_bookings ?? 0),
            'confirmed_bookings' => (int) ($statsRaw->confirmed_bookings ?? 0),
            'completed_bookings' => (int) ($statsRaw->completed_bookings ?? 0),
            'total_certificates' => $certCount,
            'paid_payments'      => (int) ($paymentStats->paid_payments ?? 0),
            'pending_payments'   => (int) ($paymentStats->pending_payments ?? 0),
            'total_paid_amount'  => (float) ($paymentStats->total_paid_amount ?? 0),
        ];

        // Recent bookings with eager-loaded payment (avoids N+1)
        $recentBookings = $parishioner->bookings()
            ->with('payment')
            ->latest()
            ->take(5)
            ->get();

        // Recent payments
        $recentPayments = $parishioner->payments()
            ->latest()
            ->take(4)
            ->get();

        // Recent certificates
        $certificates = $parishioner->certificates()
            ->latest()
            ->take(4)
            ->get();

        // Upcoming bookings — next 30 days
        $upcomingBookings = $parishioner->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereBetween('scheduled_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->orderBy('scheduled_date')
            ->take(3)
            ->get();

        // Parish announcements (rarely changes — simple query, low row count)
        $announcements = Announcement::published()
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        // Mass schedules (static — ordered by day/time)
        $massSchedules = MassSchedule::where('is_active', true)
            ->whereNull('special_date')
            ->orderBy('day_of_week')
            ->orderBy('time')
            ->get();

        return view('parishioner.dashboard', compact(
            'parishioner', 'recentBookings', 'recentPayments',
            'certificates', 'stats', 'upcomingBookings',
            'announcements', 'massSchedules'
        ));
    }
}
