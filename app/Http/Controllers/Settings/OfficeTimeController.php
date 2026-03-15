<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\OfficeTime;
use App\Models\Employee;
use Illuminate\Http\Request;

class OfficeTimeController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = Employee::where('user_id', $user->id)->first();
        $officeTimes = OfficeTime::all();

        return view('settings.office-times.index', compact('officeTimes', 'user', 'roleName', 'employee'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = Employee::where('user_id', $user->id)->first();

        return view('settings.office-times.create', compact('user', 'roleName', 'employee'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shift_name'   => 'required|string|max:100',
            'start_time'   => 'required',
            'end_time'     => 'required',
            'late_after'   => 'nullable',
            'absent_after' => 'nullable',
            'lunch_start'  => 'nullable',
            'lunch_end'    => 'nullable',
            'remarks'      => 'nullable|string|max:100',
        ]);

        OfficeTime::create($validated);
        return redirect()->route('settings.office-times.index')->with('success', 'Office Time created successfully.');
    }

    public function edit(OfficeTime $officeTime)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = Employee::where('user_id', $user->id)->first();

        return view('settings.office-times.edit', compact('officeTime', 'user', 'roleName', 'employee'));
    }

    public function update(Request $request, OfficeTime $officeTime)
    {
        $validated = $request->validate([
            'shift_name'   => 'required|string|max:100',
            'start_time'   => 'required',
            'end_time'     => 'required',
            'late_after'   => 'nullable',
            'absent_after' => 'nullable',
            'lunch_start'  => 'nullable',
            'lunch_end'    => 'nullable',
            'remarks'      => 'nullable|string|max:100',
        ]);

        $officeTime->update($validated);
        return redirect()->route('settings.office-times.index')->with('success', 'Office Time updated successfully.');
    }

    public function destroy(OfficeTime $officeTime)
    {
        $officeTime->delete();
        return redirect()->route('settings.office-times.index')->with('success', 'Office Time deleted successfully.');
    }
}
