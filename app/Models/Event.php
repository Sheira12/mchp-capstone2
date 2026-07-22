<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Event extends Model
{
    protected $fillable = [
        'title', 'description', 'location',
        'event_start', 'event_end', 'category',
        'image_path', 'status', 'is_featured', 'created_by',
    ];

    protected $casts = [
        'event_start' => 'datetime',
        'event_end'   => 'datetime',
        'is_featured' => 'boolean',
    ];

    const CATEGORIES = [
        'general'   => 'General',
        'sacrament' => 'Sacrament',
        'community' => 'Community',
        'youth'     => 'Youth',
        'outreach'  => 'Outreach',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('event_start', '>=', now())->orderBy('event_start');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function getIsOngoingAttribute(): bool
    {
        return $this->event_start <= now() && ($this->event_end === null || $this->event_end >= now());
    }
}
