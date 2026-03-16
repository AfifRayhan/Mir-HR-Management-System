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
                $q->where('first_name', 'like', '%' . $this->request['search'] . '%')
                    ->orWhere('last_name', 'like', '%' . $this->request['search'] . '%')
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
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'Address',
            'Date of Birth',
            'Joining Date',
            'Department',
            'Section',
            'Designation',
            'Grade',
            'Office',
            'Office Time',
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
            $employee->first_name,
            $employee->last_name,
            $employee->user->email ?? 'N/A',
            $employee->phone,
            $employee->address,
            $employee->date_of_birth,
            $employee->joining_date,
            $employee->department->name ?? 'N/A',
            $employee->section->name ?? 'N/A',
            $employee->designation->name ?? 'N/A',
            $employee->grade->name ?? 'N/A',
            $employee->office->name ?? 'N/A',
            $employee->officeTime->name ?? 'N/A',
            ucfirst($employee->status),
        ];
    }
}
