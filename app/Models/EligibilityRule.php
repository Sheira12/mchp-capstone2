<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EligibilityRule extends Model
{
    use HasFactory;

    // ── Criterion types ──────────────────────────────────────────────────────
    const TYPE_SEMINAR_COMPLETED      = 'seminar_completed';
    const TYPE_DOCUMENT_APPROVED      = 'document_approved';
    const TYPE_PREREQUISITE_SACRAMENT = 'prerequisite_sacrament';
    const TYPE_MINIMUM_AGE            = 'minimum_age';
    const TYPE_PARISHIONER_STATUS     = 'parishioner_status';
    const TYPE_OTHER                  = 'other';

    const CRITERION_TYPES = [
        self::TYPE_SEMINAR_COMPLETED      => 'Seminar Completed',
        self::TYPE_DOCUMENT_APPROVED      => 'Document Approved',
        self::TYPE_PREREQUISITE_SACRAMENT => 'Prerequisite Sacrament',
        self::TYPE_MINIMUM_AGE            => 'Minimum Age',
        self::TYPE_PARISHIONER_STATUS     => 'Parishioner Status (Active)',
        self::TYPE_OTHER                  => 'Other (Admin-Verified)',
    ];

    // ── Applies-to values ────────────────────────────────────────────────────
    const APPLIES_APPLICANT  = 'applicant';
    const APPLIES_SPOUSE     = 'spouse';
    const APPLIES_PARENTS    = 'parents';
    const APPLIES_GODPARENTS = 'godparents';
    const APPLIES_BOTH       = 'both';

    const APPLIES_TO_LABELS = [
        self::APPLIES_APPLICANT  => 'Applicant',
        self::APPLIES_SPOUSE     => 'Spouse',
        self::APPLIES_PARENTS    => 'Parents',
        self::APPLIES_GODPARENTS => 'Godparents',
        self::APPLIES_BOTH       => 'Both (Applicant & Spouse)',
    ];

    protected $fillable = [
        'service_id',
        'criterion_type',
        'applies_to',
        'params',
        'name',
        'description',
        'is_required',
        'is_placeholder',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'params'         => 'array',
        'is_required'    => 'boolean',
        'is_placeholder' => 'boolean',
        'is_active'      => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function waivers()
    {
        return $this->hasMany(EligibilityWaiver::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getCriterionLabel(): string
    {
        return self::CRITERION_TYPES[$this->criterion_type] ?? ucfirst($this->criterion_type);
    }

    public function getAppliesToLabel(): string
    {
        return self::APPLIES_TO_LABELS[$this->applies_to] ?? ucfirst($this->applies_to);
    }

    /** Convenience: get a specific param value with a default. */
    public function param(string $key, mixed $default = null): mixed
    {
        return ($this->params ?? [])[$key] ?? $default;
    }

    /** True if a waiver exists for the given parishioner (and optionally booking). */
    public function isWaived(int $parishionerId, ?int $bookingId = null): bool
    {
        return $this->waivers()
            ->where('parishioner_id', $parishionerId)
            ->when($bookingId, fn($q) => $q->where('booking_id', $bookingId))
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }
}
