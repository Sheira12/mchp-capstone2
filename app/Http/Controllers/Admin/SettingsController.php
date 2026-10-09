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
        $settings = [
            'parish_name'            => Setting::get('parish_name',    config('parish.name')),
            'parish_address'         => Setting::get('parish_address', config('parish.address')),
            'parish_phone'           => Setting::get('parish_phone',   config('parish.phone')),
            'parish_email'           => Setting::get('parish_email',   config('parish.email')),
            'parish_priest'          => Setting::get('parish_priest',  config('parish.priest')),
            'parish_secretary'       => Setting::get('parish_secretary', ''),
            'parish_finance_officer' => Setting::get('parish_finance_officer', ''),
            'office_hours'           => Setting::get('office_hours', ''),
        ];

        $socials = Setting::socials();

        // Media paths stored in settings (Supabase URLs)
        $media = [
            'parish_logo'   => Setting::get('media_parish_logo', ''),
            'church_banner' => Setting::get('media_church_banner', ''),
        ];

        return view('admin.settings.index', compact('settings', 'socials', 'media'));
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
            'office_hours'           => ['nullable', 'string', 'max:255'],
        ]);

        $keys = [
            'parish_name', 'parish_address', 'parish_phone',
            'parish_email', 'parish_priest',
            'parish_secretary', 'parish_finance_officer',
            'office_hours',
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

    /**
     * Upload parish logo or church banner to Supabase.
     * Stores the Supabase path in Settings, not local disk.
     */
    public function updateMedia(Request $request)
    {
        $request->validate([
            'parish_logo'   => ['nullable', 'image', 'max:3072'],  // 3MB
            'church_banner' => ['nullable', 'image', 'max:5120'],  // 5MB
        ]);

        $uploaded = [];

        foreach (['parish_logo', 'church_banner'] as $key) {
            if ($request->hasFile($key)) {
                try {
                    // Delete old image if exists
                    $old = Setting::get("media_{$key}", '');
                    if ($old) {
                        try {
                            \Illuminate\Support\Facades\Storage::disk('supabase')->delete($old);
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::warning("Failed to delete old {$key}: " . $e->getMessage());
                        }
                    }

                    $file = $request->file($key);
                    $ext  = $file->getClientOriginalExtension() ?: 'jpg';
                    $path = $file->storeAs(
                        'settings',
                        $key . '-' . now()->format('YmdHis') . '.' . $ext,
                        'supabase'
                    );

                    if ($path !== false) {
                        Setting::set("media_{$key}", $path);
                        $uploaded[] = str_replace('_', ' ', ucfirst($key));
                    } else {
                        return back()->with('warning', ucfirst(str_replace('_', ' ', $key)) . ' upload failed. Please try again.');
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Media upload failed for {$key}: " . $e->getMessage());
                    return back()->with('warning', 'Upload failed: ' . $e->getMessage());
                }
            }
        }

        $msg = $uploaded ? implode(' & ', $uploaded) . ' updated successfully.' : 'No files uploaded.';
        return back()->with('success', $msg);
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
