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
            'api_token' => 'nullable|string|max:80|unique:devices,api_token',
            'address' => 'nullable|string',
        ]);

        if (empty($validated['api_token']) && !empty($validated['device_uid'])) {
            $validated['api_token'] = \Illuminate\Support\Str::random(60);
        }

        Device::create($validated);
        return redirect()->route('settings.devices.index')->with('success', 'Device added successfully.');
    }

    public function update(Request $request, Device $device)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'device_uid' => 'nullable|string|max:50|unique:devices,device_uid,' . $device->id,
            'api_token' => 'nullable|string|max:80|unique:devices,api_token,' . $device->id,
            'address' => 'nullable|string',
        ]);

        if ($request->has('regenerate_token')) {
            $validated['api_token'] = \Illuminate\Support\Str::random(60);
        }

        $device->update($validated);
        return redirect()->route('settings.devices.index')->with('success', 'Device updated successfully.');
    }

    public function destroy(Device $device)
    {
        $device->delete();
        return redirect()->route('settings.devices.index')->with('success', 'Device deleted successfully.');
    }
}
