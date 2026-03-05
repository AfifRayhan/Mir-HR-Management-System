<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeTime extends Model
{
    use HasFactory;

    protected $table = 'office_times';

    protected $fillable = [
        'shift_name',
        'start_time',
        'end_time',
        'late_after',
        'absent_after',
        'lunch_start',
        'lunch_end',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
