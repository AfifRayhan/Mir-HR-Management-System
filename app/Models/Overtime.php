<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'ot_start',
        'ot_stop',
        'total_ot_hours',
        'is_workday_duty_plus_5',
        'is_holiday_duty_plus_5',
        'is_eid_duty',
        'amount',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'is_workday_duty_plus_5' => 'boolean',
        'is_holiday_duty_plus_5' => 'boolean',
        'is_eid_duty' => 'boolean',
        'total_ot_hours' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
