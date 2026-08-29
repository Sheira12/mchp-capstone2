<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'parishioner_id',
        'is_active',
        'last_login_at',
        'two_factor_code',
        'two_factor_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'      => 'datetime',
        'last_login_at'          => 'datetime',
        'two_factor_expires_at'  => 'datetime',
        'password'               => 'hashed',
        'is_active'              => 'boolean',
    ];

    /**
     * Generate a cryptographically secure 6-digit OTP.
     * Stores a bcrypt hash in the DB — the plaintext is returned ONCE for emailing only.
     * Never store or log the plaintext after this call.
     */
    public function generateTwoFactorCode(): string
    {
        $plainCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->update([
            'two_factor_code'       => Hash::make($plainCode), // store hash, not plaintext
            'two_factor_expires_at' => now()->addMinutes(5),   // 5-minute expiry
        ]);

        return $plainCode; // caller must use this ONLY to send the email — never store it
    }

    /**
     * Clear the OTP after successful verification or on failure.
     */
    public function clearTwoFactorCode(): void
    {
        $this->update([
            'two_factor_code'       => null,
            'two_factor_expires_at' => null,
        ]);
    }

    /**
     * Verify the submitted OTP against the stored hash, and check expiry.
     */
    public function validateTwoFactorCode(string $submittedCode): bool
    {
        if (!$this->two_factor_code || !$this->two_factor_expires_at) {
            return false;
        }

        if ($this->two_factor_expires_at->isPast()) {
            return false;
        }

        return Hash::check($submittedCode, $this->two_factor_code);
    }

    public function parishioner()
    {
        return $this->belongsTo(Parishioner::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
