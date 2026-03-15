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
        'office_id',
    ];

    protected $casts = [
        'is_holiday' => 'boolean',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}
