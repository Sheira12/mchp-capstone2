<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'sender',
        'message',
        'intent',
        'is_escalated',
        'escalated_to',
        'escalated_at',
        'ip_address',
    ];

    protected $casts = [
        'is_escalated' => 'boolean',
        'escalated_at' => 'datetime',
    ];

    public function escalatedTo()
    {
        return $this->belongsTo(User::class, 'escalated_to');
    }
}
