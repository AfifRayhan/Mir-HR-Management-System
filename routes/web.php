<?php

use App\Http\Controllers\HrDashboardController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\Personnel\EmployeeController;
use App\Http\Controllers\Personnel\DepartmentController;
use App\Http\Controllers\Personnel\SectionController;
use App\Http\Controllers\Personnel\DesignationController;
use App\Http\Controllers\Personnel\GradeController;
use App\Http\Controllers\Settings\OfficeTypeController;
use App\Http\Controllers\Settings\OfficeController;
use App\Http\Controllers\Settings\OfficeTimeController;
use App\Http\Controllers\Settings\HolidayController;
use App\Http\Controllers\Settings\WeeklyHolidayController;
use App\Http\Controllers\Settings\LeaveTypeController;
use App\Http\Controllers\LeaveApplicationController;
use App\Http\Controllers\Personnel\LeaveBalanceController;
use App\Http\Controllers\Personnel\AttendanceController;
use App\Http\Controllers\Settings\NoticeController;
use App\Http\Controllers\Settings\DeviceController;
use App\Http\Controllers\EmployeeAttendanceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RosterController;
use App\Http\Controllers\Roster\RosterTimeController;
use App\Http\Controllers\ReportTemplateController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TeamLead\AttendanceApprovalController;
use App\Http\Controllers\TeamLead\SupervisorRemarkController;
use App\Http\Controllers\Api\WorkingDayController;
use App\Http\Controllers\Api\DeviceLogController;
use App\Http\Controllers\ReportGeneratorController;
use App\Http\Controllers\Personnel\LeaveBalanceReportController;
use App\Http\Controllers\Personnel\OvertimeController;
use App\Http\Controllers\Personnel\OvertimeSettingController;
use App\Http\Controllers\DriverRosterController;

Route::get('/', function () {
    return view('/auth/login');
})->middleware('guest');

