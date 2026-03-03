<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class HrDashboardController extends Controller
{
    /**
     * Display the HR dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $roleName = optional($user->role)->name ?? 'Unassigned';

        if ($roleName !== 'HR Admin') {
            abort(403, 'Unauthorized action. Only HR Admins can access this dashboard.');
        }

        $employee = Employee::where('user_id', $user->id)->first();

        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();
        $maleEmployees = Employee::where('gender', 'male')->count();
        $femaleEmployees = Employee::where('gender', 'female')->count();

        return view('hr-dashboard', compact(
            'user',
            'roleName',
            'employee',
            'totalEmployees',
            'activeEmployees',
            'maleEmployees',
            'femaleEmployees'
        ));
    }
}
