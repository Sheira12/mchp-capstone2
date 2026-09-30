<?php

use Illuminate\Support\Facades\Storage;

if (!function_exists('media_url')) {
    /**
     * Generate a public URL for a file stored on the media disk (supabase).
     *
     * Usage in Blade:  {{ media_url($item->image_path) }}
     * Usage in PHP:    media_url($certificate->file_path)
     *
     * Falls back gracefully: if $path is empty/null, returns ''.
     * If $path is a base64 data URI (legacy profile photos), returns it as-is.
     *
     * @param string|null $path  Relative path stored in DB (e.g. "gallery/abc.jpg")
     */
    function media_url(?string $path): string
    {
        if (empty($path)) {
            return '';
        }

        // Guard against PHP false/0 stored as the string "0" when an upload silently fails
        if ($path === '0' || $path === 'false') {
            return '';
        }

        // Legacy profile photos stored as base64 data URIs — serve directly
        if (str_starts_with($path, 'data:')) {
            return $path;
        }

        try {
            return Storage::disk('supabase')->url($path);
        } catch (\Exception $e) {
            \Log::warning('media_url() failed for path: ' . $path . ' — ' . $e->getMessage());
            return '';
        }
    }
}

if (!function_exists('media_exists')) {
    /**
     * Check whether a file exists on the media disk (supabase).
     * Use this instead of Storage::disk('public')->exists() in controllers.
     */
    function media_exists(?string $path): bool
    {
        if (empty($path) || str_starts_with($path, 'data:')) {
            return false;
        }
        try {
            return Storage::disk('supabase')->exists($path);
        } catch (\Exception $e) {
            return false;
        }
    }
}
