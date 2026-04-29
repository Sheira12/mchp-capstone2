<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Family extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'family_name',
        'address',
        'barangay',
        'city',
        'province',
        'contact_number',
        'notes',
    ];

    public function members()
    {
        return $this->hasMany(Parishioner::class);
    }

    public function head()
    {
        return $this->hasOne(Parishioner::class)->where('is_head_of_family', true);
    }
}
