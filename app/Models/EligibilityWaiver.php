<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EligibilityWaiver extends Model
{
    use HasFactory;

    protected $fillable = [
        'eligibility_rule_id',
        'parishioner_id',
        'booking_id',
        'waived_by',
        'reason',
        'waived_at',
        'expires_at',
    ];

    protected $casts = [
        'waived_at'  => 'datetime',
        'expires_at' => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function rule()
    {
        return $this->belongsTo(EligibilityRule::class, 'eligibility_rule_id');
    }

    public function parishioner()
    {
        return $this->belongsTo(Parishioner::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function waivedBy()
    {
        return $this->belongsTo(User::class, 'waived_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return is_null($this->expires_at) || $this->expires_at->isFuture();
    }
}
