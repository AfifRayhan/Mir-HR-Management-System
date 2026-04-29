<?php

namespace Database\Seeders;

use App\Models\Designation;
use App\Models\Grade;
use App\Models\OvertimeRate;
use Illuminate\Database\Seeder;

class OvertimeSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default OT Rates based on Grades
        $rates_grade = [
            'Management' => 115,
            'Technician' => 35,
            'Lineman' => 35,
            'Driver' => 30,
            'Peon' => 25,
            'Cleaner' => 25,
            'Cook' => 25,
        ];

        $rates_designation = [
            'Sr. Technical Officer' => 35,
            'Technical Officer' => 35,
            'Jr. Technical Officer' => 35,
        ];

        // Seed Grade-based rates
        foreach ($rates_grade as $gradeName => $rate) {
            $grade = Grade::where('name', $gradeName)->first();
            if ($grade) {
                OvertimeRate::updateOrCreate(
                    ['grade_id' => $grade->id, 'designation_id' => null],
                    ['rate' => $rate]
                );
            }
        }

        // Seed Designation-based rates (Overrides)
        foreach ($rates_designation as $designationName => $rate) {
            $designation = Designation::where('name', $designationName)->first();
            if ($designation) {
                OvertimeRate::updateOrCreate(
                    ['designation_id' => $designation->id, 'grade_id' => null],
                    ['rate' => $rate]
                );
            }
        }

        // OT Eligible Designations
        $eligibleKeywords = [
            'Technician', 'Driver', 'Peon', 'Cleaner', 'Line Man', 'Electrician', 'Technical Officer',
            'Assistant Engineer', 'Engineer', 'Office Assistant', 'Executive', 
        ];

        Designation::query()->update(['is_ot_eligible' => false]);

        Designation::where(function($query) use ($eligibleKeywords) {
            foreach ($eligibleKeywords as $keyword) {
                $query->orWhere('name', 'like', '%' . $keyword . '%');
            }
        })->update(['is_ot_eligible' => true]);
    }
}
