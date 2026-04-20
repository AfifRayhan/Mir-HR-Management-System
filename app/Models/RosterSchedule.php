<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RosterSchedule extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'shift_type',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
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
