<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Livestream extends Model
{
    protected $fillable = [
        'title', 'description', 'youtube_url', 'youtube_id',
        'type', 'scheduled_at', 'is_active', 'is_featured', 'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'is_active'    => 'boolean',
        'is_featured'  => 'boolean',
    ];

    const TYPES = [
        'live'     => 'Live Now',
        'upcoming' => 'Upcoming',
        'recorded' => 'Recorded',
    ];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            // Extract YouTube ID from URL automatically
            if ($model->youtube_url && !$model->youtube_id) {
                $model->youtube_id = static::extractYoutubeId($model->youtube_url);
            }
        });
    }

    public static function extractYoutubeId(string $url): ?string
    {
        preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m);
        return $m[1] ?? (strlen(trim($url)) === 11 ? trim($url) : null);
    }

    public function getEmbedUrlAttribute(): string
    {
        $id = $this->youtube_id ?? static::extractYoutubeId($this->youtube_url);
        return "https://www.youtube.com/embed/{$id}?rel=0&modestbranding=1";
    }

    public function getThumbnailAttribute(): string
    {
        $id = $this->youtube_id ?? static::extractYoutubeId($this->youtube_url);
        return "https://img.youtube.com/vi/{$id}/hqdefault.jpg";
    }

    public function getTypeLabel(): string { return self::TYPES[$this->type] ?? $this->type; }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeFeatured($q) { return $q->where('is_featured', true); }
}
