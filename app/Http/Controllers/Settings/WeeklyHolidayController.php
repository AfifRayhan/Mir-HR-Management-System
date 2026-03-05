<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\WeeklyHoliday;
use Illuminate\Http\Request;

class WeeklyHolidayController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();
        $weeklyHolidays = WeeklyHoliday::all();

        return view('settings.holidays.weekly', compact('weeklyHolidays', 'user', 'roleName', 'employee'));
    }

    public function update(Request $request)
    {
        $holidays = $request->input('holidays', []);

        WeeklyHoliday::query()->update(['is_holiday' => false]);

        if (!empty($holidays)) {
            WeeklyHoliday::whereIn('day_name', $holidays)->update(['is_holiday' => true]);
        }

        return redirect()->back()->with('success', 'Weekly holidays updated successfully.');
    }
}
