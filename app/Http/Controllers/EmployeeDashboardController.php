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
        $user = $request->user();
        $roleName = optional($user->role)->name ?? 'Unassigned';

        // An Employee might have an associated Employee record
        $employee = Employee::where('user_id', $user->id)->first();

        return view('employee-dashboard', compact(
            'user',
            'roleName',
            'employee'
        ));
    }
}
