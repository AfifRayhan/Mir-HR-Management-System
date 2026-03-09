<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use App\Models\Employee;
use App\Models\Office;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = Employee::where('user_id', $user->id)->first();

        $leaveTypes = LeaveType::with('office')->orderBy('sort_order')->orderBy('name')->get();
        $offices = Office::all();

        return view('settings.leave-types.index', compact('leaveTypes', 'user', 'roleName', 'employee', 'offices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:100',
            'office_id'           => 'nullable|exists:offices,id',
            'total_days_per_year' => 'required|integer|min:0',
            'max_consecutive_days' => 'nullable|integer|min:1',
            'carry_forward'       => 'boolean',
            'sort_order'          => 'required|integer|min:1',
        ]);

        $validated['carry_forward'] = $request->has('carry_forward');

        LeaveType::create($validated);
        return redirect()->back()->with('success', 'Leave Type created successfully.');
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:100',
            'office_id'           => 'nullable|exists:offices,id',
            'total_days_per_year' => 'required|integer|min:0',
            'max_consecutive_days' => 'nullable|integer|min:1',
            'carry_forward'       => 'boolean',
            'sort_order'          => 'required|integer|min:1',
        ]);

        $validated['carry_forward'] = $request->has('carry_forward');

        $leaveType->update($validated);
        return redirect()->back()->with('success', 'Leave Type updated successfully.');
    }

    public function destroy(LeaveType $leaveType)
    {
        $leaveType->delete();
        return redirect()->back()->with('success', 'Leave Type deleted successfully.');
    }
}
