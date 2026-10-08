<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequirement extends Model
{
    use HasFactory;

    const TYPES = [
        'file'     => 'File Upload',
        'checkbox' => 'Checkbox Declaration',
        'text'     => 'Text Entry',
    ];

    protected $fillable = [
        'service_id',
        'name',
        'description',
        'type',
        'is_required',
        'accepted_file_types',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active'   => 'boolean',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function bookingRequirements()
    {
        return $this->hasMany(BookingRequirement::class);
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
