<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MassSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_of_week',
        'time',
        'language',
        'celebrant',
        'is_active',
        'notes',
        'special_date',
        'special_title',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'special_date' => 'date',
        // 'time' is a TIME column — keep as string, format in views with Carbon::parse()
    ];

    const DAYS = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];
}
