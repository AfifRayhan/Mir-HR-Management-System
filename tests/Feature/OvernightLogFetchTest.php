<?php
namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\RosterSchedule;
use App\Models\RosterTime;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OvernightLogFetchTest extends TestCase
{
    use RefreshDatabase;

    public function test_overnight_shift_fetches_out_time_from_next_day()
    {
        $user = \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $officeTime = \App\Models\OfficeTime::create([
            'shift_name' => 'Roster',
            'start_time' => '10:00:00',
            'end_time'   => '18:00:00',
        ]);

        $employee = \App\Models\Employee::create([
            'name' => 'Test Employee',
            'employee_code' => 'T001',
            'email' => 'test@example.com',
            'roster_group'  => 'NOC (Borak)',
            'office_time_id' => $officeTime->id,
            'status' => 'active'
        ]);

        RosterTime::create([
            'group_slug'    => 'noc-borak',
            'shift_key'     => 'N',
            'display_label' => 'Night',
            'start_time'    => '22:00:00',
            'end_time'      => '06:00:00',
            'badge_class'   => 'badge-dark',
            'is_off_day'    => false,
            'is_overnight'  => true,
            'is_manual'     => false,
        ]);

        RosterSchedule::create([
            'employee_id' => $employee->id,
            'date'        => '2026-04-20',
            'shift_type'  => 'N',
            'created_by'  => $user->id,
        ]);

        // Punch in day-1, punch out day-2
        Attendance::create(['user_id' => 'T001', 'punch_time' => '2026-04-20 22:05:00', 'machine_id' => 1]);
        Attendance::create(['user_id' => 'T001', 'punch_time' => '2026-04-21 06:10:00', 'machine_id' => 1]);

        (new AttendanceService())->processEmployeeAttendance($employee, '2026-04-20');

        $record = \App\Models\AttendanceRecord::where('employee_id', $employee->id)
            ->whereDate('date', '2026-04-20')->first();

        $this->assertNotNull($record);
        $this->assertEquals('present', $record->status);
        $this->assertNotNull($record->out_time);
        $this->assertEquals('2026-04-21', $record->out_time->toDateString());
        // Working hours: 22:05 to 06:10 = 8h 5m = 8.08h
        $this->assertEqualsWithDelta(8.08, (float)$record->working_hours, 0.1);
    }

    public function test_overnight_shift_marks_late_when_punch_in_after_threshold()
    {
        $user = \App\Models\User::create([
            'name' => 'Admin 2',
            'email' => 'admin2@example.com',
            'password' => bcrypt('password'),
        ]);

        $officeTime = \App\Models\OfficeTime::create([
            'shift_name' => 'Roster',
            'start_time' => '10:00:00',
            'end_time'   => '18:00:00',
        ]);

        $employee = \App\Models\Employee::create([
            'name' => 'Test Employee 2',
            'employee_code' => 'T002',
            'email' => 'test2@example.com',
            'roster_group'  => 'NOC (Borak)',
            'office_time_id' => $officeTime->id,
            'status' => 'active'
        ]);

        RosterTime::create([
            'group_slug'    => 'noc-borak',
            'shift_key'     => 'N2',
            'display_label' => 'Night 2',
            'start_time'    => '22:00:00',
            'end_time'      => '06:00:00',
            'badge_class'   => 'badge-dark',
            'is_off_day'    => false,
            'is_overnight'  => true,
            'is_manual'     => false,
        ]);

        RosterSchedule::create([
            'employee_id' => $employee->id,
            'date'        => '2026-04-20',
            'shift_type'  => 'N2',
            'created_by'  => $user->id,
        ]);

        // 70 minutes late (threshold = 23:00, actual = 23:10)
        Attendance::create(['user_id' => 'T002', 'punch_time' => '2026-04-20 23:10:00', 'machine_id' => 1]);
        Attendance::create(['user_id' => 'T002', 'punch_time' => '2026-04-21 06:00:00', 'machine_id' => 1]);

        (new AttendanceService())->processEmployeeAttendance($employee, '2026-04-20');

        $record = \App\Models\AttendanceRecord::where('employee_id', $employee->id)
            ->whereDate('date', '2026-04-20')->first();

        $this->assertNotNull($record);
        $this->assertEquals('late', $record->status);
        $this->assertEquals(600, $record->late_seconds); // 10 min = 600 sec
    }

    public function test_overnight_shift_is_working_day_on_start_date_only()
    {
        $officeTime = \App\Models\OfficeTime::create([
            'shift_name' => 'Roster',
            'start_time' => '10:00:00',
            'end_time'   => '18:00:00',
        ]);

        $employee = \App\Models\Employee::create([
            'name' => 'Test Employee 3',
            'employee_code' => 'T003',
            'roster_group'  => 'NOC (Borak)',
            'office_time_id' => $officeTime->id,
            'status' => 'active'
        ]);

        RosterTime::create([
            'group_slug'    => 'noc-borak',
            'shift_key'     => 'N3',
            'display_label' => 'Night 3',
            'is_off_day'    => false,
            'is_overnight'  => true,
            'is_manual'     => false,
        ]);

        RosterSchedule::create([
            'employee_id' => $employee->id,
            'date'        => '2026-04-20', // Monday
            'shift_type'  => 'N3',
        ]);

        $service = new AttendanceService();
        
        $this->assertTrue($service->isWorkingDay($employee, '2026-04-20'), 'Monday should be a working day');
        $this->assertFalse($service->isWorkingDay($employee, '2026-04-21'), 'Tuesday should NOT be a working day if not rostered');
    }

    public function test_checkout_of_previous_shift_is_not_reused_as_checkin_of_next_day()
    {
        $user = \App\Models\User::create([
            'name' => 'Admin 4',
            'email' => 'admin4@example.com',
            'password' => bcrypt('password'),
        ]);

        $officeTime = \App\Models\OfficeTime::create([
            'shift_name' => 'Roster',
            'start_time' => '10:00:00',
            'end_time'   => '18:00:00',
        ]);

        $employee = \App\Models\Employee::create([
            'name' => 'Test Employee 4',
            'employee_code' => 'T004',
            'email' => 'test4@example.com',
            'roster_group'  => 'NOC (Borak)',
            'office_time_id' => $officeTime->id,
            'status' => 'active'
        ]);

        RosterTime::create([
            'group_slug'    => 'noc-borak',
            'shift_key'     => 'N4',
            'display_label' => 'Night 4',
            'start_time'    => '22:00:00',
            'end_time'      => '06:00:00',
            'is_overnight'  => true,
        ]);

        // Day 1 (Monday) and Day 2 (Tuesday) both have Night Shift (10 PM - 6 AM)
        RosterSchedule::create(['employee_id' => $employee->id, 'date' => '2026-04-20', 'shift_type' => 'N4']);
        RosterSchedule::create(['employee_id' => $employee->id, 'date' => '2026-04-21', 'shift_type' => 'N4']);

        // Logs:
        // Day 1: 10:05 PM (Check-in Day 1)
        // Day 2: 06:10 AM (Check-out Day 1)
        // Day 2: 10:05 PM (Check-in Day 2)
        // Day 3: 06:10 AM (Check-out Day 2)
        Attendance::create(['user_id' => 'T004', 'punch_time' => '2026-04-20 22:05:00', 'machine_id' => 1]);
        Attendance::create(['user_id' => 'T004', 'punch_time' => '2026-04-21 06:10:00', 'machine_id' => 1]);
        Attendance::create(['user_id' => 'T004', 'punch_time' => '2026-04-21 22:05:00', 'machine_id' => 1]);
        Attendance::create(['user_id' => 'T004', 'punch_time' => '2026-04-22 06:10:00', 'machine_id' => 1]);

        $service = new AttendanceService();

        // Process Day 2
        $service->processEmployeeAttendance($employee, '2026-04-21');

        $record = \App\Models\AttendanceRecord::where('employee_id', $employee->id)
            ->whereDate('date', '2026-04-21')->first();

        $this->assertNotNull($record);
        // CURRENT BUG: $record->in_time would be 2026-04-21 06:10:00
        // DESIRED: $record->in_time should be 2026-04-21 22:05:00
        $this->assertEquals('2026-04-21 22:05:00', $record->in_time->toDateTimeString(), 'Check-in for Day 2 should be the 10:05 PM log, not the 6:10 AM log');
    }
}
