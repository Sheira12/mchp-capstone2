<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequirement extends Model
{
    use HasFactory;

    // ── Legacy per-booking upload types (kept for backward compat) ───────────
    const TYPES = [
        'file'     => 'File Upload',
        'checkbox' => 'Checkbox Declaration',
        'text'     => 'Text Entry',
    ];

    // ── Eligibility gate types (v2) ──────────────────────────────────────────
    const ELIGIBILITY_TYPES = [
        'seminar'          => 'Seminar / Catechesis',
        'document'         => 'Document Submission',
        'sacrament_record' => 'Sacramental Record',
        'file'             => 'File Upload (per booking)',
        'checkbox'         => 'Checkbox (per booking)',
        'text'             => 'Text Entry (per booking)',
    ];

    const SACRAMENT_TYPES = [
        'baptism'         => 'Baptism',
        'confirmation'    => 'Confirmation',
        'marriage'        => 'Marriage',
        'first_communion' => 'First Communion',
        'death_burial'    => 'Death/Burial',
    ];

    protected $fillable = [
        'service_id',
        'service_slug',
        'name',
        'description',
        'type',
        'eligibility_type',
        'required_sacrament_type',
        'requires_proof',
        'is_required',
        'accepted_file_types',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_required'    => 'boolean',
        'is_active'      => 'boolean',
        'requires_proof' => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function bookingRequirements()
    {
        return $this->hasMany(BookingRequirement::class);
    }

    public function parishionerRequirements()
    {
        return $this->hasMany(ParishionerRequirement::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** The effective eligibility type — uses eligibility_type if set, else falls back to type. */
    public function getEffectiveEligibilityType(): string
    {
        return $this->eligibility_type ?? $this->type ?? 'document';
    }

    /** True if this is a gate-level eligibility requirement (not just per-booking upload). */
    public function isGateRequirement(): bool
    {
        return in_array($this->getEffectiveEligibilityType(), ['seminar', 'document', 'sacrament_record']);
    }

    /** Human-readable accepted file types string. */
    public function acceptedTypesLabel(): string
    {
        if (!$this->accepted_file_types) return 'Any file';
        return strtoupper(str_replace(',', ', ', $this->accepted_file_types));
    }

    /** MIME types array for HTML accept attribute. */
    public function acceptAttribute(): string
    {
        if (!$this->accepted_file_types) return '*/*';

        $map = [
            'pdf'  => 'application/pdf',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        $types = collect(explode(',', $this->accepted_file_types))
            ->map(fn($ext) => $map[strtolower(trim($ext))] ?? '.' . trim($ext))
            ->unique()
            ->values();

        return $types->implode(',');
    }
}
