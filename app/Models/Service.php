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
}
