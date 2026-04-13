<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeQualification extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'qualification',
        'level',
        'institution',
        'board_university',
        'passing_year',
        'group_major',
        'result',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
