<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeDashboardController extends Controller
{
    /**
     * Display the Employee dashboard.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';

        // An Employee might have an associated Employee record
        $employee = Employee::where('user_id', $user->id)->first();

        return view('employee-dashboard', compact(
            'user',
            'roleName',
            'employee'
        ));
    }

    /**
     * Display the dedicated Employee Profile.
     */
    public function profile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';

        // Load employee with all necessary relationships for the profile view
        $employee = Employee::where('user_id', $user->id)
            ->with(['department', 'section', 'designation', 'grade', 'officeTime', 'reportingManager'])
            ->first();

        return view('personnel.employees.profile', compact(
            'user',
            'roleName',
            'employee'
        ));
    }
}
