<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'to_email',
        'to_name',
        'subject',
        'template',
        'status',
        'sent_at',
        'error_message',
        'related_type',
        'related_id',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function related()
    {
        return $this->morphTo();
    }
}
