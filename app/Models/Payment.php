<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    const STATUSES = [
        'pending'   => 'Pending',
        'paid'      => 'Paid',
        'failed'    => 'Failed',
        'refunded'  => 'Refunded',
        'voided'    => 'Voided',
    ];

    const METHODS = [
        'gcash'     => 'GCash',
        'maya'      => 'Maya (PayMaya)',
        'cash'      => 'Cash',
        'bank'      => 'Bank Transfer',
    ];

    protected $fillable = [
        'parishioner_id',
        'booking_id',
        'certificate_id',
        'amount',
        'payment_method',
        'status',
        'reference_number',
        'gateway_reference',
        'gateway_response',
        'paid_at',
        'receipt_number',
        'notes',
        'refund_reason',
        'refunded_by',
        'refunded_at',
        'void_reason',
        'voided_by',
        'voided_at',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'paid_at'          => 'datetime',
        'refunded_at'      => 'datetime',
        'voided_at'        => 'datetime',
        'gateway_response' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($payment) {
            $payment->reference_number = 'PAY-' . strtoupper(uniqid());
            $payment->receipt_number   = 'RCP-' . date('Ymd') . '-' . str_pad(
                static::whereDate('created_at', today())->count() + 1,
                4,
                '0',
                STR_PAD_LEFT
            );
        });
    }

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

    public function refundedBy()
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('paid_at', [$from, $to]);
    }
}
