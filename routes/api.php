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
