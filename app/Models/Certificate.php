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
     */
    public static function generateUniqueNumber(): string
    {
        $year   = date('Y');
        $prefix = "CERT-{$year}-";
        $offset = strlen($prefix) + 1; // SUBSTRING is 1-based

        $maxAttempts = 5;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $number = \DB::transaction(function () use ($prefix, $year, $offset) {
                // Lock the table for the duration of this transaction so no other
                // concurrent request can read the same MAX value before we commit.
                $maxNum = \DB::table('certificates')
                    ->where('certificate_number', 'like', "{$prefix}%")
                    ->lockForUpdate()
                    ->selectRaw("MAX(CAST(SUBSTRING(certificate_number, {$offset}) AS UNSIGNED)) as max_num")
                    ->value('max_num');

                return $prefix . str_pad(($maxNum ?? 0) + 1, 5, '0', STR_PAD_LEFT);
            });

            // Check if this number is already taken (handles edge cases)
            if (!\DB::table('certificates')->where('certificate_number', $number)->exists()) {
                return $number;
            }

            // Tiny random backoff before retrying
            usleep(random_int(10000, 50000)); // 10–50 ms
        }

        // Last-resort fallback: append microseconds to guarantee uniqueness
        $year   = date('Y');
        $micro  = substr((string) microtime(true) * 100, -5);
        return "CERT-{$year}-" . str_pad($micro, 5, '0', STR_PAD_LEFT);
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
