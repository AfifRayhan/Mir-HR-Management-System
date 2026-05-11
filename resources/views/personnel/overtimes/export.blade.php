<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Overtime Payment Request Form</title>
    <style>
        @page { size: A4 portrait; margin: 10mm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9pt; color: #333; line-height: 1.2; }
        .header-table { width: 100%; border: none; border-collapse: collapse; margin-bottom: 5px; }
        .logo { width: 120px; height: auto; }
        .company-name { font-size: 18pt; font-weight: bold; color: #4b7a3a; margin: 0; text-align: center; }
        
        .form-title-container { text-align: center; margin-bottom: 10px; border: 1px solid #000; padding: 5px; background-color: #f9f9f9; }
        .form-title { font-size: 12pt; font-weight: bold; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; border: 1px solid #000; }
        .info-table td { padding: 5px; border: 1px solid #000; font-size: 9pt; }
        .info-label { font-weight: bold; width: 15%; background-color: #fdfdfd; }
        .info-value { width: 35%; background-color: #ffffcc; font-weight: bold; }
        
        .report-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .report-table th { border: 1px solid #000; padding: 5px; text-align: center; font-size: 8pt; font-weight: bold; background-color: #f2f2f2; }
        .report-table td { border: 1px solid #000; padding: 4px; font-size: 8pt; text-align: center; }
        .bg-dayoff { background-color: #fff2f2; }
        
        .footer-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .footer-table td { border: 1px solid #000; padding: 4px; font-size: 8pt; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        
        .signature-section { width: 100%; margin-top: 50px; }
        .signature-table { width: 100%; border-collapse: collapse; }
        .signature-table td { text-align: center; width: 33.33%; padding-top: 40px; font-size: 9pt; }
        .sig-name { font-weight: bold; margin-bottom: 5px; border-top: 1px solid #000; display: inline-block; width: 80%; padding-top: 5px; }
        .sig-label { font-size: 8pt; }
    </style>
</head>
<body>
    <div style="text-align: center; margin-bottom: 10px;">
        @php
            $logo = $employee->office->logo ?? 'images/MIRORIGINAL.jpeg';
            $logoPath = public_path($logo);
            if (!file_exists($logoPath)) {
                $logoPath = public_path('images/MIRORIGINAL.jpeg');
            }
            $logoData = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
        @endphp
        @if($logoData)
            <img src="{{ $logoData }}" class="logo">
        @else
            <h1 class="company-name">{{ $employee->office->name ?? 'Mir Telecom Ltd.' }}</h1>
        @endif
    </div>

    <table class="info-table">
        <tr>
            <td class="info-label">OT Month:</td>
            <td class="info-value">{{ $monthName }}</td>
            <td class="info-label" style="background-color: #fff;"></td>
            <td class="info-value" style="background-color: #fff;">{{ $employee->name }}</td>
        </tr>
        <tr>
            <td class="info-label">ID :</td>
            <td class="info-value">{{ $employee->employee_code }}</td>
            <td class="info-label" style="background-color: #fff;"></td>
            <td class="info-value" style="background-color: #fff;">{{ $employee->designation->name ?? '' }}</td>
        </tr>
        <tr>
            <td class="info-label">Gross Salary:</td>
            <td class="info-value">{{ number_format($employee->gross_salary, 0) }}</td>
            <td class="info-label" style="background-color: #fff;"></td>
            <td class="info-value" style="background-color: #fff;">{{ $employee->department->name ?? '' }}</td>
        </tr>
    </table>

    <div class="form-title-container">
        <span class="form-title">Over Time Payment Request Form</span>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 15%;">Date</th>
                <th>In</th>
                <th>Out</th>
                <th>Hours</th>
                <th>Workday Duty (+5 hrs)</th>
                <th>Holiday Duty (+5 hrs)</th>
                <th>Eid Special Duty</th>
                <th>Remarks</th>
                <th style="width: 12%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($daysInMonth as $day)
                @php
                    $dateStr = $day->toDateString();
                    $record = $records->get($dateStr);
                    $holiday = $holidays[$dateStr] ?? null;
                    $roster = $rosterSchedules->get($dateStr);
                    
                    $isRosterEmployee = ($employee->officeTime && $employee->officeTime->shift_name === 'Roster');
                    if ($isRosterEmployee) {
                        $isWeeklyOff = ($roster && strtolower($roster->shift_type) === 'off');
                    } else {
                        $isWeeklyOff = in_array($day->format('l'), $weeklyHolidays); 
                    }
                    $isHoliday = (bool)$holiday;
                    $isDayOff = $isWeeklyOff || $isHoliday;
                @endphp
                <tr class="{{ $isDayOff ? 'bg-dayoff' : '' }}">
                    <td class="fw-bold">{{ $day->format('l, M d') }}</td>
                    <td>{{ $record ? $record->ot_start : '' }}</td>
                    <td>{{ $record ? $record->ot_stop : '' }}</td>
                    <td>{{ $record ? number_format($record->total_ot_hours, 2) : '0.00' }}</td>
                    <td>{{ ($record && $record->is_workday_duty_plus_5) ? 'Yes' : '' }}</td>
                    <td>{{ ($record && $record->is_holiday_duty_plus_5) ? 'Yes' : '' }}</td>
                    <td>{{ ($record && $record->is_eid_duty) ? 'Yes' : '' }}</td>
                    <td>{{ $record ? $record->remarks : '' }}</td>
                    <td class="text-end fw-bold">{{ $record ? number_format($record->amount, 2) : '0.00' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @php
                $gross = $employee->gross_salary;
                $basic = $gross * 0.6;
                $perDay = $basic / 30;
            @endphp
            <tr>
                <td class="fw-bold">Gross Salary:</td>
                <td class="text-end">{{ number_format($gross, 0) }}</td>
                <td class="text-end fw-bold">Total hours/Shift</td>
                <td class="text-center fw-bold">{{ number_format($hourlyOTHours, 2) }}</td>
                <td class="text-center fw-bold">{{ $workdayCount }}</td>
                <td class="text-center fw-bold">{{ $holidayCount }}</td>
                <td class="text-center fw-bold">{{ $eidCountDisplay }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="fw-bold">Basic Salary</td>
                <td class="text-end">{{ number_format($basic, 2) }}</td>
                <td class="text-end text-muted">Rate per hour/Shift/ Eid Special</td>
                <td class="text-center">{{ number_format($perHourRate, 2) }}</td>
                <td class="text-center">{{ number_format($incomeBase, 2) }}</td>
                <td class="text-center">{{ number_format($incomeBase, 2) }}</td>
                <td class="text-center">{{ number_format($incomeBase, 2) }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="fw-bold">Per Day</td>
                <td class="text-end">{{ number_format($perDay, 2) }}</td>
                <td class="text-end text-muted">Multiplying Factor</td>
                <td class="text-center">1</td>
                <td class="text-center">2</td>
                <td class="text-center">2</td>
                <td class="text-center">3</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="fw-bold">Per Hour</td>
                <td class="text-end">{{ number_format($perHourRate, 2) }}</td>
                <td class="text-end fw-bold">Sub-Total</td>
                <td class="text-center fw-bold">{{ number_format($perHourRate * $hourlyUnits, 2) }}</td>
                <td class="text-center fw-bold">{{ number_format($incomeBase * $workdayUnits, 2) }}</td>
                <td class="text-center fw-bold">{{ number_format($incomeBase * $holidayUnits, 2) }}</td>
                <td class="text-center fw-bold">{{ number_format($incomeBase * $eidUnits, 2) }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr style="background-color: #000; color: #fff;">
                <td colspan="8" class="text-end fw-bold" style="font-size: 10pt;">Total Payable Amount</td>
                <td class="text-end fw-bold" style="font-size: 10pt;">{{ number_format($totalPayable, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    <div class="sig-name">{{ $employee->name }}</div>
                    <div class="sig-label">Payment Requested by</div>
                </td>
                <td>
                    <div class="sig-name">{{ $employee->reportingManager->name ?? 'Supervisor' }}</div>
                    <div class="sig-label">Supervisor</div>
                </td>
                <td>
                    <div class="sig-name">Md. Riaz Mahmud</div>
                    <div class="sig-label">Head of HR & Administration</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
