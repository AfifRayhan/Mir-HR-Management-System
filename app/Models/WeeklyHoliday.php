<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyHoliday extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_name',
        'is_holiday',
    ];

    protected $casts = [
        'is_holiday' => 'boolean',
    ];
}
