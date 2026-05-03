<table>
    <tr>
        <th colspan="1">OT Month:</th>
        <th colspan="2">{{ $monthName }}</th>
        <th colspan="1">Employee:</th>
        <th colspan="5">{{ $employee->name }}</th>
    </tr>
    <tr>
        <th colspan="1">ID:</th>
        <th colspan="2">{{ $employee->employee_code }}</th>
        <th colspan="1">Designation:</th>
        <th colspan="5">{{ $employee->designation->name ?? '' }}</th>
    </tr>
    <tr>
        <th colspan="1">Gross Salary:</th>
        <th colspan="2">{{ number_format($employee->gross_salary, 0) }}</th>
        <th colspan="1">Department:</th>
        <th colspan="5">{{ $employee->department->name ?? '' }}</th>
    </tr>
    <tr><th colspan="9"></th></tr>
    <tr><th colspan="9">Over Time Payment Request Form</th></tr>
    <tr><th colspan="9"></th></tr>
    <tr>
        <th>Date</th>
        <th>In</th>
        <th>Out</th>
        <th>Hours</th>
        <th>Workday Duty (+5 hrs)</th>
        <th>Holiday Duty (+5 hrs)</th>
        <th>Eid Special Duty</th>
        <th>Remarks</th>
        <th>Amount</th>
    </tr>
    @foreach($daysInMonth as $day)
        @php
            $dateStr = $day->toDateString();
            $record = $records->get($dateStr);
        @endphp
        <tr>
            <td>{{ $day->format('l, M d') }}</td>
            <td>{{ $record ? $record->ot_start : '' }}</td>
            <td>{{ $record ? $record->ot_stop : '' }}</td>
            <td>{{ $record ? number_format($record->total_ot_hours, 2) : '0.00' }}</td>
            <td>{{ ($record && $record->is_workday_duty_plus_5) ? 'Yes' : '' }}</td>
            <td>{{ ($record && $record->is_holiday_duty_plus_5) ? 'Yes' : '' }}</td>
            <td>{{ ($record && $record->is_eid_duty) ? 'Yes' : '' }}</td>
            <td>{{ $record ? $record->remarks : '' }}</td>
            <td>{{ $record ? number_format($record->amount, 2) : '0.00' }}</td>
        </tr>
    @endforeach
    @php
        $gross = $employee->gross_salary;
        $basic = $gross * 0.6;
        $perDay = $basic / 30;
    @endphp
    <tr>
        <td>Gross Salary:</td>
        <td>{{ number_format($gross, 0) }}</td>
        <td>Total hours/Shift</td>
        <td>{{ number_format($hourlyOTHours, 2) }}</td>
        <td>{{ $workdayCount }}</td>
        <td>{{ $holidayCount }}</td>
        <td>{{ $eidCountDisplay }}</td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>Basic Salary</td>
        <td>{{ number_format($basic, 2) }}</td>
        <td>Rate per hour/Shift/ Eid Special</td>
        <td>{{ number_format($perHourRate, 2) }}</td>
        <td>{{ number_format($incomeBase, 2) }}</td>
        <td>{{ number_format($incomeBase, 2) }}</td>
        <td>{{ number_format($incomeBase, 2) }}</td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>Per Day</td>
        <td>{{ number_format($perDay, 2) }}</td>
        <td>Multiplying Factor</td>
        <td>1</td>
        <td>2</td>
        <td>2</td>
        <td>3</td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>Per Hour</td>
        <td>{{ number_format($perHourRate, 2) }}</td>
        <td>Sub-Total</td>
        <td>{{ number_format($perHourRate * $hourlyUnits, 2) }}</td>
        <td>{{ number_format($incomeBase * $workdayUnits, 2) }}</td>
        <td>{{ number_format($incomeBase * $holidayUnits, 2) }}</td>
        <td>{{ number_format($incomeBase * $eidUnits, 2) }}</td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="8">Total Payable Amount</td>
        <td>{{ number_format($totalPayable, 2) }}</td>
    </tr>
    <tr><td colspan="9"></td></tr>
    <tr><td colspan="9"></td></tr>
    <tr><td colspan="9"></td></tr>
    <tr>
        <td colspan="3">{{ $employee->name }}</td>
        <td colspan="3">{{ $employee->reportingManager->name ?? 'Supervisor' }}</td>
        <td colspan="3">Md. Riaz Mahmud</td>
    </tr>
    <tr>
        <td colspan="3">Payment Requested by</td>
        <td colspan="3">Supervisor</td>
        <td colspan="3">Head of HR & Administration</td>
    </tr>
</table>
