<?php

namespace App\Providers;

use App\Models\Booking;
use App\Policies\BookingPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register policies
        Gate::policy(Booking::class, BookingPolicy::class);

        // Super admin bypasses all gates
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }
        });

        // Auto-remove Vite hot file on boot so production assets always load correctly.
        // The hot file is only needed when `npm run dev` is running.
        // If it exists without the dev server, CSS/JS won't load.
        $hotFile = public_path('hot');
        if (file_exists($hotFile)) {
            // Only delete if Vite dev server is NOT actually reachable
            $viteRunning = false;
            try {
                $ctx = stream_context_create(['http' => ['timeout' => 0.5]]);
                $check = @file_get_contents('http://localhost:5173', false, $ctx);
                $viteRunning = ($check !== false);
            } catch (\Throwable $e) {
                $viteRunning = false;
            }

            if (!$viteRunning) {
                @unlink($hotFile);
            }
        }
    }
}
