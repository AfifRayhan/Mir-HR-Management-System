<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\OfficeTime;
use Illuminate\Http\Request;

class OfficeTimeController extends Controller
{
    public function index()
    {
        $officeTimes = OfficeTime::all();
        return view('personnel.office-times.index', compact('officeTimes'));
    }

    public function create()
    {
        return view('personnel.office-times.create');
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
        ]);

        OfficeTime::create($validated);
        return redirect()->route('personnel.office-times.index')->with('success', 'Office Time created successfully.');
    }

    public function edit(OfficeTime $officeTime)
    {
        return view('personnel.office-times.edit', compact('officeTime'));
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
        ]);

        $officeTime->update($validated);
        return redirect()->route('personnel.office-times.index')->with('success', 'Office Time updated successfully.');
    }

    public function destroy(OfficeTime $officeTime)
    {
        $officeTime->delete();
        return redirect()->route('personnel.office-times.index')->with('success', 'Office Time deleted successfully.');
    }
}
