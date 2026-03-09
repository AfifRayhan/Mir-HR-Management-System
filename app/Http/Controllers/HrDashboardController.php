<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Section;
use Illuminate\Http\Request;

class HrDashboardController extends Controller
{
    /**
     * Display the HR dashboard.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';

        if ($roleName !== 'HR Admin') {
            abort(403, 'Unauthorized action. Only HR Admins can access this dashboard.');
        }

        $employee = Employee::where('user_id', $user->id)->first();

        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();
        $totalDepartments = Department::count();
        $totalSections = Section::count();

        return view('hr-dashboard', compact(
            'user',
            'roleName',
            'employee',
            'totalEmployees',
            'activeEmployees',
            'totalDepartments',
            'totalSections'
        ));
    }
}
