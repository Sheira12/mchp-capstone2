<?php

use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public verification API (for mobile QR scanning)
Route::get('/verify/{token}', [VerificationController::class, 'apiVerify'])->name('api.verify');

// Public: booked dates for a given service type (used by parishioner booking form)
Route::get('/booked-dates', function (\Illuminate\Http\Request $request) {
    $type  = $request->get('type');
    $month = $request->get('month', now()->format('Y-m'));

    // Validate month format
    if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
        $month = now()->format('Y-m');
    }

    $year  = (int) substr($month, 0, 4);
    $mo    = (int) substr($month, 5, 2);

    $query = \App\Models\Booking::whereIn('status', ['pending', 'confirmed'])
        ->whereYear('scheduled_date',  $year)
        ->whereMonth('scheduled_date', $mo);

    if ($type) {
        // Service-specific: count bookings of this type per day
        $counts = (clone $query)
            ->where('booking_type', $type)
            ->selectRaw('scheduled_date, count(*) as n')
            ->groupBy('scheduled_date')
            ->get();

        // Fully booked = 3+ of same type on same day
        $bookedDates = $counts->where('n', '>=', 3)
            ->pluck('scheduled_date')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->values();

        $busyDates = $counts->where('n', '>=', 1)->where('n', '<', 3)
            ->pluck('scheduled_date')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->values();
    } else {
        // No service selected: show ALL bookings across all types
        $counts = $query
            ->selectRaw('scheduled_date, count(*) as n')
            ->groupBy('scheduled_date')
            ->get();

        // Any day with 5+ total bookings is "fully booked"
        $bookedDates = $counts->where('n', '>=', 5)
            ->pluck('scheduled_date')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->values();

        $busyDates = $counts->where('n', '>=', 1)->where('n', '<', 5)
            ->pluck('scheduled_date')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->values();
    }

    return response()->json([
        'booked' => $bookedDates,
        'busy'   => $busyDates,
        'month'  => $month,
        'type'   => $type,
    ]);
});

// Authenticated API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn() => request()->user());

    // Parishioner search (for autocomplete)
    Route::get('/parishioners/search', [\App\Http\Controllers\Admin\ParishionerController::class, 'search']);

    // Dashboard stats
    Route::get('/dashboard/stats', [\App\Http\Controllers\Admin\DashboardController::class, 'stats']);

    // Booking calendar events
    Route::get('/bookings/calendar-events', function () {
        $bookings = \App\Models\Booking::whereIn('status', ['pending', 'confirmed'])
            ->where('scheduled_date', '>=', now()->subMonth())
            ->with('parishioner')
            ->get()
            ->map(fn($b) => [
                'id'    => $b->id,
                'title' => $b->getTypeLabel(),
                'start' => $b->scheduled_date->format('Y-m-d'),
                'color' => $b->status === 'confirmed' ? '#16a34a' : '#d97706',
            ]);
        return response()->json($bookings);
    });
});
