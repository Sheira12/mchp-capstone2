<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    public function index()
    {
        // Read all parish fields from the Settings DB table first,
        // falling back to config() (which reads .env) if not set in DB.
        // This way the admin can update values without writing to .env.
        $settings = [
            'parish_name'            => Setting::get('parish_name',    config('parish.name')),
            'parish_address'         => Setting::get('parish_address', config('parish.address')),
            'parish_phone'           => Setting::get('parish_phone',   config('parish.phone')),
            'parish_email'           => Setting::get('parish_email',   config('parish.email')),
            'parish_priest'          => Setting::get('parish_priest',  config('parish.priest')),
            'parish_secretary'       => Setting::get('parish_secretary', ''),
            'parish_finance_officer' => Setting::get('parish_finance_officer', ''),
        ];

        $socials = Setting::socials();

        return view('admin.settings.index', compact('settings', 'socials'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'parish_name'            => ['required', 'string', 'max:255'],
            'parish_address'         => ['required', 'string', 'max:255'],
            'parish_phone'           => ['nullable', 'string', 'max:50'],
            'parish_email'           => ['nullable', 'email'],
            'parish_priest'          => ['nullable', 'string', 'max:255'],
            'parish_secretary'       => ['nullable', 'string', 'max:255'],
            'parish_finance_officer' => ['nullable', 'string', 'max:255'],
        ]);

        // Store ALL fields in the settings DB table.
        // This replaces the old approach of writing to .env (which fails on
        // production because .env is owned by root and Apache runs as www-data).
        // config('parish.*') reads from .env; the admin panel reads from DB.
        // The two sources are kept in sync here.
        $keys = [
            'parish_name', 'parish_address', 'parish_phone',
            'parish_email', 'parish_priest',
            'parish_secretary', 'parish_finance_officer',
        ];

        foreach ($keys as $key) {
            Setting::set($key, $validated[$key] ?? '');
        }

        // Re-cache the config so in-memory values stay consistent.
        // We do NOT call config:clear because that would destroy the production
        // route/view caches and force a full re-bootstrap on the next request.
        try {
            Artisan::call('config:cache');
        } catch (\Exception $e) {
            // Config cache may fail if .env is read-only on this platform.
            // Settings are already saved to DB, so this is non-fatal.
        }

        return back()->with('success', 'Parish settings updated.');
    }

    public function updateSocials(Request $request)
    {
        $validated = $request->validate([
            'social_facebook'  => ['nullable', 'url', 'max:500'],
            'social_messenger' => ['nullable', 'url', 'max:500'],
            'social_instagram' => ['nullable', 'url', 'max:500'],
            'social_youtube'   => ['nullable', 'url', 'max:500'],
            'social_tiktok'    => ['nullable', 'url', 'max:500'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value ?? '');
        }

        return back()->with('success', 'Social media links updated.');
    }

    public function clearCache(Request $request)
    {
        $type = $request->get('type', 'config');
        match ($type) {
            'view'   => Artisan::call('view:clear'),
            'route'  => Artisan::call('route:clear'),
            default  => Artisan::call('config:clear'),
        };
        return back()->with('success', ucfirst($type) . ' cache cleared.');
    }
}
