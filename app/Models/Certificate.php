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
            if (!empty($cert->certificate_number)) return; // Already set, skip

            $year = date('Y');

            // Use transaction + MAX to prevent duplicate numbers
            \DB::transaction(function () use ($cert, $year) {
                // Get the highest existing number this year, then add 1
                $maxNum = \DB::table('certificates')
                    ->whereYear('created_at', $year)
                    ->where('certificate_number', 'like', "CERT-{$year}-%")
                    ->lockForUpdate()
                    ->selectRaw('MAX(CAST(SUBSTRING(certificate_number, ' . (strlen("CERT-{$year}-") + 1) . ') AS UNSIGNED)) as max_num')
                    ->value('max_num');

                $next = ($maxNum ?? 0) + 1;
                $cert->certificate_number = "CERT-{$year}-" . str_pad($next, 5, '0', STR_PAD_LEFT);
            });
        });
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
