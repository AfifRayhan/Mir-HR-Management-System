<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\WeeklyHoliday;
use Illuminate\Http\Request;

class WeeklyHolidayController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();
        
        $offices = \App\Models\Office::orderBy('name')->get();
        $officeId = $request->input('office_id');
        
        // System defaults if officeId is empty
        $weeklyHolidays = WeeklyHoliday::where('office_id', $officeId ?: null)->get();

        // If specific office has no config yet, show global default but don't save yet
        if ($weeklyHolidays->isEmpty() && $officeId) {
            $weeklyHolidays = WeeklyHoliday::whereNull('office_id')->get();
        }

        return view('settings.holidays.weekly', compact('weeklyHolidays', 'user', 'roleName', 'employee', 'offices', 'officeId'));
    }

    public function update(Request $request)
    {
        $officeId = $request->input('office_id') ?: null;
        $holidays = $request->input('holidays', []);

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        foreach ($days as $day) {
            WeeklyHoliday::updateOrCreate(
                ['day_name' => $day, 'office_id' => $officeId],
                ['is_holiday' => in_array($day, $holidays)]
            );
        }

        return redirect()->route('settings.holidays.weekly.index', ['office_id' => $officeId])
            ->with('success', 'Weekly holidays updated successfully.');
    }
}
