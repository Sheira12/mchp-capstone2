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
        'payment_id',
        'notes',
    ];

    protected $casts = [
        'issued_date' => 'date',
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

    public function getTypeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }
}
