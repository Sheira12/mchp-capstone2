<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    const STATUSES = [
        'pending'   => 'Pending',
        'confirmed' => 'Confirmed',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    const TYPES = [
        // Sacramentals
        'house_blessing'    => 'House Blessing',
        'car_blessing'      => 'Car Blessing',
        'business_blessing' => 'Business Blessing',
        'sick_call'         => 'Sick Call / Anointing',
        // Seminars
        'pre_baptismal'     => 'Pre-Baptismal Seminar',
        'pre_marriage'      => 'Pre-Marriage / Pre-Cana Seminar',
        'confirmation_catechesis' => 'Confirmation Catechesis',
        // Mass
        'mass_intention'    => 'Mass Intention',
        // Sacraments
        'baptism'           => 'Baptism',
        'wedding'           => 'Wedding',
        'funeral_mass'      => 'Funeral Mass',
    ];

    protected $fillable = [
        'parishioner_id',
        'booking_type',
        'scheduled_date',
        'scheduled_time',
        'status',
        'service_fee',
        'address',
        'notes',
        'admin_notes',
        'confirmed_by',
        'confirmed_at',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'reference_number',
        'reminder_sent',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time' => 'string',
        'service_fee'    => 'decimal:2',
        'confirmed_at'   => 'datetime',
        'cancelled_at'   => 'datetime',
        'reminder_sent'  => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($booking) {
            $booking->reference_number = 'BK-' . strtoupper(uniqid());
        });
    }

    public function parishioner()
    {
        return $this->belongsTo(Parishioner::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function qrCode()
    {
        return $this->morphOne(QrCode::class, 'qr_codeable');
    }

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getTypeLabel(): string
    {
        return self::TYPES[$this->booking_type] ?? ucfirst($this->booking_type);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_date', '>=', now()->toDateString())
                     ->whereIn('status', ['pending', 'confirmed']);
    }
}
