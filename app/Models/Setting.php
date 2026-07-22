<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key, with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    /**
     * Set (upsert) a setting value and clear its cache.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting_{$key}");
    }

    /**
     * Get all social media links as an array.
     */
    public static function socials(): array
    {
        $keys = ['facebook', 'messenger', 'instagram', 'youtube', 'tiktok'];
        $result = [];
        foreach ($keys as $k) {
            $result[$k] = static::get("social_{$k}", '');
        }
        return $result;
    }
}
