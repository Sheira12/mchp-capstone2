<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seminar extends Model
{
    use HasFactory;

    const STATUSES = [
        'scheduled' => 'Scheduled',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'service_id',
        'title',
        'scheduled_at',
        'venue',
        'speaker',
        'capacity',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function registrations()
    {
        return $this->hasMany(SeminarRegistration::class);
    }

    public function attendees()
    {
        return $this->hasMany(SeminarRegistration::class)->where('status', 'attended');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function spotsRemaining(): int
    {
        $registered = $this->registrations()
            ->whereIn('status', ['registered', 'attended'])
            ->count();
        return max(0, $this->capacity - $registered);
    }

    public function isOpen(): bool
    {
        return $this->status === 'scheduled'
            && $this->scheduled_at->isFuture()
            && $this->spotsRemaining() > 0;
    }
}
