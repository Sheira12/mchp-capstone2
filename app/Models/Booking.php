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
        'location_type',
        'contact_person',
        'contact_phone',
        'notes',
        'ninong_name',
        'ninang_name',
        'spouse_name',
        'admin_notes',
        'confirmed_by',
        'confirmed_at',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'reference_number',
        'reminder_sent',
        'requirements_approved_at',
        'requirements_approved_by',
        'service_package_id',
        'order_id',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time' => 'string',
        'service_fee'    => 'decimal:2',
        'confirmed_at'              => 'datetime',
        'cancelled_at'              => 'datetime',
        'requirements_approved_at'  => 'datetime',
        'reminder_sent'             => 'boolean',
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

    public function package()
    {
        return $this->belongsTo(ServicePackage::class, 'service_package_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function qrCode()
    {
        return $this->morphOne(QrCode::class, 'qr_codeable');
    }

    public function bookingRequirements()
    {
        return $this->hasMany(BookingRequirement::class);
    }

    /** True when all required items for this booking are approved. */
    public function requirementsApproved(): bool
    {
        // If requirements_approved_at is set by admin, honour it directly
        if ($this->requirements_approved_at) return true;

        // Otherwise compute from booking_requirements rows
        $service = \App\Models\Service::where('slug', $this->booking_type)->first();
        if (!$service) return true; // unknown service — no gate

        $requiredIds = $service->serviceRequirements()
            ->where('is_required', true)
            ->pluck('id');

        if ($requiredIds->isEmpty()) return true; // no required items

        // All required items must exist AND be approved
        $approvedCount = $this->bookingRequirements()
            ->whereIn('service_requirement_id', $requiredIds)
            ->where('status', 'approved')
            ->count();

        return $approvedCount >= $requiredIds->count();
    }

    /** Count approved / total required requirements for progress display. */
    public function requirementsProgress(): array
    {
        $service = \App\Models\Service::where('slug', $this->booking_type)->first();
        if (!$service) return ['approved' => 0, 'total' => 0];

        $requiredIds = $service->serviceRequirements()
            ->where('is_required', true)
            ->pluck('id');

        $total    = $requiredIds->count();
        $approved = $this->bookingRequirements()
            ->whereIn('service_requirement_id', $requiredIds)
            ->where('status', 'approved')
            ->count();

        return ['approved' => $approved, 'total' => $total];
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
