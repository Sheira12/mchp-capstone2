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
        'discount',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'inclusions' => 'array',
        'price'      => 'decimal:2',
        'discount'   => 'decimal:2',
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

    /**
     * Inclusions as a safe array.
     * Supports both old format (plain strings) and new format (objects with name/price/qty).
     * Always returns an array of arrays with at minimum a 'name' key.
     */
    public function inclusionsList(): array
    {
        $raw = $this->inclusions ?? [];
        return collect($raw)->map(function ($item) {
            if (is_string($item)) {
                // Legacy plain-string format → wrap as object with zero price
                return ['name' => $item, 'price' => 0, 'qty' => 1, 'subtotal' => 0];
            }
            $qty      = max(1, (int) ($item['qty']   ?? 1));
            $price    = max(0, (float) ($item['price'] ?? 0));
            $subtotal = round($qty * $price, 2);
            return [
                'name'     => $item['name'] ?? '',
                'price'    => $price,
                'qty'      => $qty,
                'subtotal' => $subtotal,
            ];
        })->filter(fn($i) => !empty($i['name']))->values()->all();
    }

    /**
     * Calculate the total from inclusions (sum of subtotals).
     * Used when auto-pricing from inclusions.
     */
    public function priceFromInclusions(): float
    {
        return round(
            collect($this->inclusionsList())->sum('subtotal'),
            2
        );
    }

    /**
     * Return just the inclusion names as a flat string array.
     * Used for legacy display contexts (e.g., booking form).
     */
    public function inclusionNames(): array
    {
        return collect($this->inclusionsList())->pluck('name')->all();
    }
}
