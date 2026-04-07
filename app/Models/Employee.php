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
        'hrm_employee_id',
        'name',
        'email',
        'phone',
        'blood_group',
        'father_name',
        'mother_name',
        'spouse_name',
        'gender',
        'religion',
        'marital_status',
        'national_id',
        'tin',
        'nationality',
        'no_of_children',
        'contact_no',
        'emergency_contact_name',
        'emergency_contact_address',
        'emergency_contact_no',
        'emergency_contact_relation',
        'date_of_birth',
        'joining_date',
        'discontinuation_date',
        'discontinuation_reason',
        'present_address',
        'permanent_address',
        'department_id',
        'section_id',
        'designation_id',
        'grade_id',
        'office_id',
        'office_time_id',
        'reporting_manager_id',
        'status',
        'employee_type',
        'probation_duration',
        'probation_start_date',
        'probation_end_date',
        'gross_salary',
    ];

    public function scopeProbationEnded($query)
    {
        return $query->where('employee_type', 'Probation')
            ->whereDate('probation_end_date', '<=', now());
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
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

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function manualAttendanceAdjustments()
    {
        return $this->hasMany(ManualAttendanceAdjustment::class);
    }

    public function supervisorRemarks()
    {
        return $this->hasMany(SupervisorRemark::class, 'employee_id');
    }

    public function activeSupervisorRemarks()
    {
        return $this->hasMany(SupervisorRemark::class, 'employee_id')->active();
    }

    public function salaryHistories()
    {
        return $this->hasMany(EmployeeSalaryHistory::class);
    }

    /**
     * Generate next employee code based on joining date.
     * Format: YYMMXXXX (8 digits total, 4 digits YYMM + 4 digits increment)
     */
    public static function generateEmployeeCode($joiningDate, $officeId = null)
    {
        if (!$joiningDate) {
            $joiningDate = now();
        }
        
        $carbonDate = \Carbon\Carbon::parse($joiningDate);
        $prefix = $carbonDate->format('ym'); // YYMM
        
        $query = self::query()
            ->whereRaw('LENGTH(employee_code) >= 5')
            ->whereRaw('employee_code REGEXP "^[0-9]+$"');

        if ($officeId) {
            $query->where('office_id', $officeId);
        }
        
        // Find the employee with the highest sequence number
        $lastEmployee = $query->orderByRaw('CAST(SUBSTRING(employee_code, 5) AS UNSIGNED) DESC')->first();
            
        $nextNumber = 1;
        if ($lastEmployee) {
            $lastCode = $lastEmployee->employee_code;
            $sequenceStr = substr($lastCode, 4);
            if (is_numeric($sequenceStr)) {
                $nextNumber = (int)$sequenceStr + 1;
            }
        }
        
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
