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
        $statusFilter = $request->has('status') ? $request->status : 'active';
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }
 
        // Sorting — whitelist allowed columns and directions to prevent SQL injection
        $allowedSortColumns = ['name', 'employee_code', 'created_at', 'joining_date', 'department_id', 'designation_id', 'office_id', 'status'];
        $allowedDirections = ['asc', 'desc'];
        $sortColumn = in_array($request->input('sort'), $allowedSortColumns) ? $request->input('sort') : 'created_at';
        $sortDirection = in_array(strtolower($request->input('direction', 'desc')), $allowedDirections) ? strtolower($request->input('direction', 'desc')) : 'desc';
        
        if ($sortColumn === 'employee_code') {
            $query->orderByRaw('LENGTH(employee_code) ' . $sortDirection)
                  ->orderBy('employee_code', $sortDirection);
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }
 
        $employees = $query->paginate(10)->withQueryString();
        $departments = \Illuminate\Support\Facades\Cache::remember('departments_all', 3600, fn() => Department::all());
        $sections = \Illuminate\Support\Facades\Cache::remember('sections_with_dept_all', 3600, fn() => Section::with('department')->get());
        $offices = \Illuminate\Support\Facades\Cache::remember('offices_all', 3600, fn() => Office::all());
        $designations = \Illuminate\Support\Facades\Cache::remember('designations_all', 3600, fn() => Designation::all());
 
        $allEmployees = Employee::select('id', 'name', 'employee_code')->orderBy('name')->get();
 
        return view('personnel.employees.index', compact('employees', 'departments', 'sections', 'offices', 'designations', 'allEmployees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = \Illuminate\Support\Facades\Cache::remember('departments_all', 3600, fn() => Department::all());
        $sections = \Illuminate\Support\Facades\Cache::remember('sections_all', 3600, fn() => Section::all());
        $designations = \Illuminate\Support\Facades\Cache::remember('designations_ordered_all', 3600, fn() => Designation::orderBy('priority', 'asc')->get());
        $grades = \Illuminate\Support\Facades\Cache::remember('grades_all', 3600, fn() => Grade::all());
        $offices = \Illuminate\Support\Facades\Cache::remember('offices_all', 3600, fn() => Office::all());
        $officeTimes = \Illuminate\Support\Facades\Cache::remember('office_times_all', 3600, fn() => OfficeTime::all());
        $managers = Employee::all();

        // Roles for new user creation
        $roles = \Illuminate\Support\Facades\Cache::remember('roles_ordered_all', 3600, fn() => Role::orderBy('name')->get());

        // Generate auto employee code based on today as default
        $autoEmployeeCode = Employee::generateEmployeeCode(now());

        // Users not linked to any employee
        $unassignedUsers = User::whereDoesntHave('employee')->orderBy('name')->get();

        return view('personnel.employees.form', compact(
            'departments',
            'sections',
            'designations',
            'grades',
            'offices',
            'officeTimes',
            'managers',
            'roles',
            'autoEmployeeCode',
            'unassignedUsers'
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
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'user_status' => 'nullable|in:active,inactive',
            'link_user_id' => 'nullable|exists:users,id',
        ]);

        if ($request->filled('link_user_id')) {
            $userId = $request->link_user_id;
            // Optionally sync name/email to user if requested, but for now just link
            $user = User::find($userId);
            if ($user && empty($user->role_id) && $request->filled('role_id')) {
                $user->role_id = $request->role_id;
                $user->save();
            }
        } elseif ($request->filled('password') || $request->filled('role_id')) {
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

        // Use explicit allowlist to prevent mass assignment of sensitive fields (e.g., user_id, status)
        $employeeData = $request->only([
            'employee_code', 'hrm_employee_id', 'name', 'email', 'personal_email', 'phone', 'blood_group',
            'father_name', 'mother_name', 'spouse_name', 'gender', 'religion', 'marital_status',
            'national_id', 'tin', 'nationality', 'no_of_children', 'contact_no',
            'emergency_contact_name', 'emergency_contact_address', 'emergency_contact_no',
            'emergency_contact_relation', 'date_of_birth', 'joining_date',
            'discontinuation_date', 'discontinuation_reason', 'present_address', 'permanent_address',
            'department_id', 'section_id', 'designation_id', 'grade_id', 'office_id', 'office_time_id',
            'reporting_manager_id', 'roster_group', 'employee_type',
            'probation_duration', 'probation_start_date', 'probation_end_date', 'gross_salary',
        ]);
        $employeeData['status'] = $request->user_status ?? 'active'; // Employee status syncs with user status
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

        return redirect()->route('personnel.employees.edit', $employee->id)->with('success', 'Employee created successfully.');
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
        $departments = \Illuminate\Support\Facades\Cache::remember('departments_all', 3600, fn() => Department::all());
        $sections = \Illuminate\Support\Facades\Cache::remember('sections_all', 3600, fn() => Section::all());
        $designations = \Illuminate\Support\Facades\Cache::remember('designations_ordered_all', 3600, fn() => Designation::orderBy('priority', 'asc')->get());
        $grades = \Illuminate\Support\Facades\Cache::remember('grades_all', 3600, fn() => Grade::all());
        $offices = \Illuminate\Support\Facades\Cache::remember('offices_all', 3600, fn() => Office::all());
        $officeTimes = \Illuminate\Support\Facades\Cache::remember('office_times_all', 3600, fn() => OfficeTime::all());
        $managers = Employee::where('id', '!=', $employee->id)->get();

        // Roles for user modification
        $roles = \Illuminate\Support\Facades\Cache::remember('roles_ordered_all', 3600, fn() => Role::orderBy('name')->get());

        // Users not linked to any employee
        $unassignedUsers = User::whereDoesntHave('employee')
            ->where('id', '!=', $employee->user_id ?? 0)
            ->orderBy('name')
            ->get();

        return view('personnel.employees.form', compact(
            'employee',
            'departments',
            'sections',
            'designations',
            'grades',
            'offices',
            'officeTimes',
            'managers',
            'roles',
            'unassignedUsers'
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
            'role_id' => 'required|exists:roles,id',
            'user_status' => 'nullable|in:active,inactive',
            'link_user_id' => 'nullable|exists:users,id',
        ]);

        if ($employee->user_id) {
            $user = User::find($employee->user_id);
            if ($user) {
                // If the form wants to UNLINK the current user (if link_user_id is set to something else or empty)
                if ($request->has('link_user_id') && $request->link_user_id != $employee->user_id) {
                    $employee->user_id = $request->link_user_id ?: null;
                    $employee->save();
                } else {
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
            }
        } elseif ($request->filled('link_user_id')) {
            $employee->user_id = $request->link_user_id;
            $user = User::find($request->link_user_id);
            if ($user && empty($user->role_id) && $request->filled('role_id')) {
                $user->role_id = $request->role_id;
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

        // Use explicit allowlist to prevent mass assignment of sensitive fields
        $employeeData = $request->only([
            'employee_code', 'hrm_employee_id', 'name', 'email', 'personal_email', 'phone', 'blood_group',
            'father_name', 'mother_name', 'spouse_name', 'gender', 'religion', 'marital_status',
            'national_id', 'tin', 'nationality', 'no_of_children', 'contact_no',
            'emergency_contact_name', 'emergency_contact_address', 'emergency_contact_no',
            'emergency_contact_relation', 'date_of_birth', 'joining_date',
            'discontinuation_date', 'discontinuation_reason', 'present_address', 'permanent_address',
            'department_id', 'section_id', 'designation_id', 'grade_id', 'office_id', 'office_time_id',
            'reporting_manager_id', 'roster_group', 'employee_type',
            'probation_duration', 'probation_start_date', 'probation_end_date', 'gross_salary',
        ]);
        
        if ($request->has('link_user_id')) {
            $employeeData['user_id'] = $request->link_user_id ?: null;
        }
        
        // Sync Employee status with user status
        if ($request->has('user_status')) {
            $employeeData['status'] = $request->user_status;
        }
        
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

        return back()->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        DB::transaction(function () use ($employee) {
            // Capture the linked user ID before clearing the FK
            $linkedUserId = $employee->user_id;

            // Break the FK link first so employee deletion never depends on DB cascade behavior.
            if ($linkedUserId) {
                $employee->user_id = null;
                $employee->save();
            }

            // Delete the employee record
            Employee::whereKey($employee->id)->delete();

            // Delete the linked user account
            if ($linkedUserId) {
                User::where('id', $linkedUserId)->delete();
            }
        });
        
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
        $statusFilter = $request->has('status') ? $request->status : 'active';
        if ($statusFilter) $query->where('status', $statusFilter);

        // Sorting — whitelist allowed columns and directions to prevent SQL injection
        $allowedSortColumns = ['name', 'employee_code', 'created_at', 'joining_date', 'department_id', 'designation_id', 'office_id', 'status'];
        $allowedDirections = ['asc', 'desc'];
        $sortColumn = in_array($request->input('sort'), $allowedSortColumns) ? $request->input('sort') : 'created_at';
        $sortDirection = in_array(strtolower($request->input('direction', 'asc')), $allowedDirections) ? strtolower($request->input('direction', 'asc')) : 'asc';
        if ($sortColumn === 'employee_code') {
            $query->orderByRaw('LENGTH(employee_code) ' . $sortDirection)
                  ->orderBy('employee_code', $sortDirection);
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $employees = $query->get();

        $selectedOffice = null;
        if ($request->office_id) {
            $selectedOffice = Office::find($request->office_id);
        }

        $pdf = PDF::loadView('personnel.employees.exports.pdf', [
                'employees' => $employees,
                'allColumns' => $allColumns,
                'selectedColumns' => $selectedColumns,
                'selectedOffice' => $selectedOffice,
            ])
            ->setPaper('a4', 'landscape')
            ->setOption('margin-bottom', 10)
            ->setOption('margin-top', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        if ($request->input('action') === 'print') {
            return $pdf->inline('employees_' . date('Y-m-d_H-i-s') . '.pdf');
        }
        return $pdf->download('employees_' . date('Y-m-d_H-i-s') . '.pdf');
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
        $statusFilter = $request->has('status') ? $request->status : 'active';
        if ($statusFilter) $query->where('status', $statusFilter);

        // Sorting — whitelist allowed columns and directions to prevent SQL injection
        $allowedSortColumns = ['name', 'employee_code', 'created_at', 'joining_date', 'department_id', 'designation_id', 'office_id', 'status'];
        $allowedDirections = ['asc', 'desc'];
        $sortColumn = in_array($request->input('sort'), $allowedSortColumns) ? $request->input('sort') : 'created_at';
        $sortDirection = in_array(strtolower($request->input('direction', 'asc')), $allowedDirections) ? strtolower($request->input('direction', 'asc')) : 'asc';
        if ($sortColumn === 'employee_code') {
            $query->orderByRaw('LENGTH(employee_code) ' . $sortDirection)
                  ->orderBy('employee_code', $sortDirection);
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $employees = $query->get();

        $filename = 'employees_' . date('Y-m-d_H-i-s') . '.doc';

        $selectedOffice = null;
        if ($request->office_id) {
            $selectedOffice = Office::find($request->office_id);
        }

        return response()->view('personnel.employees.exports.word', [
            'employees' => $employees,
            'allColumns' => $allColumns,
            'selectedColumns' => $selectedColumns,
            'selectedOffice' => $selectedOffice,
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

        $query = Employee::query()
            ->select('employees.*')
            ->with(['department', 'section', 'designation', 'grade', 'office', 'officeTime', 'user'])
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id');

        // Reuse same filters as index
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('employees.name', 'like', '%' . $request->search . '%')
                  ->orWhere('employees.employee_code', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->department_id) $query->where('employees.department_id', $request->department_id);
        if ($request->office_id) $query->where('employees.office_id', $request->office_id);
        if ($request->designation_id) $query->where('employees.designation_id', $request->designation_id);
        if ($request->section_id) $query->where('employees.section_id', $request->section_id);
        $statusFilter = $request->has('status') ? $request->status : 'active';
        if ($statusFilter !== 'all') $query->where('employees.status', $statusFilter);

        // Hierarchical sorting for tree view
        $query->orderBy('employees.office_id')
              ->orderBy('departments.order_sequence')
              ->orderBy('designations.priority')
              ->orderByRaw('LENGTH(employees.employee_code) ASC')
              ->orderBy('employees.employee_code', 'ASC');

        $employees = $query->paginate(50)->withQueryString();

        $selectedOffice = null;
        if ($request->office_id) {
            $selectedOffice = Office::find($request->office_id);
        }

        $offices = \Illuminate\Support\Facades\Cache::remember('offices_ordered_all', 3600, fn() => Office::orderBy('name')->get());
        $departments = \Illuminate\Support\Facades\Cache::remember('departments_ordered_all', 3600, fn() => Department::orderBy('name')->get());
        $sections = \Illuminate\Support\Facades\Cache::remember('sections_ordered_all', 3600, fn() => Section::orderBy('name')->get());
        $designations = \Illuminate\Support\Facades\Cache::remember('designations_ordered_name_all', 3600, fn() => Designation::orderBy('name')->get());

        $allEmployees = Employee::select('id', 'name', 'employee_code')->where('status', 'active')->orderBy('name')->get();
 
        return view('personnel.employees.export-preview', [
            'employees' => $employees,
            'allColumns' => $allColumns,
            'selectedColumns' => $selectedColumns,
            'offices' => $offices,
            'departments' => $departments,
            'sections' => $sections,
            'designations' => $designations,
            'selectedOffice' => $selectedOffice,
            'allEmployees' => $allEmployees,
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

    /**
     * Download individual employee profile as PDF.
     */
    public function profilePdf(Employee $employee)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $employee->load([
            'department', 'section', 'designation', 'grade',
            'office', 'officeTime', 'user', 'reportingManager',
            'experiences', 'qualifications',
        ]);

        $office = $employee->office;

        $pdf = PDF::loadView('personnel.employees.exports.profile-pdf', [
                'employee' => $employee,
                'office' => $office,
            ])
            ->setPaper('a4', 'portrait')
            ->setOption('margin-bottom', 24)
            ->setOption('margin-top', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10)
            ->setOption('footer-center', 'Page [page] of [topage]')
            ->setOption('footer-right', $employee->name)
            ->setOption('footer-line', true)
            ->setOption('footer-font-size', 8)
            ->setOption('footer-spacing', 0);

        if (request()->input('action') === 'print') {
            return $pdf->inline('profile_' . $employee->employee_code . '_' . date('Y-m-d') . '.pdf');
        }
        return $pdf->download('profile_' . $employee->employee_code . '_' . date('Y-m-d') . '.pdf');
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
