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
        $request->validate([
            'shift_name'   => 'required|string|max:100',
            'start_time'   => 'required',
            'end_time'     => 'required',
            'late_after'   => 'nullable',
            'absent_after' => 'nullable',
            'lunch_start'  => 'nullable',
            'lunch_end'    => 'nullable',
            'remarks'      => 'nullable|string|max:100',
        ]);

        $start = $request->start_time;
        $end = $request->end_time;
        $isOvernight = $end < $start;

        $timeFields = ['late_after', 'absent_after', 'lunch_start', 'lunch_end'];
        $customErrors = [];

        foreach ($timeFields as $field) {
            $value = $request->$field;
            if ($value) {
                if (!$isOvernight) {
                    if ($value < $start || $value > $end) {
                        $fieldName = str_replace('_', ' ', ucfirst($field));
                        $customErrors[$field] = ["$fieldName must be between Start Time and End Time."];
                    }
                } else {
                    if ($value > $end && $value < $start) {
                        $fieldName = str_replace('_', ' ', ucfirst($field));
                        $customErrors[$field] = ["$fieldName must be within the shift duration (Start Time to End Time)."];
                    }
                }
            }
        }

        if ($request->late_after && $request->absent_after) {
            $isLateOvernight = $request->late_after < $start;
            $isAbsentOvernight = $request->absent_after < $start;
            
            $lateValue = $isLateOvernight ? '1' . $request->late_after : '0' . $request->late_after;
            $absentValue = $isAbsentOvernight ? '1' . $request->absent_after : '0' . $request->absent_after;
            
            if ($lateValue > $absentValue) {
                $customErrors['late_after'] = ["Late After time cannot be more than Absent After time."];
            }
        }

        if ($request->lunch_start && $request->lunch_end) {
            $isLnStartOvernight = $request->lunch_start < $start;
            $isLnEndOvernight = $request->lunch_end < $start;
            $lnStartVal = $isLnStartOvernight ? '1' . $request->lunch_start : '0' . $request->lunch_start;
            $lnEndVal = $isLnEndOvernight ? '1' . $request->lunch_end : '0' . $request->lunch_end;
            if ($lnStartVal >= $lnEndVal) {
                $customErrors['lunch_end'] = ["Lunch End must always be after Lunch Start."];
            }
        }

        if (!empty($customErrors)) {
            return redirect()->back()->withErrors($customErrors)->withInput();
        }

        OfficeTime::create($request->all());
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
        $request->validate([
            'shift_name'   => 'required|string|max:100',
            'start_time'   => 'required',
            'end_time'     => 'required',
            'late_after'   => 'nullable',
            'absent_after' => 'nullable',
            'lunch_start'  => 'nullable',
            'lunch_end'    => 'nullable',
            'remarks'      => 'nullable|string|max:100',
        ]);

        $start = $request->start_time;
        $end = $request->end_time;
        $isOvernight = $end < $start;

        $timeFields = ['late_after', 'absent_after', 'lunch_start', 'lunch_end'];
        $customErrors = [];

        foreach ($timeFields as $field) {
            $value = $request->$field;
            if ($value) {
                if (!$isOvernight) {
                    if ($value < $start || $value > $end) {
                        $fieldName = str_replace('_', ' ', ucfirst($field));
                        $customErrors[$field] = ["$fieldName must be between Start Time and End Time."];
                    }
                } else {
                    if ($value > $end && $value < $start) {
                        $fieldName = str_replace('_', ' ', ucfirst($field));
                        $customErrors[$field] = ["$fieldName must be within the shift duration (Start Time to End Time)."];
                    }
                }
            }
        }

        if ($request->late_after && $request->absent_after) {
            $isLateOvernight = $request->late_after < $start;
            $isAbsentOvernight = $request->absent_after < $start;
            
            $lateValue = $isLateOvernight ? '1' . $request->late_after : '0' . $request->late_after;
            $absentValue = $isAbsentOvernight ? '1' . $request->absent_after : '0' . $request->absent_after;
            
            if ($lateValue > $absentValue) {
                $customErrors['late_after'] = ["Late After time cannot be more than Absent After time."];
            }
        }

        if ($request->lunch_start && $request->lunch_end) {
            $isLnStartOvernight = $request->lunch_start < $start;
            $isLnEndOvernight = $request->lunch_end < $start;
            $lnStartVal = $isLnStartOvernight ? '1' . $request->lunch_start : '0' . $request->lunch_start;
            $lnEndVal = $isLnEndOvernight ? '1' . $request->lunch_end : '0' . $request->lunch_end;
            if ($lnStartVal >= $lnEndVal) {
                $customErrors['lunch_end'] = ["Lunch End must always be after Lunch Start."];
            }
        }

        if (!empty($customErrors)) {
            return redirect()->back()->withErrors($customErrors)->withInput();
        }

        $officeTime->update($request->all());
        return redirect()->route('settings.office-times.index')->with('success', 'Office Time updated successfully.');
    }

    public function destroy(OfficeTime $officeTime)
    {
        $officeTime->delete();
        return redirect()->route('settings.office-times.index')->with('success', 'Office Time deleted successfully.');
    }
}
