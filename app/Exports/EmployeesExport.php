<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class EmployeesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Employee::with(['department', 'section', 'designation', 'grade', 'office', 'officeTime', 'user']);

        // Search
        if ($this->request['search'] ?? null) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->request['search'] . '%')
                    ->orWhere('employee_code', 'like', '%' . $this->request['search'] . '%');
            });
        }

        // Filters
        if ($this->request['department_id'] ?? null) {
            $query->where('department_id', $this->request['department_id']);
        }
        if ($this->request['section_id'] ?? null) {
            $query->where('section_id', $this->request['section_id']);
        }
        if ($this->request['designation_id'] ?? null) {
            $query->where('designation_id', $this->request['designation_id']);
        }
        if ($this->request['status'] ?? null) {
            $query->where('status', $this->request['status']);
        }

        // Sorting
        $sortColumn = $this->request['sort'] ?? 'created_at';
        $sortDirection = $this->request['direction'] ?? 'desc';
        $query->orderBy($sortColumn, $sortDirection);

        return $query;
    }

    public function headings(): array
    {
        return [
            'Employee Code',
            'Full Name',
            'Corporate Email',
            'Personal Email',
            'Phone',
            'Blood Group',
            'Father Name',
            'Mother Name',
            'Spouse Name',
            'Gender',
            'Religion',
            'Marital Status',
            'National ID',
            'TIN',
            'Nationality',
            'No. of Children',
            'Contact No',
            'Emergency Contact Name',
            'Emergency Contact Relation',
            'Emergency Contact No',
            'Emergency Contact Address',
            'Date of Birth',
            'Joining Date',
            'Discontinuation Date',
            'Discontinuation Reason',
            'Present Address',
            'Permanent Address',
            'Department',
            'Section',
            'Designation',
            'Grade',
            'Office',
            'Office Time',
            'Gross Salary',
            'Status',
        ];
    }

    /**
    * @var Employee $employee
    */
    public function map($employee): array
    {
        return [
            $employee->employee_code,
            $employee->name,
            $employee->email,
            $employee->personal_email,
            $employee->phone,
            $employee->blood_group,
            $employee->father_name,
            $employee->mother_name,
            $employee->spouse_name,
            $employee->gender,
            $employee->religion,
            $employee->marital_status,
            $employee->national_id,
            $employee->tin,
            $employee->nationality,
            $employee->no_of_children,
            $employee->contact_no,
            $employee->emergency_contact_name,
            $employee->emergency_contact_relation,
            $employee->emergency_contact_no,
            $employee->emergency_contact_address,
            $employee->date_of_birth,
            $employee->joining_date,
            $employee->discontinuation_date,
            $employee->discontinuation_reason,
            $employee->present_address,
            $employee->permanent_address,
            $employee->department->name ?? 'N/A',
            $employee->section->name ?? 'N/A',
            $employee->designation->name ?? 'N/A',
            $employee->grade->name ?? 'N/A',
            $employee->office->name ?? 'N/A',
            $employee->officeTime->shift_name ?? 'N/A',
            $employee->gross_salary,
            ucfirst($employee->status),
        ];
    }
}
