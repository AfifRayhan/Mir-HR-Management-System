<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'in_time',
        'out_time',
        'working_hours',
        'late_minutes',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'in_time' => 'datetime',
        'out_time' => 'datetime',
        'working_hours' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
