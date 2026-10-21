<?php

namespace App\Providers;

use App\Models\Booking;
use App\Policies\BookingPolicy;
use App\Policies\RequirementsPolicy;
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
        Gate::policy(\App\Models\BookingRequirement::class, RequirementsPolicy::class);

        // Also define a named gate for use in Blade @can('requirements.review')
        // and controller Gate::authorize('requirements.review') calls.
        // This reads the same config as the policy so there is one source of truth.
        Gate::define('requirements.review', function ($user) {
            $allowedRoles = config('permissions.requirements.review.allowed_roles', ['parish_secretary']);
            $saOverride   = config('permissions.requirements.review.super_admin_override', false);

            if ($saOverride && $user->hasRole('super_admin')) {
                return true;
            }
            return $user->hasRole($allowedRoles);
        });

        Gate::define('requirements.view', function ($user) {
            $allowedRoles = config(
                'permissions.requirements.view.allowed_roles',
                ['parish_secretary', 'super_admin', 'finance_officer']
            );
            return $user->hasRole($allowedRoles);
        });

        // Super admin bypasses all gates EXCEPT 'requirements.review' when
        // super_admin_override is false in config/permissions.php.
        Gate::before(function ($user, $ability) {
            if (!$user->hasRole('super_admin')) {
                return null; // not super_admin — fall through to normal evaluation
            }

            // For the requirements review ability, check the override flag.
            // The named gate 'requirements.review' is what Blade and controllers use.
            if ($ability === 'requirements.review') {
                $saOverride = config('permissions.requirements.review.super_admin_override', false);
                if (!$saOverride) {
                    return null; // super_admin does NOT get auto-grant — fall to Gate::define
                }
            }

            return true; // super_admin bypasses all other gates as before
        });

        // ── Override config('parish.*') from the DB settings table ──────────
        // This ensures admin-editable parish information (name, address, etc.)
        // is used everywhere that calls config('parish.*'), regardless of what
        // is frozen in the config cache / .env file.
        // Wrapped in try/catch so a missing DB table during first-install or
        // migration doesn't prevent the app from booting.
        try {
            $dbName    = \App\Models\Setting::get('parish_name');
            $dbAddress = \App\Models\Setting::get('parish_address');
            $dbPhone   = \App\Models\Setting::get('parish_phone');
            $dbEmail   = \App\Models\Setting::get('parish_email');
            $dbPriest  = \App\Models\Setting::get('parish_priest');

            if ($dbName)    config(['parish.name'    => $dbName]);
            if ($dbAddress) config(['parish.address' => $dbAddress]);
            if ($dbPhone)   config(['parish.phone'   => $dbPhone]);
            if ($dbEmail)   config(['parish.email'   => $dbEmail]);
            if ($dbPriest)  config(['parish.priest'  => $dbPriest]);
        } catch (\Throwable $e) {
            // Non-fatal — fall back to .env/config values
        }

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
