<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Grade;
use App\Models\Overtime;
use App\Models\OvertimeRate;
use App\Models\Designation;
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
        $this->admin = User::factory()->create();
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
    }

    public function test_overtime_index_page_is_accessible()
    {
        $response = $this->actingAs($this->admin)->get(route('overtimes.index'));
        $response->assertStatus(200);
    }

    public function test_overtime_calculation_below_5_hours()
    {
        // Technician rate is 35tk/hr. 4 hours should be 140.
        $data = [
            'employee_id' => $this->employee->id,
            'month' => '04',
            'year' => '2026',
            'ot' => [
                '2026-04-01' => [
                    'start' => '17:00',
                    'stop' => '21:00',
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->post(route('overtimes.save'), $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);
        
        $this->assertDatabaseHas('overtimes', [
            'employee_id' => $this->employee->id,
            'date' => '2026-04-01 00:00:00',
            'total_ot_hours' => 4.00,
            'amount' => 140.00,
        ]);
    }

    public function test_overtime_calculation_above_5_hours_workday()
    {
        // Gross 30000. Full shift = (30000 * 0.6) / 30 = 600.
        $data = [
            'employee_id' => $this->employee->id,
            'month' => '04',
            'year' => '2026',
            'ot' => [
                '2026-04-02' => [
                    'start' => '09:00',
                    'stop' => '15:00', // 6 hours
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->post(route('overtimes.save'), $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);
        
        $this->assertDatabaseHas('overtimes', [
            'employee_id' => $this->employee->id,
            'date' => '2026-04-02 00:00:00',
            'total_ot_hours' => 6.00,
            'amount' => 600.00,
        ]);
    }

    public function test_overtime_calculation_holiday_plus_5()
    {
        // Gross 30000. Full shift = 600. Holiday plus 5 = 600 * 2 = 1200.
        $data = [
            'employee_id' => $this->employee->id,
            'month' => '04',
            'year' => '2026',
            'ot' => [
                '2026-04-03' => [
                    'start' => '09:00',
                    'stop' => '14:00',
                    'holiday_plus_5' => 'on',
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->post(route('overtimes.save'), $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);
        
        $this->assertDatabaseHas('overtimes', [
            'employee_id' => $this->employee->id,
            'date' => '2026-04-03 00:00:00',
            'amount' => 1200.00,
        ]);
    }

    public function test_overtime_calculation_eid_duty()
    {
        // Gross 30000. Full shift = 600. Eid Special = 600 * 3 = 1800.
        $data = [
            'employee_id' => $this->employee->id,
            'month' => '04',
            'year' => '2026',
            'ot' => [
                '2026-04-04' => [
                    'start' => '09:00',
                    'stop' => '10:00',
                    'eid_duty' => 'on',
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->post(route('overtimes.save'), $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);
        
        $this->assertDatabaseHas('overtimes', [
            'employee_id' => $this->employee->id,
            'date' => '2026-04-04 00:00:00',
            'amount' => 1800.00,
        ]);
    }
}
