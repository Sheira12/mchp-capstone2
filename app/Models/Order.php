<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    const STATUSES = [
        'pending'   => 'Pending Payment',
        'paid'      => 'Paid',
        'cancelled' => 'Cancelled',
        'expired'   => 'Expired',
    ];

    protected $fillable = [
        'order_number',
        'parishioner_id',
        'booking_id',
        'certificate_id',
        'service_id',
        'service_package_id',
        'line_items',
        'subtotal',
        'fees',
        'total',
        'status',
        'expires_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'line_items' => 'array',
        'subtotal'   => 'decimal:2',
        'fees'       => 'decimal:2',
        'total'      => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
            if (empty($order->expires_at)) {
                $order->expires_at = now()->addHours(24);
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $year   = date('Y');
        $prefix = "OP-{$year}-";
        $count  = static::where('order_number', 'like', "{$prefix}%")->count();
        return $prefix . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
    }

    // ── Relations ────────────────────────────────────────────────────────────

    public function parishioner()
    {
        return $this->belongsTo(Parishioner::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function package()
    {
        return $this->belongsTo(ServicePackage::class, 'service_package_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast() && $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /** Line items as a safe array, always with label + amount keys. */
    public function lineItemsList(): array
    {
        return collect($this->line_items ?? [])->map(fn($item) => [
            'label'  => $item['label'] ?? 'Service',
            'amount' => (float) ($item['amount'] ?? 0),
        ])->all();
    }

    /** Total formatted. */
    public function totalFormatted(): string
    {
        return '₱' . number_format($this->total, 2);
    }
}
