<?php

namespace App\Http\Controllers\Roster;

use App\Http\Controllers\Controller;
use App\Models\RosterTime;
use Illuminate\Http\Request;

class DriverRosterTimeController extends Controller
{
    const GROUP_MAP = [
        'drivers' => 'Drivers',
    ];

    public function index(Request $request)
    {
        $groups = self::GROUP_MAP;
        $selectedGroup = $request->query('group', array_key_first($groups));
        
        $rosterTimes = RosterTime::where('group_slug', $selectedGroup)
            ->orderBy('shift_key')
            ->get();
        
        $routePrefix = 'driver-roster.';
        $pageTitle = 'Driver Roster';
        return view('roster.times.index', compact('rosterTimes', 'groups', 'selectedGroup', 'routePrefix', 'pageTitle'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'group_slug'    => 'required|string',
            'shift_key'     => 'required|string',
            'display_label' => 'required|string',
            'start_time'    => 'nullable|date_format:H:i',
            'end_time'      => 'nullable|date_format:H:i',
            'badge_class'   => 'required|string',
            'is_off_day'    => 'boolean',
        ]);

        RosterTime::create($request->all());

        return redirect()->route('driver-roster.times.index')->with('success', 'Roster Time created successfully.');
    }

    public function update(Request $request, RosterTime $rosterTime)
    {
        $request->validate([
            'group_slug'    => 'required|string',
            'shift_key'     => 'required|string',
            'display_label' => 'required|string',
            'start_time'    => 'nullable|date_format:H:i',
            'end_time'      => 'nullable|date_format:H:i',
            'badge_class'   => 'required|string',
            'is_off_day'    => 'boolean',
        ]);

        $rosterTime->update($request->all());

        return redirect()->route('driver-roster.times.index')->with('success', 'Roster Time updated successfully.');
    }

    public function destroy(RosterTime $rosterTime)
    {
        $rosterTime->delete();
        return redirect()->route('driver-roster.times.index')->with('success', 'Roster Time deleted successfully.');
    }
}
