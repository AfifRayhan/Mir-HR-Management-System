<?php

use App\Models\Employee;
use App\Models\OfficeTime;
use App\Models\AttendanceRecord;
use App\Services\AttendanceService;
use Carbon\Carbon;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Find the General Shift
$generalShift = OfficeTime::where('shift_name', 'General Shift')->first();

if (!$generalShift) {
    echo "General Shift not found!\n";
    exit(1);
}

// 2. Update Rakib Islam (EMP003)
$rakib = Employee::where('employee_code', 'EMP003')->first();
if ($rakib) {
    $rakib->update(['office_time_id' => $generalShift->id]);
    echo "Updated Rakib's shift to General Shift.\n";

    // 3. Re-process attendance for the current date
    $service = new AttendanceService();
    $date = Carbon::now()->toDateString();

    // Check if he has a record today
    $record = AttendanceRecord::where('employee_id', $rakib->id)->where('date', $date)->first();
    if ($record) {
        echo "Re-processing Rakib's attendance for $date...\n";
        $service->processEmployeeAttendance($rakib, $date);

        $updatedRecord = AttendanceRecord::where('employee_id', $rakib->id)->where('date', $date)->first();
        echo "New Status: " . $updatedRecord->status . "\n";
        echo "Late Minutes: " . $updatedRecord->late_minutes . "\n";
    } else {
        echo "No attendance record found for Rakib today.\n";
    }
} else {
    echo "Rakib (EMP003) not found!\n";
}
