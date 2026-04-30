<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\User;
use App\Models\Role;
use App\Models\AttendanceRecord;
use App\Models\OfficeTime;
use App\Models\Designation;
use App\Models\Overtime;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OvertimeAutoFillTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsHrAdmin(): self
    {
        $role = Role::create(['name' => 'hr_admin']);
        $user = User::factory()->create(['role_id' => $role->id]);
        return $this->actingAs($user);
    }

    public function test_auto_fill_returns_late_arrival_ot_start(): void
    {
        $this->actingAsHrAdmin();
        $user = auth()->user();

        $officeTime = OfficeTime::create([
            'shift_name' => 'General',
            'short_name' => 'GEN',
            'start_time' => '09:00:00',
            'end_time'   => '17:00:00',  // 8-hour shift
        ]);

        $designation = Designation::create(['name' => 'Engineer', 'is_ot_eligible' => true]);

        $employee = Employee::factory()->create([
            'status'         => 'active',
            'office_time_id' => $officeTime->id,
            'designation_id' => $designation->id,
            'gross_salary'   => 30000,
            'user_id'        => $user->id,
        ]);

        // Late arrival: in 09:30, out 20:00 → ot_start must be 17:30, not 17:00
        AttendanceRecord::create([
            'employee_id'   => $employee->id,
            'date'          => '2026-04-15',
            'in_time'       => '2026-04-15 09:30:00',
            'out_time'      => '2026-04-15 20:00:00',
            'working_hours' => 10.5,
            'status'        => 'late',
        ]);

        $response = $this->getJson(route('overtimes.auto-fill', [
            'employee_id' => $employee->id,
            'month'       => '04',
            'year'        => '2026',
        ]));

        $response->assertOk()->assertJsonStructure(['suggestions']);

        $day = $response->json('suggestions.2026-04-15');
        $this->assertNotNull($day);
        $this->assertEquals('17:30', $day['ot_start']); // 09:30 + 8h
        $this->assertEquals('20:00', $day['ot_stop']);
        $this->assertEquals(2.5, $day['total_ot_hours']);
    }

    public function test_auto_fill_skips_days_without_overtime(): void
    {
        $this->actingAsHrAdmin();
        
        $officeTime = OfficeTime::create([
            'shift_name' => 'General', 'short_name' => 'GEN',
            'start_time' => '09:00:00', 'end_time'   => '17:00:00',
        ]);
        $designation = Designation::create(['name' => 'Engineer', 'is_ot_eligible' => true]);
        $employee = Employee::factory()->create([
            'status' => 'active', 'office_time_id' => $officeTime->id, 'designation_id' => $designation->id,
        ]);

        // In 09:30, out 17:10 → ot_start would be 17:30 → no OT (left early)
        AttendanceRecord::create([
            'employee_id' => $employee->id, 'date' => '2026-04-10',
            'in_time' => '2026-04-10 09:30:00', 'out_time' => '2026-04-10 17:10:00',
            'working_hours' => 7.67, 'status' => 'late',
        ]);

        $response = $this->getJson(route('overtimes.auto-fill', [
            'employee_id' => $employee->id, 'month' => '04', 'year' => '2026',
        ]));

        $response->assertOk();
        $this->assertArrayNotHasKey('2026-04-10', $response->json('suggestions'));
    }

    public function test_auto_fill_skips_days_with_existing_ot_record(): void
    {
        $this->actingAsHrAdmin();
        $user = auth()->user();

        $officeTime = OfficeTime::create([
            'shift_name' => 'General', 'short_name' => 'GEN',
            'start_time' => '09:00:00', 'end_time'   => '17:00:00',
        ]);
        $designation = Designation::create(['name' => 'Engineer', 'is_ot_eligible' => true]);
        $employee = Employee::factory()->create([
            'status' => 'active', 'office_time_id' => $officeTime->id, 'designation_id' => $designation->id,
        ]);

        AttendanceRecord::create([
            'employee_id' => $employee->id, 'date' => '2026-04-05',
            'in_time' => '2026-04-05 09:00:00', 'out_time' => '2026-04-05 20:00:00',
            'working_hours' => 11, 'status' => 'present',
        ]);

        Overtime::create([
            'employee_id' => $employee->id, 'date' => '2026-04-05',
            'ot_start' => '18:00', 'ot_stop' => '20:00',
            'total_ot_hours' => 2, 'created_by' => $user->id,
        ]);

        $response = $this->getJson(route('overtimes.auto-fill', [
            'employee_id' => $employee->id, 'month' => '04', 'year' => '2026',
        ]));

        $response->assertOk();
        $this->assertArrayNotHasKey('2026-04-05', $response->json('suggestions'));
    }
}
