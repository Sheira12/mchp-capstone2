<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BookingRequirement extends Model
{
    use HasFactory;

    const STATUSES = [
        'pending'          => 'Pending Review',
        'approved'         => 'Approved',
        'needs_revision'   => 'Needs Revision',
    ];

    const STATUS_COLORS = [
        'pending'        => 'yellow',
        'approved'       => 'green',
        'needs_revision' => 'red',
    ];

    protected $fillable = [
        'booking_id',
        'service_requirement_id',
        'status',
        'file_path',
        'text_value',
        'parishioner_note',
        'admin_remark',
        'reviewed_by',
        'reviewed_at',
        'submitted_at',
    ];

    protected $casts = [
        'reviewed_at'  => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function requirement()
    {
        return $this->belongsTo(ServiceRequirement::class, 'service_requirement_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    /** Whether the parishioner has submitted something. */
    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    /** Public URL for the uploaded file via Supabase. */
    public function fileUrl(): ?string
    {
        if (!$this->file_path) return null;
        try {
            return Storage::disk('supabase')->url($this->file_path);
        } catch (\Exception $e) {
            return null;
        }
    }

    /** Filename without path. */
    public function fileName(): string
    {
        return basename($this->file_path ?? '');
    }

    /** Extension of the uploaded file. */
    public function fileExtension(): string
    {
        return strtolower(pathinfo($this->file_path ?? '', PATHINFO_EXTENSION));
    }

    /** Is this an image file? */
    public function isImage(): bool
    {
        return in_array($this->fileExtension(), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }
}
