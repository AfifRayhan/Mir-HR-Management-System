<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'gender',
        'phone',
        'address',
        'date_of_birth',
        'joining_date',
        'department_id',
        'section_id',
        'designation_id',
        'grade_id',
        'office_time_id',
        'reporting_manager_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

