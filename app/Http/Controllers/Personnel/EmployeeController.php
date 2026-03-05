<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Section;
use App\Models\Designation;
use App\Models\Grade;
use App\Models\OfficeTime;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'section', 'designation', 'grade', 'officeTime', 'user']);

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                    ->orWhere('last_name', 'like', '%' . $request->search . '%')
                    ->orWhere('employee_code', 'like', '%' . $request->search . '%');
            });
        }

        // Filters
        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortColumn = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        $employees = $query->paginate(10)->withQueryString();
        $departments = Department::all();

        return view('personnel.employees.index', compact('employees', 'departments'));
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
        $officeTimes = OfficeTime::all();
        $managers = Employee::all();

        // Users not yet linked to an employee
        $linkedUserIds = Employee::whereNotNull('user_id')->pluck('user_id')->toArray();
        $users = User::whereNotIn('id', $linkedUserIds)->get();

        return view('personnel.employees.form', compact(
            'departments',
            'sections',
            'designations',
            'grades',
            'officeTimes',
            'managers',
            'users'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_code' => 'required|string|max:50|unique:employees,employee_code',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'joining_date' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'section_id' => 'nullable|exists:sections,id',
            'designation_id' => 'nullable|exists:designations,id',
            'grade_id' => 'nullable|exists:grades,id',
            'office_time_id' => 'nullable|exists:office_times,id',
            'reporting_manager_id' => 'nullable|exists:employees,id',
            'user_id' => 'nullable|exists:users,id|unique:employees,user_id',
            'status' => 'required|in:active,resigned',
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
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'joining_date' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'section_id' => 'nullable|exists:sections,id',
            'designation_id' => 'nullable|exists:designations,id',
            'grade_id' => 'nullable|exists:grades,id',
            'office_time_id' => 'nullable|exists:office_times,id',
            'reporting_manager_id' => 'nullable|exists:employees,id',
            'user_id' => 'nullable|exists:users,id|unique:employees,user_id,' . $employee->id,
            'status' => 'required|in:active,resigned',
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
}
