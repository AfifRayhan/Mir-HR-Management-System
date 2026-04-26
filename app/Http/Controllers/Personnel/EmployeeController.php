<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Section;
use App\Models\Designation;
use App\Models\Grade;
use App\Models\OfficeTime;
use App\Models\Office;
use App\Models\User;
use App\Models\Role;
use App\Models\EmployeeExperience;
use App\Models\EmployeeQualification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Exports\EmployeesExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'section', 'designation', 'grade', 'office', 'officeTime', 'user']);

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('employee_code', 'like', '%' . $request->search . '%');
            });
        }

        // Filters
        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->office_id) {
            $query->where('office_id', $request->office_id);
        }
        if ($request->designation_id) {
            $query->where('designation_id', $request->designation_id);
        }
        if ($request->section_id) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
 
        // Sorting
        $sortColumn = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        
        if ($sortColumn === 'employee_code') {
            $query->orderByRaw('LENGTH(employee_code) ' . $sortDirection)
                  ->orderBy('employee_code', $sortDirection);
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }
 
        $employees = $query->paginate(10)->withQueryString();
        $departments = Department::all();
        $sections = Section::with('department')->get();
        $offices = Office::all();
        $designations = Designation::all();
 
        return view('personnel.employees.index', compact('employees', 'departments', 'sections', 'offices', 'designations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::all();
        $sections = Section::all();
        $designations = Designation::orderBy('priority', 'asc')->get();
        $grades = Grade::all();
        $offices = Office::all();
        $officeTimes = OfficeTime::all();
        $managers = Employee::all();

        // Roles for new user creation
        $roles = Role::orderBy('name')->get();

        // Generate auto employee code based on today as default
        $autoEmployeeCode = Employee::generateEmployeeCode(now());

        return view('personnel.employees.form', compact(
            'departments',
            'sections',
            'designations',
            'grades',
            'offices',
            'officeTimes',
            'managers',
            'roles',
            'autoEmployeeCode'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_code' => 'required|string|max:50|unique:employees,employee_code',
            'name' => 'required|string|max:200',
            'email' => 'nullable|email|max:150',
            'personal_email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:20',
            'blood_group' => 'nullable|string|max:10',
            'father_name' => 'nullable|string|max:150',
            'mother_name' => 'nullable|string|max:150',
            'spouse_name' => 'nullable|string|max:150',
            'gender' => 'nullable|string|max:20',
            'religion' => 'nullable|string|max:50',
            'marital_status' => 'nullable|string|max:50',
            'national_id' => 'nullable|string|max:50',
            'tin' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:50',
            'no_of_children' => 'nullable|integer|min:0',
            'contact_no' => 'nullable|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:150',
            'emergency_contact_address' => 'nullable|string',
            'emergency_contact_no' => 'nullable|string|max:20',
            'emergency_contact_relation' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'joining_date' => 'required|date',
            'discontinuation_date' => 'nullable|date',
            'discontinuation_reason' => 'nullable|string',
            'present_address' => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'section_id' => 'nullable|exists:sections,id',
            'designation_id' => 'nullable|exists:designations,id',
            'grade_id' => 'nullable|exists:grades,id',
            'office_id' => 'nullable|exists:offices,id',
            'office_time_id' => 'nullable|exists:office_times,id',
            'reporting_manager_id' => 'nullable|exists:employees,id',
            'roster_group' => 'nullable|string|in:All,NOC (Borak),NOC (Sylhet),Technician (Gulshan),Technician (Borak),Technician (Jessore),Technician (Sylhet)',
            'employee_type' => 'required|in:Regular,Probation',
            'probation_duration' => 'nullable|required_if:employee_type,Probation|integer|min:1',
            'probation_start_date' => 'nullable|required_if:employee_type,Probation|date',
            'probation_end_date' => 'nullable|required_if:employee_type,Probation|date|after_or_equal:probation_start_date',
            'gross_salary' => 'nullable|numeric|min:0',
            // User validations
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'nullable|exists:roles,id',
            'user_status' => 'nullable|in:active,inactive',
        ]);

        $userId = null;
        if ($request->filled('password') || $request->filled('role_id')) {
            $userEmail = $request->email;
            if (empty($userEmail)) {
                return back()->withErrors(['email' => 'Email is required to create a user account.'])->withInput();
            }
            // Manually check if valid unique user email
            $existingUser = User::where('email', $userEmail)->first();
            if ($existingUser) {
                return back()->withErrors(['email' => 'This email is already taken by another user.'])->withInput();
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $userEmail,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
                'status' => $request->user_status ?? 'active',
            ]);
            $userId = $user->id;
        }

        $employeeData = $request->except(['password', 'password_confirmation', 'role_id', 'user_status']);
        $employeeData['status'] = 'active'; // Employee status removed from form
        $employeeData['user_id'] = $userId;

        $employee = Employee::create($employeeData);

        if ($employee->gross_salary > 0) {
            \App\Models\EmployeeSalaryHistory::create([
                'employee_id' => $employee->id,
                'old_salary' => null,
                'new_salary' => $employee->gross_salary,
                'difference' => null,
                'type' => 'initial',
                'reason' => 'Initial Salary',
                'changed_by' => Auth::id(),
                'effective_date' => now(),
            ]);
        }

        // Save Experiences
        if ($request->filled('experiences')) {
            foreach ($request->experiences as $exp) {
                if (!empty($exp['organization']) || !empty($exp['designation'])) {
                    $employee->experiences()->create($exp);
                }
            }
        }

        // Save Qualifications
        if ($request->filled('qualifications')) {
            foreach ($request->qualifications as $qual) {
                if (!empty($qual['qualification'])) {
                    $employee->qualifications()->create($qual);
                }
            }
        }

        return redirect()->route('personnel.employees.index')->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        return view('personnel.employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        $departments = Department::all();
        $sections = Section::all();
        $designations = Designation::orderBy('priority', 'asc')->get();
        $grades = Grade::all();
        $offices = Office::all();
        $officeTimes = OfficeTime::all();
        $managers = Employee::where('id', '!=', $employee->id)->get();

        // Roles for user modification
        $roles = Role::orderBy('name')->get();

        return view('personnel.employees.form', compact(
            'employee',
            'departments',
            'sections',
            'designations',
            'grades',
            'offices',
            'officeTimes',
            'managers',
            'roles'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_code' => 'required|string|max:50|unique:employees,employee_code,' . $employee->id,
            'name' => 'required|string|max:200',
            'email' => 'nullable|email|max:150',
            'personal_email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:100',
            'blood_group' => 'nullable|string|max:10',
            'father_name' => 'nullable|string|max:150',
            'mother_name' => 'nullable|string|max:150',
            'spouse_name' => 'nullable|string|max:150',
            'gender' => 'nullable|string|max:20',
            'religion' => 'nullable|string|max:50',
            'marital_status' => 'nullable|string|max:50',
            'national_id' => 'nullable|string|max:50',
            'tin' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:50',
            'no_of_children' => 'nullable|integer|min:0',
            'contact_no' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:150',
            'emergency_contact_address' => 'nullable|string',
            'emergency_contact_no' => 'nullable|string|max:100',
            'emergency_contact_relation' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'joining_date' => 'required|date',
            'discontinuation_date' => 'nullable|date',
            'discontinuation_reason' => 'nullable|string',
            'present_address' => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'section_id' => 'nullable|exists:sections,id',
            'designation_id' => 'nullable|exists:designations,id',
            'grade_id' => 'nullable|exists:grades,id',
            'office_id' => 'nullable|exists:offices,id',
            'office_time_id' => 'nullable|exists:office_times,id',
            'reporting_manager_id' => 'nullable|exists:employees,id',
            'roster_group' => 'nullable|string|in:All,NOC (Borak),NOC (Sylhet),Technician (Gulshan),Technician (Borak),Technician (Jessore),Technician (Sylhet)',
            'employee_type' => 'required|in:Regular,Probation',
            'probation_duration' => 'nullable|required_if:employee_type,Probation|integer|min:1',
            'probation_start_date' => 'nullable|required_if:employee_type,Probation|date',
            'probation_end_date' => 'nullable|required_if:employee_type,Probation|date|after_or_equal:probation_start_date',
            'gross_salary' => 'nullable|numeric|min:0',
            // User validations
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'nullable|exists:roles,id',
            'user_status' => 'nullable|in:active,inactive',
        ]);

        if ($employee->user_id) {
            $user = User::find($employee->user_id);
            if ($user) {
                if ($request->filled('email') && $user->email !== $request->email) {
                    $existing = User::where('email', $request->email)->where('id', '!=', $user->id)->first();
                    if ($existing) {
                        return back()->withErrors(['email' => 'This email is already taken by another user.'])->withInput();
                    }
                }
                $user->name = $request->name;
                if ($request->filled('email')) $user->email = $request->email;
                if ($request->filled('password')) $user->password = Hash::make($request->password);
                $user->role_id = $request->role_id;
                if ($request->filled('user_status')) $user->status = $request->user_status;
                $user->save();
            }
        } elseif ($request->filled('password') || $request->filled('role_id') || $request->filled('user_status')) {
            $userEmail = $request->email;
            if (empty($userEmail)) {
                return back()->withErrors(['email' => 'Email is required to create a user account.'])->withInput();
            }
            $existingUser = User::where('email', $userEmail)->first();
            if ($existingUser) {
                return back()->withErrors(['email' => 'This email is already taken by another user.'])->withInput();
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $userEmail,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
                'status' => $request->user_status ?? 'active',
            ]);
            $employee->user_id = $user->id;
        }

        $employeeData = $request->except(['password', 'password_confirmation', 'role_id', 'user_status']);
        
        // Handle Salary History
        if (isset($employeeData['gross_salary'])) {
            $oldSalary = $employee->gross_salary;
            $newSalary = $employeeData['gross_salary'];
            
            if ($oldSalary != $newSalary && $oldSalary > 0) {
                \App\Models\EmployeeSalaryHistory::create([
                    'employee_id' => $employee->id,
                    'old_salary' => $oldSalary,
                    'new_salary' => $newSalary,
                    'difference' => $newSalary - $oldSalary,
                    'type' => $newSalary > $oldSalary ? 'increment' : 'decrement',
                    'reason' => $request->input('salary_change_reason'),
                    'changed_by' => Auth::id(),
                    'effective_date' => now(),
                ]);
            }
        }
        
        $employee->update($employeeData);

        // Sync Experiences (Delete existing and recreate)
        $employee->experiences()->delete();
        if ($request->filled('experiences')) {
            foreach ($request->experiences as $exp) {
                if (!empty($exp['organization']) || !empty($exp['designation'])) {
                    $employee->experiences()->create($exp);
                }
            }
        }

        // Sync Qualifications (Delete existing and recreate)
        $employee->qualifications()->delete();
        if ($request->filled('qualifications')) {
            foreach ($request->qualifications as $qual) {
                if (!empty($qual['qualification'])) {
                    $employee->qualifications()->create($qual);
                }
            }
        }

        return redirect()->route('personnel.employees.index')->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $userId = $employee->user_id;
        $employee->delete();
        
        if ($userId) {
            User::find($userId)?->delete();
        }
        
        return redirect()->route('personnel.employees.index')->with('success', 'Employee and associated user account deleted successfully.');
    }

    public function exportExcel(Request $request)
    {
        $columns = $request->input('columns', null);
        return Excel::download(new EmployeesExport($request->all(), $columns, false, 'excel'), 'employees_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    public function exportCsv(Request $request)
    {
        $columns = $request->input('columns', null);
        return Excel::download(new EmployeesExport($request->all(), $columns, false, 'csv'), 'employees_' . date('Y-m-d_H-i-s') . '.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);
        
        $allColumns = EmployeesExport::getColumnDefinitions();
        $selectedColumns = $request->input('columns', EmployeesExport::DEFAULT_COLUMNS);
        $selectedColumns = array_values(array_intersect($selectedColumns, array_keys($allColumns)));
        if (empty($selectedColumns)) {
            $selectedColumns = EmployeesExport::DEFAULT_COLUMNS;
        }

        $query = Employee::with(['department', 'section', 'designation', 'grade', 'office', 'officeTime', 'user']);

        // Apply filters
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('employee_code', 'like', "%{$request->search}%");
            });
        }
        if ($request->office_id) $query->where('office_id', $request->office_id);
        if ($request->department_id) $query->where('department_id', $request->department_id);
        if ($request->designation_id) $query->where('designation_id', $request->designation_id);
        if ($request->section_id) $query->where('section_id', $request->section_id);
        if ($request->status) $query->where('status', $request->status);

        // Sorting
        $sortColumn = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'asc');
        if ($sortColumn === 'employee_code') {
            $query->orderByRaw('LENGTH(employee_code) ' . $sortDirection)
                  ->orderBy('employee_code', $sortDirection);
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $employees = $query->get();

        return PDF::loadView('personnel.employees.exports.pdf', [
                'employees' => $employees,
                'allColumns' => $allColumns,
                'selectedColumns' => $selectedColumns,
            ])
            ->setPaper('a3', 'landscape')
            ->setOption('margin-bottom', 10)
            ->setOption('margin-top', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10)
            ->download('employees_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    public function exportWord(Request $request)
    {
        $allColumns = EmployeesExport::getColumnDefinitions();
        $selectedColumns = $request->input('columns', EmployeesExport::DEFAULT_COLUMNS);
        $selectedColumns = array_values(array_intersect($selectedColumns, array_keys($allColumns)));

        $query = Employee::with(['department', 'section', 'designation', 'grade', 'office', 'officeTime', 'user']);

        // Apply same filters as preview
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('employee_code', 'like', "%{$request->search}%");
            });
        }
        if ($request->office_id) $query->where('office_id', $request->office_id);
        if ($request->department_id) $query->where('department_id', $request->department_id);
        if ($request->designation_id) $query->where('designation_id', $request->designation_id);
        if ($request->section_id) $query->where('section_id', $request->section_id);
        if ($request->status) $query->where('status', $request->status);

        // Sorting
        $sortColumn = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'asc');
        if ($sortColumn === 'employee_code') {
            $query->orderByRaw('LENGTH(employee_code) ' . $sortDirection)
                  ->orderBy('employee_code', $sortDirection);
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $employees = $query->get();

        $filename = 'employees_' . date('Y-m-d_H-i-s') . '.doc';

        return response()->view('personnel.employees.exports.word', [
            'employees' => $employees,
            'allColumns' => $allColumns,
            'selectedColumns' => $selectedColumns,
        ])
        ->header('Content-Type', 'application/vnd.ms-word')
        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportPreview(Request $request)
    {
        $allColumns = EmployeesExport::getColumnDefinitions();
        $selectedColumns = $request->input('columns', EmployeesExport::DEFAULT_COLUMNS);

        // Validate columns
        $selectedColumns = array_values(array_intersect($selectedColumns, array_keys($allColumns)));
        if (empty($selectedColumns)) {
            $selectedColumns = EmployeesExport::DEFAULT_COLUMNS;
        }

        $query = Employee::with(['department', 'section', 'designation', 'grade', 'office', 'officeTime', 'user']);

        // Reuse same filters as index
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('employee_code', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->department_id) $query->where('department_id', $request->department_id);
        if ($request->office_id) $query->where('office_id', $request->office_id);
        if ($request->designation_id) $query->where('designation_id', $request->designation_id);
        if ($request->section_id) $query->where('section_id', $request->section_id);
        if ($request->status) $query->where('status', $request->status);

        // Sorting
        $sortColumn = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'asc');
        if ($sortColumn === 'employee_code') {
            $query->orderByRaw('LENGTH(employee_code) ' . $sortDirection)
                  ->orderBy('employee_code', $sortDirection);
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $employees = $query->paginate(50)->withQueryString();

        $offices = Office::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $sections = Section::orderBy('name')->get();
        $designations = Designation::orderBy('name')->get();

        return view('personnel.employees.export-preview', [
            'employees' => $employees,
            'allColumns' => $allColumns,
            'selectedColumns' => $selectedColumns,
            'offices' => $offices,
            'departments' => $departments,
            'sections' => $sections,
            'designations' => $designations,
            'sortColumn' => $sortColumn,
            'sortDirection' => $sortDirection,
        ]);
    }

    /**
     * Get the next available employee code for a given date.
     */
    public function getNextCode(Request $request)
    {
        $date = $request->date ?: now();
        $officeId = $request->office_id;
        $code = Employee::generateEmployeeCode($date, $officeId);
        return response()->json(['code' => $code]);
    }

    //Delete Experience
    public function destroyExperience(EmployeeExperience $experience)
    {
        try {
            $experience->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    //Delete Qualification
    public function destroyQualification(EmployeeQualification $qualification)
    {
        try {
            $qualification->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }
}
