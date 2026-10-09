<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SeminarRegistration extends Model
{
    use HasFactory;

    const STATUSES = [
        'registered' => 'Registered',
        'attended'   => 'Attended',
        'absent'     => 'Absent',
        'cancelled'  => 'Cancelled',
    ];

    protected $fillable = [
        'seminar_id',
        'parishioner_id',
        'status',
        'registered_at',
        'attended_at',
        'qr_token',
        'cert_path',
        'notes',
        'marked_by',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'attended_at'   => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function (SeminarRegistration $reg) {
            if (empty($reg->qr_token)) {
                $reg->qr_token = Str::upper(Str::random(12));
            }
            if (empty($reg->registered_at)) {
                $reg->registered_at = now();
            }
        });
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function seminar()
    {
        return $this->belongsTo(Seminar::class);
    }

    public function parishioner()
    {
        return $this->belongsTo(Parishioner::class);
    }

    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function hasAttended(): bool
    {
        return $this->status === 'attended';
    }
}
