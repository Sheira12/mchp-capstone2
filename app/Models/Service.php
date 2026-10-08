<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'requirements',
        'fee',
        'duration_minutes',
        'is_bookable',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'requirements'     => 'array',
        'fee'              => 'decimal:2',
        'is_bookable'      => 'boolean',
        'is_active'        => 'boolean',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'booking_type', 'slug');
    }

    public function packages()
    {
        return $this->hasMany(ServicePackage::class)->where('is_active', true)->orderBy('sort_order');
    }

    public function allPackages()
    {
        return $this->hasMany(ServicePackage::class)->orderBy('sort_order');
    }

    public function serviceRequirements()
    {
        return $this->hasMany(ServiceRequirement::class)->where('is_active', true)->orderBy('sort_order');
    }

    public function allServiceRequirements()
    {
        return $this->hasMany(ServiceRequirement::class)->orderBy('sort_order');
    }

    /** True if this service has at least one active required requirement. */
    public function requiresPreApproval(): bool
    {
        return $this->serviceRequirements()->where('is_required', true)->exists();
    }
}
