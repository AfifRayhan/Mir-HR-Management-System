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
        'late_seconds',
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

    /**
     * Get the late timing in hr:min:sec format.
     */
    public function getLateTimingAttribute()
    {
        if (!$this->late_seconds) {
            return '0:00:00';
        }

        $hours = floor($this->late_seconds / 3600);
        $minutes = floor(($this->late_seconds % 3600) / 60);
        $seconds = $this->late_seconds % 60;

        return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
    }

    /**
     * Get the late time in hours.
     */
    public function getLateHoursAttribute()
    {
        if (!$this->late_seconds) {
            return 0;
        }
        return round($this->late_seconds / 3600, 2);
    }
}
