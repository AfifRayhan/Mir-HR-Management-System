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
        'name',
        'phone',
        'address',
        'date_of_birth',
        'joining_date',
        'department_id',
        'section_id',
        'designation_id',
        'grade_id',
        'office_id',
        'office_time_id',
        'reporting_manager_id',
        'status',
    ];

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

    /**
     * Generate next employee code based on joining date.
     * Format: YYMMXXXX (8 digits total, 4 digits YYMM + 4 digits increment)
     */
    public static function generateEmployeeCode($joiningDate)
    {
        if (!$joiningDate) {
            $joiningDate = now();
        }
        
        $carbonDate = \Carbon\Carbon::parse($joiningDate);
        $prefix = $carbonDate->format('ym'); // YYMM
        
        $lastEmployee = self::where('employee_code', 'like', $prefix . '%')
            ->orderBy('employee_code', 'desc')
            ->first();
            
        $nextNumber = 1;
        if ($lastEmployee) {
            // Extract the last 4 digits from the code (YYMMXXXX)
            $lastCode = $lastEmployee->employee_code;
            if (strlen($lastCode) >= 8) {
                $lastNumberPart = substr($lastCode, 4);
                if (is_numeric($lastNumberPart)) {
                    $nextNumber = (int)$lastNumberPart + 1;
                }
            } else if (preg_match('/(\d+)$/', $lastCode, $matches)) {
                // Fallback for non-matching formats if any
                $nextNumber = (int)$matches[1] + 1;
            }
        }
        
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
