<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = Employee::where('user_id', $user->id)->first();

        $departments = Department::with('incharge')->orderBy('order_sequence', 'asc')->get();
        $employees = Employee::all();
        return view('personnel.departments.index', compact('departments', 'employees', 'user', 'roleName', 'employee'));
    }

    public function create()
    {
        $employees = Employee::all();
        return view('personnel.departments.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'short_name'     => 'nullable|string|max:50',
            'incharge_id'    => 'nullable|exists:employees,id',
            'description'    => 'nullable|string',
            'order_sequence' => 'nullable|integer',
        ]);

        Department::create($validated);
        return redirect()->route('personnel.departments.index')->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        $employees = Employee::all();
        return view('personnel.departments.edit', compact('department', 'employees'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'short_name'     => 'nullable|string|max:50',
            'incharge_id'    => 'nullable|exists:employees,id',
            'description'    => 'nullable|string',
            'order_sequence' => 'nullable|integer',
        ]);

        $department->update($validated);
        return redirect()->route('personnel.departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('personnel.departments.index')->with('success', 'Department deleted successfully.');
    }
}
