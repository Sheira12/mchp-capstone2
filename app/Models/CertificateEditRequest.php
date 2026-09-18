<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateEditRequest extends Model
{
    protected $fillable = [
        'certificate_id',
        'submitted_by',
        'requested_changes',
        'parishioner_note',
        'status',
        'reviewed_by',
        'reviewed_at',
        'staff_response',
    ];

    protected $casts = [
        'requested_changes' => 'array',
        'reviewed_at'       => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Human-readable label for a changed field key.
     */
    public static function fieldLabel(string $key): string
    {
        return [
            'date_administered' => 'Date Administered',
            'celebrant'         => 'Officiating Priest',
            'venue'             => 'Venue / Church',
            'godparents'        => 'Godparents (Ninong/Ninang)',
            'sponsors'          => 'Sponsors',
            'witnesses'         => 'Witnesses',
            'register_number'   => 'Register Number',
            'page_number'       => 'Page Number',
            'line_number'       => 'Line Number',
            'notes'             => 'Notes',
            'parents_names'     => "Parents' Names",
            'spouse_name'       => 'Spouse Name',
        ][$key] ?? ucfirst(str_replace('_', ' ', $key));
    }
}
