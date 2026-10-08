<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'image_path',
        'is_published',
        'status',
        'published_at',
        'scheduled_at',
        'expires_at',
        'created_by',
        'category',
        'is_pinned',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_pinned'    => 'boolean',
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    const STATUSES = [
        'draft'     => 'Draft',
        'published' => 'Published',
        'scheduled' => 'Scheduled',
    ];

    const CATEGORIES = [
        'general'   => 'General',
        'mass'      => 'Mass Schedule',
        'event'     => 'Event',
        'sacrament' => 'Sacrament',
        'notice'    => 'Notice',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->where(function ($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>', now());
                     });
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }
}
