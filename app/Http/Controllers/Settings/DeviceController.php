<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Employee;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = Employee::where('user_id', $user->id)->first();
        $devices = Device::all();

        return view('settings.devices.index', compact('devices', 'user', 'roleName', 'employee'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'device_uid' => 'nullable|string|max:50|unique:devices,device_uid',
            'port' => 'nullable|string|max:10',
            'ip_address' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        Device::create($validated);
        return redirect()->route('settings.devices.index')->with('success', 'Device added successfully.');
    }

    public function update(Request $request, Device $device)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'device_uid' => 'nullable|string|max:50|unique:devices,device_uid,' . $device->id,
            'port' => 'nullable|string|max:10',
            'ip_address' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        $device->update($validated);
        return redirect()->route('settings.devices.index')->with('success', 'Device updated successfully.');
    }

    public function destroy(Device $device)
    {
        $device->delete();
        return redirect()->route('settings.devices.index')->with('success', 'Device deleted successfully.');
    }
}
