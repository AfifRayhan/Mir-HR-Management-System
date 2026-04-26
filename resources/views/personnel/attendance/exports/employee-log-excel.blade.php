<table>
    <thead>
        <tr>
            <td rowspan="3" colspan="2"></td> {{-- Logo Space --}}
            <td colspan="5" style="font-size: 18pt; font-weight: bold; text-align: left; vertical-align: bottom;">Mir Telecom Ltd.</td>
        </tr>
        <tr>
            <td colspan="5" style="font-size: 11pt; text-align: left; vertical-align: top;">House-04, Road-21, Gulshan-1, Dhaka-1212</td>
        </tr>
        <tr>
            <td colspan="5"></td>
        </tr>
        <tr>
            <td colspan="7" style="font-size: 14pt; font-weight: bold; text-align: right; border-bottom: 2px solid #007A10;">EMPLOYEE ATTENDANCE LOG</td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: right;">Period: {{ $fromDate }} to {{ $toDate }}</td>
        </tr>
        <tr><td colspan="7"></td></tr>
        <tr>
            <td colspan="2"><strong>Employee:</strong></td>
            <td colspan="5">{{ $employee->name }} ({{ $employee->employee_code }})</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Department:</strong></td>
            <td colspan="2">{{ $employee->department->name ?? 'N/A' }}</td>
            <td colspan="1"><strong>Designation:</strong></td>
            <td colspan="2">{{ $employee->designation->name ?? 'N/A' }}</td>
        </tr>
        <tr><td colspan="7"></td></tr>
    </thead>
    <thead>
        <tr style="background-color: #007A10; color: #ffffff;">
            <th style="font-weight: bold; text-align: center; border: 1px solid #005a0b;">Date</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #005a0b;">Day</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #005a0b;">In Time</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #005a0b;">Out Time</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #005a0b;">Working Hours</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #005a0b;">Late (H:M:S)</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #005a0b;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $record)
            <tr>
                <td style="text-align: center; border: 1px solid #dee2e6;">{{ $record->date->format('Y-m-d') }}</td>
                <td style="text-align: center; border: 1px solid #dee2e6;">{{ $record->date->format('l') }}</td>
                <td style="text-align: center; border: 1px solid #dee2e6;">{{ $record->in_time ? $record->in_time->format('h:i A') : '-' }}</td>
                <td style="text-align: center; border: 1px solid #dee2e6;">{{ $record->out_time ? $record->out_time->format('h:i A') : '-' }}</td>
                <td style="text-align: center; border: 1px solid #dee2e6;">{{ $record->working_hours ?: '-' }}h</td>
                <td style="text-align: center; border: 1px solid #dee2e6;">{{ $record->late_timing ?: '-' }}</td>
                <td style="text-align: center; border: 1px solid #dee2e6;">{{ ucfirst($record->status) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
