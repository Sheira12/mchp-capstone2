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
        'card'      => 'Credit/Debit Card',
        'cash'      => 'Cash',
        'bank'      => 'Bank Transfer',
    ];

    /**
     * Transaction type from the parishioner's perspective:
     *   debit  = money owed / paid by the parishioner (service fees, bookings)
     *   credit = money returned to the parishioner (refunds, adjustments)
     */
    const TRANSACTION_TYPES = [
        'debit'  => 'Debit',
        'credit' => 'Credit',
    ];

    protected $fillable = [
        'parishioner_id',
        'booking_id',
        'certificate_id',
        'amount',
        'payment_method',
        'transaction_type',
        'status',
        'reference_number',
        'gateway_reference',
        'submitted_reference',
        'proof_path',
        'payer_contact',
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
        'verified_by',
        'verified_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'paid_at'          => 'datetime',
        'refunded_at'      => 'datetime',
        'voided_at'        => 'datetime',
        'verified_at'      => 'datetime',
        'gateway_response' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($payment) {
            // Auto-set transaction_type based on status if not explicitly provided
            if (empty($payment->transaction_type)) {
                $payment->transaction_type = in_array($payment->status, ['refunded', 'voided'])
                    ? 'credit'
                    : 'debit';
            }

            $payment->reference_number = 'PAY-' . strtoupper(uniqid());
            $payment->receipt_number   = 'RCP-' . date('Ymd') . '-' . str_pad(
                static::whereDate('created_at', today())->count() + 1,
                4,
                '0',
                STR_PAD_LEFT
            );
        });

        // When a payment is updated to refunded/voided, flip type to credit
        static::updating(function ($payment) {
            if ($payment->isDirty('status') && in_array($payment->status, ['refunded', 'voided'])) {
                $payment->transaction_type = 'credit';
            }
        });
    }

    /**
     * Human-readable transaction type label with colour hint.
     * Returns ['label' => 'Debit', 'color' => 'red'] etc.
     */
    public function getTransactionTypeBadgeAttribute(): array
    {
        return match($this->transaction_type) {
            'credit' => ['label' => 'Credit', 'color' => 'green'],
            default  => ['label' => 'Debit',  'color' => 'red'],
        };
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

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('paid_at', [$from, $to]);
    }

    public function scopeDebit($query)
    {
        return $query->where('transaction_type', 'debit');
    }

    public function scopeCredit($query)
    {
        return $query->where('transaction_type', 'credit');
    }
}
