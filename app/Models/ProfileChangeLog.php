<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileChangeLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'parishioner_id',
        'changed_by',
        'field_name',
        'old_value',
        'new_value',
        'reason',
    ];

    public function parishioner()
    {
        return $this->belongsTo(Parishioner::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
