<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ParishionerRequirement extends Model
{
    use HasFactory;

    const STATUSES = [
        'pending'  => 'Pending Verification',
        'verified' => 'Verified',
        'rejected' => 'Rejected',
    ];

    const STATUS_COLORS = [
        'pending'  => 'yellow',
        'verified' => 'green',
        'rejected' => 'red',
    ];

    const SOURCES = [
        'self_declared'  => 'Self-Declared',
        'staff_recorded' => 'Staff Recorded',
        'system_matched' => 'System Matched',
    ];

    protected $fillable = [
        'parishioner_id',
        'service_requirement_id',
        'status',
        'date_completed',
        'venue_or_organizer',
        'notes',
        'proof_path',
        'source',
        'verified_by',
        'verified_at',
        'rejection_reason',
    ];

    protected $casts = [
        'date_completed' => 'date',
        'verified_at'    => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function parishioner()
    {
        return $this->belongsTo(Parishioner::class);
    }

    public function requirement()
    {
        return $this->belongsTo(ServiceRequirement::class, 'service_requirement_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function getSourceLabel(): string
    {
        return self::SOURCES[$this->source] ?? ucfirst($this->source);
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /** Can the parishioner still edit this? Only pending or rejected declarations. */
    public function isEditable(): bool
    {
        return in_array($this->status, ['pending', 'rejected'])
            && $this->source === 'self_declared';
    }

    /** Public URL for the uploaded proof file. */
    public function proofUrl(): ?string
    {
        if (!$this->proof_path) return null;
        try {
            return Storage::disk('supabase')->url($this->proof_path);
        } catch (\Exception $e) {
            return null;
        }
    }

    /** File extension of the proof. */
    public function proofExtension(): string
    {
        return strtolower(pathinfo($this->proof_path ?? '', PATHINFO_EXTENSION));
    }

    /** True if proof is an image. */
    public function proofIsImage(): bool
    {
        return in_array($this->proofExtension(), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }
}
