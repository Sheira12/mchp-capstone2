<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryItem extends Model
{
    protected $fillable = [
        'title', 'caption', 'image_path', 'category',
        'album', 'album_cover',
        'is_featured', 'sort_order', 'created_by',
    ];

    protected $casts = [
        'is_featured'  => 'boolean',
        'album_cover'  => 'boolean',
    ];

    const CATEGORIES = [
        'general'   => 'General',
        'sacrament' => 'Sacraments',
        'community' => 'Community',
        'event'     => 'Events',
        'youth'     => 'Youth',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }

    /**
     * Get all distinct album names (non-null).
     */
    public static function albums(): \Illuminate\Support\Collection
    {
        return static::whereNotNull('album')
            ->where('album', '!=', '')
            ->select('album')
            ->distinct()
            ->orderBy('album')
            ->pluck('album');
    }

    /**
     * Get the cover photo for an album (album_cover=true, or first photo).
     */
    public static function albumCover(string $album): ?self
    {
        return static::where('album', $album)
            ->orderByDesc('album_cover')
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->first();
    }

    /**
     * Count photos in each album.
     */
    public static function albumCounts(): \Illuminate\Support\Collection
    {
        return static::whereNotNull('album')
            ->where('album', '!=', '')
            ->selectRaw('album, count(*) as total')
            ->groupBy('album')
            ->orderBy('album')
            ->get();
    }
}
