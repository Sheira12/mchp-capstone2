<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parishioner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'family_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'birthdate',
        'gender',
        'civil_status',
        'address',
        'barangay',
        'city',
        'province',
        'postal_code',
        'contact_number',
        'email',
        'photo_path',
        'is_head_of_family',
        'relationship_to_head',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'birthdate'        => 'date',
        'is_head_of_family' => 'boolean',
        'is_active'        => 'boolean',
    ];

    protected $appends = ['full_name', 'age'];

    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ]);
        return implode(' ', $parts);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birthdate ? $this->birthdate->age : null;
    }

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function sacramentalRecords()
    {
        return $this->hasMany(SacramentalRecord::class);
    }

    public function baptismRecord()
    {
        return $this->hasOne(SacramentalRecord::class)->where('type', 'baptism');
    }

    public function confirmationRecord()
    {
        return $this->hasOne(SacramentalRecord::class)->where('type', 'confirmation');
    }

    public function communionRecord()
    {
        return $this->hasOne(SacramentalRecord::class)->where('type', 'first_communion');
    }

    public function marriageRecords()
    {
        return $this->hasMany(SacramentalRecord::class)->where('type', 'marriage');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function profileChanges()
    {
        return $this->hasMany(ProfileChangeLog::class);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
              ->orWhere('last_name', 'like', "%{$term}%")
              ->orWhere('middle_name', 'like', "%{$term}%")
              ->orWhere('contact_number', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%");
        });
    }

    public function scopeByBarangay($query, string $barangay)
    {
        return $query->where('barangay', $barangay);
    }
}
