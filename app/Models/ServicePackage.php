<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'name',
        'description',
        'inclusions',
        'price',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'inclusions' => 'array',
        'price'      => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /** Formatted price string. */
    public function priceFormatted(): string
    {
        return '₱' . number_format($this->price, 2);
    }

    /** Inclusions as a safe array (never null). */
    public function inclusionsList(): array
    {
        return $this->inclusions ?? [];
    }
}
