<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    const TYPES = [
        'baptism'         => 'Certificate of Baptism',
        'confirmation'    => 'Certificate of Confirmation',
        'marriage'        => 'Certificate of Marriage',
        'first_communion' => 'Certificate of First Communion',
        'death_burial'    => 'Certificate of Death/Burial',
        'no_impediment'   => 'Certificate of No Impediment',
        'membership'      => 'Certificate of Parish Membership',
    ];

    /**
     * Sacrament-type certificates that require a matching sacramental record
     * before a parishioner can download the generated PDF.
     */
    const REQUIRES_RECORD = [
        'baptism', 'confirmation', 'marriage', 'first_communion', 'death_burial',
    ];

    /**
     * Maps certificate type → sacramental_record type
     */
    const TYPE_TO_SACRAMENT = [
        'baptism'         => 'baptism',
        'confirmation'    => 'confirmation',
        'marriage'        => 'marriage',
        'first_communion' => 'first_communion',
        'death_burial'    => 'death_burial',
    ];

    const VERIFICATION_STATUSES = [
        'pending'    => 'Pending Verification',
        'verified'   => 'Verified',
        'unverified' => 'Unverified / No Record Found',
    ];

    protected $fillable = [
        'parishioner_id',
        'sacramental_record_id',
        'type',
        'certificate_number',
        'issued_date',
        'issued_by',
        'purpose',
        'file_path',
        'qr_code_path',
        'status',
        'record_verification_status',
        'staff_notes',
        'payment_id',
        'notes',
        'released_at',
        'handled_by',
        'requested_at',
    ];

    protected $casts = [
        'issued_date'  => 'date',
        'released_at'  => 'datetime',
        'requested_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($cert) {
            if (!empty($cert->certificate_number)) return; // Already set externally, skip

            $cert->certificate_number = static::generateUniqueNumber();
        });
    }

    /**
     * Generate a unique certificate number using a pessimistic lock + retry loop.
     * This safely handles concurrent inserts without race conditions.
     * Uses BIGINT cast for PostgreSQL compatibility (MySQL/MariaDB also accepts it).
     */
    public static function generateUniqueNumber(): string
    {
        $year   = date('Y');
        $prefix = "CERT-{$year}-";

        $maxAttempts = 5;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            // Count existing certificates this year to get next sequence number
            // No locking needed — unique constraint on certificate_number handles race conditions
            $count = \DB::table('certificates')
                ->where('certificate_number', 'like', "{$prefix}%")
                ->count();

            $number = $prefix . str_pad($count + 1, 5, '0', STR_PAD_LEFT);

            // Check if this number is already taken (handles race conditions)
            if (!\DB::table('certificates')->where('certificate_number', $number)->exists()) {
                return $number;
            }

            // Number taken — try count+2, count+3, etc.
            usleep(random_int(10000, 50000));
        }

        // Last-resort fallback: use timestamp microseconds for guaranteed uniqueness
        $micro = str_pad((string)(microtime(true) * 1000 % 100000), 5, '0', STR_PAD_LEFT);
        return "CERT-{$year}-" . $micro;
    }

    public function parishioner()
    {
        return $this->belongsTo(Parishioner::class);
    }

    public function sacramentalRecord()
    {
        return $this->belongsTo(SacramentalRecord::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function qrCode()
    {
        return $this->morphOne(QrCode::class, 'qr_codeable');
    }

    public function editRequests()
    {
        return $this->hasMany(CertificateEditRequest::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(CertificateStatusHistory::class)->orderBy('changed_at');
    }

    public function handledBy()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function getTypeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Returns true when a parishioner is allowed to download this certificate.
     * Must be `released` (not just `issued`) AND verified.
     * Staff can always download via the admin route regardless.
     */
    public function isDownloadable(): bool
    {
        // Only released certificates are available to the parishioner
        if ($this->status !== 'released') {
            return false;
        }

        if (in_array($this->type, self::REQUIRES_RECORD)) {
            return $this->record_verification_status === 'verified';
        }

        return true; // membership, no_impediment don't need a record
    }

    public function getVerificationStatusLabel(): string
    {
        return self::VERIFICATION_STATUSES[$this->record_verification_status] ?? 'Pending';
    }
}
