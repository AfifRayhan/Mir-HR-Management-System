<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_code',
        'first_name',
        'last_name',
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

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function officeTime()
    {
        return $this->belongsTo(OfficeTime::class);
    }

    public function reportingManager()
    {
        return $this->belongsTo(Employee::class, 'reporting_manager_id');
    }

    public function directReports()
    {
        return $this->hasMany(Employee::class, 'reporting_manager_id');
    }

    public function inchargeOf()
    {
        return $this->hasOne(Department::class, 'incharge_id');
    }
}
