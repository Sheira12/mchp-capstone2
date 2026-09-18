<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateStatusHistory extends Model
{
    public $timestamps = false; // uses changed_at instead

    protected $fillable = [
        'certificate_id',
        'from_status',
        'to_status',
        'changed_by',
        'ip_address',
        'note',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // ── Static helper ──────────────────────────────────────────────────────

    public static function log(
        Certificate $certificate,
        ?string $fromStatus,
        string $toStatus,
        ?string $note = null,
        ?string $ip = null
    ): self {
        return self::create([
            'certificate_id' => $certificate->id,
            'from_status'    => $fromStatus,
            'to_status'      => $toStatus,
            'changed_by'     => auth()->id(),
            'ip_address'     => $ip ?? request()->ip(),
            'note'           => $note,
            'changed_at'     => now(),
        ]);
    }
}