Route::get('/hr-dashboard', [HrDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('hr-dashboard');

Route::get('/employee-dashboard', [EmployeeDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('employee-dashboard');

Route::get('/employee-profile', [EmployeeDashboardController::class, 'profile'])
    ->middleware(['auth', 'verified'])
    ->name('employee-profile');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Secure Document Viewing
    Route::get('leave-applications/document/{id}', [LeaveApplicationController::class, 'viewDocument'])->name('leave-applications.view-document');


    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});

// Security management routes (protected by menu-based permission check)
Route::middleware(['auth', 'permission:security'])->prefix('security')->name('security.')->group(function () {
    Route::resource('users', UserController::class)->except(['show', 'create', 'edit']);
    Route::resource('roles', RoleController::class)->except(['show', 'create', 'edit']);

    Route::get('role-permissions', [RolePermissionController::class, 'index'])->name('role-permissions.index');
    Route::get('role-permissions/tree', [RolePermissionController::class, 'tree'])->name('role-permissions.tree');
    Route::put('role-permissions', [RolePermissionController::class, 'update'])->name('role-permissions.update');
});

// Personnel management routes (restricted by menu-based permission check)
Route::middleware(['auth', 'verified', 'permission:personnel'])->prefix('personnel')->name('personnel.')->group(function () {
    Route::get('employees/next-code', [EmployeeController::class, 'getNextCode'])->name('employees.next-code');
    Route::delete('employees/experience/{experience}', [EmployeeController::class, 'destroyExperience'])->name('employees.delete-experience');
      Route::delete('employees/qualification/{qualification}', [EmployeeController::class, 'destroyQualification'])->name('employees.delete-qualification');
      Route::get('employees/{employee}/profile-pdf', [EmployeeController::class, 'profilePdf'])->name('employees.profile-pdf');
      Route::resource('employees', EmployeeController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('sections', SectionController::class);
    Route::resource('designations', DesignationController::class);
    Route::resource('grades', GradeController::class);

    Route::get('leave-applications', [LeaveApplicationController::class, 'indexHR'])->name('leave-applications.index');
    Route::get('leave-applications/history', [LeaveApplicationController::class, 'historyHR'])->name('leave-applications.history');
    Route::put('leave-applications/{leaveApplication}/status', [LeaveApplicationController::class, 'updateStatus'])->name('leave-applications.status');

    Route::get('leave/manual', [LeaveApplicationController::class, 'manualLeave'])->name('leave.manual');
    Route::post('leave/manual', [LeaveApplicationController::class, 'storeManual'])->name('leave.manual.store');

    Route::get('leave-accounts', [LeaveBalanceController::class, 'index'])->name('leave-balances.index');
    Route::get('leave-accounts/existing', [LeaveBalanceController::class, 'existing'])->name('leave-balances.existing');
    Route::post('leave-accounts', [LeaveBalanceController::class, 'store'])->name('leave-balances.store');
    Route::post('leave-accounts/update-bulk', [LeaveBalanceController::class, 'updateBulk'])->name('leave-balances.update-bulk');

    // Attendance routes
    Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('attendances/records', [AttendanceController::class, 'records'])->name('attendances.records');
    Route::post('attendances/process', [AttendanceController::class, 'processLogs'])->name('attendances.process');
    Route::get('attendances/adjust', [AttendanceController::class, 'adjust'])->name('attendances.adjust');
    Route::post('attendances/adjust', [AttendanceController::class, 'storeAdjustment'])->name('attendances.store-adjustment');
    Route::get('attendances/approvals', [AttendanceController::class, 'approvals'])->name('attendances.approvals');
    Route::post('attendances/approvals/{id}/approve', [AttendanceController::class, 'approveAdjustment'])->name('attendances.approve-adjustment');
    Route::post('attendances/approvals/{id}/reject', [AttendanceController::class, 'rejectAdjustment'])->name('attendances.reject-adjustment');

    // Reports & Exports (with rate limiting)
    Route::prefix('reports')->name('reports.')->middleware('throttle:exports')->group(function () {
        // Employee Exports
        Route::get('employees/export/excel', [EmployeeController::class, 'exportExcel'])->name('employees.export.excel');
        Route::get('employees/export/csv', [EmployeeController::class, 'exportCsv'])->name('employees.export.csv');
        Route::get('employees/export/pdf', [EmployeeController::class, 'exportPdf'])->name('employees.export.pdf');
        Route::get('employees/export/word', [EmployeeController::class, 'exportWord'])->name('employees.export.word');
        Route::get('employees/export/preview', [EmployeeController::class, 'exportPreview'])->name('employees.export.preview');

        // Attendance Exports
        Route::get('attendances/export/excel', [AttendanceController::class, 'exportExcel'])->name('attendances.export.excel');
        Route::get('attendances/export/csv', [AttendanceController::class, 'exportCsv'])->name('attendances.export.csv');
        Route::get('attendances/export/pdf', [AttendanceController::class, 'exportPdf'])->name('attendances.export.pdf');
        Route::get('attendances/export/word', [AttendanceController::class, 'exportWord'])->name('attendances.export.word');
        Route::get('attendances/export/preview', [AttendanceController::class, 'exportPreview'])->name('attendances.export.preview');

        // Monthly Attendance Exports
        Route::get('attendances/monthly/export/excel', [AttendanceController::class, 'exportMonthlyExcel'])->name('attendances.monthly.export.excel');
        Route::get('attendances/monthly/export/csv', [AttendanceController::class, 'exportMonthlyCsv'])->name('attendances.monthly.export.csv');
        Route::get('attendances/monthly/export/pdf', [AttendanceController::class, 'exportMonthlyPdf'])->name('attendances.monthly.export.pdf');
        Route::get('attendances/monthly/export/word', [AttendanceController::class, 'exportMonthlyWord'])->name('attendances.monthly.export.word');
        Route::get('attendances/monthly/export/preview', [AttendanceController::class, 'exportMonthlyPreview'])->name('attendances.monthly.export.preview');

        // Yearly Attendance Exports
        Route::get('attendances/yearly/export/excel', [AttendanceController::class, 'exportYearlyExcel'])->name('attendances.yearly.export.excel');
        Route::get('attendances/yearly/export/csv', [AttendanceController::class, 'exportYearlyCsv'])->name('attendances.yearly.export.csv');
        Route::get('attendances/yearly/export/pdf', [AttendanceController::class, 'exportYearlyPdf'])->name('attendances.yearly.export.pdf');
        Route::get('attendances/yearly/export/word', [AttendanceController::class, 'exportYearlyWord'])->name('attendances.yearly.export.word');
        Route::get('attendances/yearly/export/preview', [AttendanceController::class, 'exportYearlyPreview'])->name('attendances.yearly.export.preview');
        
        // Employee Log (Specific Employee Report)
        Route::get('attendances/log/preview', [AttendanceController::class, 'exportLogPreview'])->name('attendances.log.preview');
        Route::get('attendances/log/export/excel', [AttendanceController::class, 'exportLogExcel'])->name('attendances.log.export.excel');
        Route::get('attendances/log/export/csv', [AttendanceController::class, 'exportLogCsv'])->name('attendances.log.export.csv');
        Route::get('attendances/log/export/pdf', [AttendanceController::class, 'exportLogPdf'])->name('attendances.log.export.pdf');
        Route::get('attendances/log/export/word', [AttendanceController::class, 'exportLogWord'])->name('attendances.log.export.word');

        // Leave Balance Report
        Route::get('leave-balance/preview', [LeaveBalanceReportController::class, 'preview'])->name('leave-balance.preview');
        Route::get('leave-balance/export/excel', [LeaveBalanceReportController::class, 'exportExcel'])->name('leave-balance.export.excel');
        Route::get('leave-balance/export/csv', [LeaveBalanceReportController::class, 'exportCsv'])->name('leave-balance.export.csv');
        Route::get('leave-balance/export/pdf', [LeaveBalanceReportController::class, 'exportPdf'])->name('leave-balance.export.pdf');
        Route::get('leave-balance/export/word', [LeaveBalanceReportController::class, 'exportWord'])->name('leave-balance.export.word');

        // Report Generator
        Route::get('generate', [ReportGeneratorController::class, 'index'])->name('generate');
        Route::get('generate/fields', [ReportGeneratorController::class, 'getFields'])->name('generate.fields');
        Route::post('generate/preview', [ReportGeneratorController::class, 'preview'])->name('generate.preview');
        Route::post('generate/pdf', [ReportGeneratorController::class, 'generatePdf'])->name('generate.pdf');
        Route::post('generate/docx', [ReportGeneratorController::class, 'generateDocx'])->name('generate.docx');
    });

    // Report templates routes
    Route::resource('report-templates', ReportTemplateController::class);

});

// Overtime routes — controller handles per-employee authorization internally
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('overtimes/auto-fill', [OvertimeController::class, 'autoFill'])->name('overtimes.auto-fill');
    Route::get('overtimes/export', [OvertimeController::class, 'export'])->name('overtimes.export');
    Route::get('overtimes', [OvertimeController::class, 'index'])->name('overtimes.index');
    Route::post('overtimes/save', [OvertimeController::class, 'save'])->name('overtimes.save');
});

// Overtime settings — restricted to users with settings permission
Route::middleware(['auth', 'verified', 'permission:settings'])->group(function () {
    Route::get('overtimes/settings', [OvertimeSettingController::class, 'index'])->name('overtimes.settings');
    Route::post('overtimes/settings', [OvertimeSettingController::class, 'store'])->name('overtimes.settings.save');
});

// Employee specific routes
Route::middleware(['auth', 'verified'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('attendance', [EmployeeAttendanceController::class, 'index'])->name('attendance.index');
    Route::get('attendance/adjust', [EmployeeAttendanceController::class, 'adjust'])->name('attendance.adjust');
    Route::post('attendance/adjust', [EmployeeAttendanceController::class, 'storeAdjustment'])->name('attendance.store-adjustment');
    Route::get('leave', [LeaveApplicationController::class, 'indexEmployee'])->name('leave.index');
    Route::post('leave', [LeaveApplicationController::class, 'store'])->name('leave.store');
    Route::get('roster/download', [EmployeeDashboardController::class, 'downloadRoster'])->name('roster.download');
});

// Team Lead specific routes
Route::middleware(['auth', 'verified'])->prefix('team-lead')->name('team-lead.')->group(function () {
    Route::get('leave', [LeaveApplicationController::class, 'indexTeamLeadSelf'])->name('leave.index');
    Route::post('leave', [LeaveApplicationController::class, 'store'])->name('leave.store');

    Route::get('leave-applications', [LeaveApplicationController::class, 'indexTeamLead'])->name('leave-applications.index');
    Route::get('leave-applications/history', [LeaveApplicationController::class, 'historyTeamLead'])->name('leave-applications.history');
    Route::put('leave-applications/{leaveApplication}/status', [LeaveApplicationController::class, 'updateStatusTeamLead'])->name('leave-applications.status');

    Route::get('attendances/approvals', [AttendanceApprovalController::class, 'index'])->name('attendances.approvals');
    Route::post('attendances/approvals/{id}/approve', [AttendanceApprovalController::class, 'approve'])->name('attendances.approve');
    Route::post('attendances/approvals/{id}/reject', [AttendanceApprovalController::class, 'reject'])->name('attendances.reject');
    
    Route::get('remarks', [SupervisorRemarkController::class, 'index'])->name('remarks.index');
    Route::get('remarks/create', [SupervisorRemarkController::class, 'create'])->name('remarks.create');
    Route::post('remarks', [SupervisorRemarkController::class, 'store'])->name('remarks.store');
    Route::delete('remarks/{remark}', [SupervisorRemarkController::class, 'destroy'])->name('remarks.destroy');
});

// Settings management routes
Route::middleware(['auth', 'verified', 'permission:settings'])->prefix('settings')->name('settings.')->group(function () {
    Route::resource('office-types', OfficeTypeController::class);
    Route::resource('offices', OfficeController::class);
    Route::resource('office-times', OfficeTimeController::class)->except(['create', 'edit', 'show']);
    Route::resource('devices', DeviceController::class);
    Route::resource('leave-types', LeaveTypeController::class)->except(['show', 'create', 'edit']);
    Route::resource('notices', NoticeController::class)->except(['create', 'edit']);

    // Holiday configuration routes
    Route::prefix('holidays')->name('holidays.')->group(function () {
        Route::get('weekly', [WeeklyHolidayController::class, 'index'])->name('weekly.index');
        Route::put('weekly', [WeeklyHolidayController::class, 'update'])->name('weekly.update');

        Route::get('others', [HolidayController::class, 'index'])->name('others.index');
        Route::post('others', [HolidayController::class, 'store'])->name('others.store');
        Route::put('others/{holiday}', [HolidayController::class, 'update'])->name('others.update');
        Route::delete('others/{holiday}', [HolidayController::class, 'destroy'])->name('others.destroy');
    });
});

// Roster management routes (restricted by menu-based permission check)
Route::middleware(['auth', 'verified', 'permission:roster'])->prefix('roster')->name('roster.')->group(function () {
    Route::get('/', [RosterController::class, 'index'])->name('index');
    Route::post('/save', [RosterController::class, 'save'])->name('save');
    Route::get('/import-previous', [RosterController::class, 'importPrevious'])->name('import-previous');
    Route::get('/employees', [RosterController::class, 'employees'])->name('employees');
    Route::get('/export', [RosterController::class, 'export'])->name('export');

    // Roster Times Management
    Route::resource('times', RosterTimeController::class);
});

// Driver Roster management routes (restricted by menu-based permission check)
Route::middleware(['auth', 'verified', 'permission:driver-roster'])->prefix('driver-roster')->name('driver-roster.')->group(function () {
    Route::get('/', [DriverRosterController::class, 'index'])->name('index');
    Route::post('/save', [DriverRosterController::class, 'save'])->name('save');
    Route::get('/import-previous', [DriverRosterController::class, 'importPrevious'])->name('import-previous');
    Route::get('/employees', [DriverRosterController::class, 'employees'])->name('employees');
    Route::get('/export', [DriverRosterController::class, 'export'])->name('export');

    Route::resource('times', \App\Http\Controllers\Roster\DriverRosterTimeController::class);
});

// Device Sync API (Exempt from CSRF in bootstrap/app.php)
Route::post('api/device/sync', [DeviceLogController::class, 'sync'])->name('api.device.sync');

// Weekly and National holidays lookup for Manual Leave form (authenticated)
Route::get('api/weekly-holidays', function (\Illuminate\Http\Request $request) {
    $officeId = $request->query('office_id');
    $hasOfficeConfig = \App\Models\WeeklyHoliday::where('office_id', $officeId)->exists();
    
    // 1. Weekly Holidays
    $weeklyDays = \App\Models\WeeklyHoliday::where('is_holiday', true)
        ->where(function ($q) use ($hasOfficeConfig, $officeId) {
            if ($hasOfficeConfig) {
                $q->where('office_id', $officeId);
            } else {
                $q->whereNull('office_id');
            }
        })
        ->pluck('day_name');

    // 2. National/Other Holidays (expanded to dates)
    $holidaysInRange = \App\Models\Holiday::where('is_active', true)
        ->where(function ($q) use ($officeId) {
            $q->where('all_office', true)
                ->orWhere('office_id', $officeId);
        })
        ->get();

    $holidayDates = [];
    foreach ($holidaysInRange as $h) {
        $curr = \Illuminate\Support\Carbon::parse($h->from_date);
        $end = \Illuminate\Support\Carbon::parse($h->to_date);
        while ($curr->lte($end)) {
            $holidayDates[] = $curr->toDateString();
            $curr->addDay();
        }
    }

    return response()->json([
        'holiday_days' => $weeklyDays,
        'national_holidays' => array_unique($holidayDates)
    ]);
})->middleware('auth')->name('api.weekly-holidays');

// Get actual working days count (incorporating roster logic) for Leave Duration calculation
Route::get('api/check-working-days', [WorkingDayController::class, 'calculate'])
    ->middleware('auth')
    ->name('api.check-working-days');

require __DIR__ . '/auth.php';
