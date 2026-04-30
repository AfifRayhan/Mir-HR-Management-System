<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Grade;
use App\Models\Overtime;
use App\Models\OvertimeRate;
use App\Models\Designation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OvertimeTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $employee;
    protected $grade;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'HR Admin', 'description' => 'HR Administrator']);
        $this->admin = User::factory()->create(['role_id' => $adminRole->id]);
        $this->grade = Grade::create(['name' => 'Technician']);
        
        // Create OT rate for this grade
        OvertimeRate::create([
            'grade_id' => $this->grade->id,
            'rate' => 35
        ]);

        $designation = Designation::create([
            'name' => 'Technician',
            'short_name' => 'TECH',
            'is_ot_eligible' => true
        ]);

        $this->employee = Employee::create([
            'name' => 'Test Employee',
            'employee_code' => 'EMP001',
            'grade_id' => $this->grade->id,
            'designation_id' => $designation->id,
            'gross_salary' => 30000,
            'status' => 'active',
        ]);

        $officeTime = \App\Models\OfficeTime::create([
            'shift_name' => 'General',
            'start_time' => '09:00:00',
            'end_time'   => '17:00:00',
        ]);
        $this->employee->update(['office_time_id' => $officeTime->id]);

        \App\Models\WeeklyHoliday::create([
            'day_name' => 'Friday',
            'is_holiday' => true,
        ]);
    }

    public function test_overtime_index_page_is_accessible()
    {
        $response = $this->actingAs($this->admin)->get(route('overtimes.index'));
        $response->assertStatus(200);
    }

    public function test_overtime_calculation_below_5_hours()
    {
        // Rate 35/hr. 4.5 hours. ≤5 hrs → floor(4.5) × 35 × 1 = 140.
        $data = [
            'employee_id' => $this->employee->id,
            'month' => '04',
            'year' => '2026',
            'ot' => [
                '2026-04-01' => [
                    'start' => '17:00',
                    'stop'  => '21:30', // 4.5 hours
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->post(route('overtimes.save'), $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertDatabaseHas('overtimes', [
            'employee_id'    => $this->employee->id,
            'date'           => '2026-04-01 00:00:00',
            'total_ot_hours' => 4.50,
            'amount'         => 140.00,   // 4 × 35 × 1
        ]);
    }

    public function test_overtime_calculation_above_5_hours_workday()
    {
        // Gross 30000. fullShiftIncome = (30000×0.6)/30 = 600.
        // 6 hours > 5 → day rate. No holiday/eid. 600 × 1 = 600.
        $data = [
            'employee_id' => $this->employee->id,
            'month' => '04',
            'year' => '2026',
            'ot' => [
                '2026-04-02' => [
                    'start'          => '09:00',
                    'stop'           => '15:00',   // 6 hours
                    'workday_plus_5' => 'on',
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->post(route('overtimes.save'), $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertDatabaseHas('overtimes', [
            'employee_id'    => $this->employee->id,
            'date'           => '2026-04-02 00:00:00',
            'total_ot_hours' => 6.00,
            'amount'         => 1200.00,   // fullShiftIncome × 2 × 1
        ]);
    }

    public function test_overtime_calculation_holiday_5_hours_or_less()
    {
        // Rate 35/hr. 5 hours. Tier 1 (≤5h) → floor(5) × 35 = 175.
        // No multiplier for Tier 1.
        $data = [
            'employee_id' => $this->employee->id,
            'month' => '04',
            'year' => '2026',
            'ot' => [
                '2026-04-03' => [
                    'start'          => '09:00',
                    'stop'           => '14:00',   // exactly 5 hours
                    'holiday_plus_5' => 'on',
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->post(route('overtimes.save'), $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertDatabaseHas('overtimes', [
            'employee_id' => $this->employee->id,
            'date'        => '2026-04-03 00:00:00',
            'amount'      => 175.00,   // 5 × 35
        ]);
    }

    public function test_overtime_calculation_holiday_above_5_hours()
    {
        // Gross 30000. fullShiftIncome = 600. 6 hours > 5 → Tier 2 Base. 
        // Amount = fullShiftIncome * 2 = 1200.
        $data = [
            'employee_id' => $this->employee->id,
            'month' => '04',
            'year' => '2026',
            'ot' => [
                '2026-04-05' => [
                    'start'          => '09:00',
                    'stop'           => '15:00',   // 6 hours
                    'holiday_plus_5' => 'on',
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->post(route('overtimes.save'), $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertDatabaseHas('overtimes', [
            'employee_id' => $this->employee->id,
            'date'        => '2026-04-05 00:00:00',
            'amount'      => 1200.00,   // (600 × 2)
        ]);
    }

    public function test_overtime_calculation_holiday_very_long_shift()
    {
        // Gross 30000. fullShiftIncome = 600. 14 hours > 12.
        // Base (2 units) + Long Shift Bonus (1 unit) = 3 units.
        // Amount = 600 * 3 = 1800.
        $data = [
            'employee_id' => $this->employee->id,
            'month' => '04',
            'year' => '2026',
            'ot' => [
                '2026-04-06' => [
                    'start'          => '08:00',
                    'stop'           => '22:00',   // 14 hours
                    'holiday_plus_5' => 'on',
                    'workday_plus_5' => 'on',
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->post(route('overtimes.save'), $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertDatabaseHas('overtimes', [
            'employee_id' => $this->employee->id,
            'date'        => '2026-04-06 00:00:00',
            'amount'      => 1800.00,   // (600 × 3)
        ]);
    }


    public function test_overtime_calculation_eid_duty_above_5_hours()
    {
        // Gross 30000. fullShiftIncome = 600. 6 hours > 5.
        // Base (2 units) + Eid Bonus (4 units) = 6 units.
        // Amount = 600 * 6 = 3600.
        $data = [
            'employee_id' => $this->employee->id,
            'month' => '04',
            'year' => '2026',
            'ot' => [
                '2026-04-07' => [
                    'start'    => '09:00',
                    'stop'     => '15:00', // 6 hours
                    'eid_duty' => 'on',
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->post(route('overtimes.save'), $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertDatabaseHas('overtimes', [
            'employee_id' => $this->employee->id,
            'date'        => '2026-04-07 00:00:00',
            'amount'      => 3600.00,   // (600 × 6)
        ]);
    }

    public function test_overtime_index_exposes_per_hour_rate_to_view()
    {
        // Grade rate = 35. Verify the controller passes it as JS variable perHourRate = 35.
        $response = $this->actingAs($this->admin)
            ->get(route('overtimes.index', [
                'employee_id' => $this->employee->id,
                'month'       => '04',
                'year'        => '2026',
            ]));

        $response->assertStatus(200);
        $response->assertSee('perHourRate    = Number("35")', false);
    }

    public function test_auto_fill_logic_workday()
    {
        // Wednesday (Workday). Shift 09:00-17:00. Attendance 09:00-19:00. OT start should be 17:00.
        \App\Models\AttendanceRecord::create([
            'employee_id' => $this->employee->id,
            'date'        => '2026-04-01',
            'in_time'     => '2026-04-01 09:00:00',
            'out_time'    => '2026-04-01 19:00:00',
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('overtimes.auto-fill', [
            'employee_id' => $this->employee->id,
            'month'       => '04',
            'year'        => '2026',
        ]));

        $response->assertStatus(200);
        $response->assertJsonPath('suggestions.2026-04-01.ot_start', '17:00');
        $response->assertJsonPath('suggestions.2026-04-01.ot_stop', '19:00');
    }

    public function test_auto_fill_logic_offday()
    {
        // Friday (Off-day). Attendance 10:00-14:00. OT start should be 10:00.
        \App\Models\AttendanceRecord::create([
            'employee_id' => $this->employee->id,
            'date'        => '2026-04-03',
            'in_time'     => '2026-04-03 10:00:00',
            'out_time'    => '2026-04-03 14:00:00',
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('overtimes.auto-fill', [
            'employee_id' => $this->employee->id,
            'month'       => '04',
            'year'        => '2026',
        ]));

        $response->assertStatus(200);
        $response->assertJsonPath('suggestions.2026-04-03.ot_start', '10:00');
        $response->assertJsonPath('suggestions.2026-04-03.ot_stop', '14:00');
    }
}
