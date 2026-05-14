<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\ManualAttendanceAdjustment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualAttendanceAdjustmentOvernightTest extends TestCase
{
    use RefreshDatabase;

    public function test_personnel_adjustment_allows_overnight_out_time()
    {
        $this->withoutMiddleware();

        $user = User::create([
            'name' => 'HR Admin',
            'email' => 'hr@example.com',
            'password' => bcrypt('password'),
        ]);

        $employee = Employee::create([
            'name' => 'Night Shift Employee',
            'employee_code' => 'NS001',
            'email' => 'night@example.com',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('personnel.attendances.store-adjustment'), [
            'employee_id' => $employee->id,
            'date' => '2026-05-14',
            'in_time' => '22:00',
            'out_time' => '06:00',
            'reason' => 'Overnight shift',
        ]);

        $response->assertRedirect(route('personnel.attendances.index', ['date' => '2026-05-14']));

        $adjustment = ManualAttendanceAdjustment::where('employee_id', $employee->id)->first();
        $record = AttendanceRecord::where('employee_id', $employee->id)->whereDate('date', '2026-05-14')->first();

        $this->assertNotNull($adjustment);
        $this->assertEquals('2026-05-14 22:00:00', $adjustment->in_time->toDateTimeString());
        $this->assertEquals('2026-05-15 06:00:00', $adjustment->out_time->toDateTimeString());

        $this->assertNotNull($record);
        $this->assertEquals('present', $record->status);
        $this->assertEquals('2026-05-15 06:00:00', $record->out_time->toDateTimeString());
        $this->assertEqualsWithDelta(8.0, (float) $record->working_hours, 0.01);
    }
}
