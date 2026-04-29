<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    use HasFactory;

    protected $table = 'qr_codes';

    protected $fillable = [
        'qr_codeable_type',
        'qr_codeable_id',
        'token',
        'verification_url',
        'qr_image_path',
        'is_active',
        'scan_count',
        'last_scanned_at',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'last_scanned_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($qr) {
            $qr->token = bin2hex(random_bytes(16));
            $qr->verification_url = config('app.url') . '/verify/' . $qr->token;
        });
    }

    public function qrCodeable()
    {
        return $this->morphTo();
    }

    public function incrementScanCount(): void
    {
        $this->increment('scan_count');
        $this->update(['last_scanned_at' => now()]);
    }
}
