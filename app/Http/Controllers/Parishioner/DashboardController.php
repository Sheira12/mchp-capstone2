<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\MassSchedule;

class DashboardController extends Controller
{
    public function index()
    {
        $user        = auth()->user();
        $parishioner = $user->parishioner;

        $recentBookings = $parishioner
            ? $parishioner->bookings()->with('payment')->latest()->take(5)->get()
            : collect();

        $recentPayments = $parishioner
            ? $parishioner->payments()->latest()->take(4)->get()
            : collect();

        $certificates = $parishioner
            ? $parishioner->certificates()->latest()->take(4)->get()
            : collect();

        // Stats
        $stats = [];
        if ($parishioner) {
            $stats = [
                'total_bookings'    => $parishioner->bookings()->count(),
                'pending_bookings'  => $parishioner->bookings()->where('status', 'pending')->count(),
                'confirmed_bookings'=> $parishioner->bookings()->where('status', 'confirmed')->count(),
                'completed_bookings'=> $parishioner->bookings()->where('status', 'completed')->count(),
                'total_certificates'=> $parishioner->certificates()->count(),
                'paid_payments'     => $parishioner->payments()->where('status', 'paid')->count(),
                'pending_payments'  => $parishioner->payments()->where('status', 'pending')->count(),
                'total_paid_amount' => $parishioner->payments()->where('status', 'paid')->sum('amount'),
            ];
        }

        // Upcoming bookings (next 30 days)
        $upcomingBookings = $parishioner
            ? $parishioner->bookings()
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('scheduled_date', '>=', now()->toDateString())
                ->where('scheduled_date', '<=', now()->addDays(30)->toDateString())
                ->orderBy('scheduled_date')
                ->take(3)
                ->get()
            : collect();

        // Parish announcements
        $announcements = Announcement::published()
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        // Mass schedules
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
