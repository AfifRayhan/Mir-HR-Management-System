<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Models\Grade;
use App\Models\OvertimeRate;
use App\Models\OvertimeSpecialRate;
use App\Models\Employee;
use Illuminate\Http\Request;

class OvertimeSettingController extends Controller
{
    public function index()
    {
        $grades = Grade::orderBy('name')->get();
        $designations = Designation::orderBy('name')->get();
        
        $gradeRates = OvertimeRate::whereNull('designation_id')->pluck('rate', 'grade_id')->all();
        $designationRates = OvertimeRate::whereNull('grade_id')->pluck('rate', 'designation_id')->all();

        $rosterGroups = Employee::whereNotNull('roster_group')
            ->where('roster_group', '!=', '')
            ->distinct()
            ->orderBy('roster_group')
            ->pluck('roster_group');
            
        $specialRates = OvertimeSpecialRate::where('is_eid_special', true)
            ->pluck('rate', 'roster_group')->all();

        return view('personnel.overtimes.settings', compact('grades', 'designations', 'gradeRates', 'designationRates', 'rosterGroups', 'specialRates'));
    }

    public function store(Request $request)
    {
        // Save grade rates
        $gradeRates = $request->input('grade_rates', []);
        foreach ($gradeRates as $gradeId => $rate) {
            if ($rate !== null && $rate !== '') {
                OvertimeRate::updateOrCreate(
                    ['grade_id' => $gradeId, 'designation_id' => null],
                    ['rate' => $rate]
                );
            }
        }

        // Save designation rates
        $designationRates = $request->input('designation_rates', []);
        foreach ($designationRates as $designationId => $rate) {
            if ($rate !== null && $rate !== '') {
                OvertimeRate::updateOrCreate(
                    ['designation_id' => $designationId, 'grade_id' => null],
                    ['rate' => $rate]
                );
            } else {
                OvertimeRate::where('designation_id', $designationId)->whereNull('grade_id')->delete();
            }
        }

        // Save designation eligibility
        $eligibleIds = $request->input('designations', []);
        Designation::query()->update(['is_ot_eligible' => false]);
        if (!empty($eligibleIds)) {
            Designation::whereIn('id', $eligibleIds)->update(['is_ot_eligible' => true]);
        }

        // Save special eid rates
        $specialRates = $request->input('special_rates', []);
        foreach ($specialRates as $group => $rate) {
            if ($rate !== null && $rate !== '') {
                OvertimeSpecialRate::updateOrCreate(
                    ['roster_group' => $group, 'is_eid_special' => true],
                    ['rate' => $rate]
                );
            } else {
                OvertimeSpecialRate::where('roster_group', $group)->where('is_eid_special', true)->delete();
            }
        }

        return redirect()->route('overtimes.settings')->with('success', 'Overtime settings updated successfully.');
    }
}
