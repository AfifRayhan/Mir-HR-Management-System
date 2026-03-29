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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\EmployeesExport;
use Maatwebsite\Excel\Facades\Excel;

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
        $offices = Office::all();
        $designations = Designation::all();
 
        return view('personnel.employees.index', compact('employees', 'departments', 'offices', 'designations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::all();
        $sections = Section::all();
        $designations = Designation::orderBy('priority', 'desc')->get();
        $grades = Grade::all();
        $offices = Office::all();
        $officeTimes = OfficeTime::all();
        $managers = Employee::all();

        // Users not yet linked to an employee
        $linkedUserIds = Employee::whereNotNull('user_id')->pluck('user_id')->toArray();
        $users = User::whereNotIn('id', $linkedUserIds)->get();

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
            'users',
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
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'blood_group' => 'nullable|string|max:10',
            'father_name' => 'nullable|string|max:150',
            'mother_name' => 'nullable|string|max:150',
            'date_of_birth' => 'nullable|date',
            'joining_date' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'section_id' => 'nullable|exists:sections,id',
            'designation_id' => 'nullable|exists:designations,id',
            'grade_id' => 'nullable|exists:grades,id',
            'office_id' => 'nullable|exists:offices,id',
            'office_time_id' => 'nullable|exists:office_times,id',
            'reporting_manager_id' => 'nullable|exists:employees,id',
            'user_id' => 'nullable|exists:users,id|unique:employees,user_id',
            'status' => 'required|in:active,resigned',
            'gross_salary' => 'nullable|numeric|min:0',
        ]);

        Employee::create($validated);

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
        $designations = Designation::orderBy('priority', 'desc')->get();
        $grades = Grade::all();
        $offices = Office::all();
        $officeTimes = OfficeTime::all();
        $managers = Employee::where('id', '!=', $employee->id)->get();

        // Users not yet linked to an employee, plus the currently linked user
        $linkedUserIds = Employee::whereNotNull('user_id')
            ->where('user_id', '!=', $employee->user_id)
            ->pluck('user_id')
            ->toArray();
        $users = User::whereNotIn('id', $linkedUserIds)->get();

        return view('personnel.employees.form', compact(
            'employee',
            'departments',
            'sections',
            'designations',
            'grades',
            'offices',
            'officeTimes',
            'managers',
            'users'
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
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'blood_group' => 'nullable|string|max:10',
            'father_name' => 'nullable|string|max:150',
            'mother_name' => 'nullable|string|max:150',
            'date_of_birth' => 'nullable|date',
            'joining_date' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'section_id' => 'nullable|exists:sections,id',
            'designation_id' => 'nullable|exists:designations,id',
            'grade_id' => 'nullable|exists:grades,id',
            'office_id' => 'nullable|exists:offices,id',
            'office_time_id' => 'nullable|exists:office_times,id',
            'reporting_manager_id' => 'nullable|exists:employees,id',
            'user_id' => 'nullable|exists:users,id|unique:employees,user_id,' . $employee->id,
            'status' => 'required|in:active,inactive,left,hold',
            'gross_salary' => 'nullable|numeric|min:0',
        ]);

        $employee->update($validated);

        return redirect()->route('personnel.employees.index')->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('personnel.employees.index')->with('success', 'Employee deleted successfully.');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new EmployeesExport($request->all()), 'employees_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    public function exportCsv(Request $request)
    {
        return Excel::download(new EmployeesExport($request->all()), 'employees_' . date('Y-m-d_H-i-s') . '.csv', \Maatwebsite\Excel\Excel::CSV);
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
}
