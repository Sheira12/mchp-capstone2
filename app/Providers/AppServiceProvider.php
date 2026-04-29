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
    }
}
