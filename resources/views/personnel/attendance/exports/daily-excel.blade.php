<table>
    <thead>
        <tr>
            <td rowspan="3"></td> {{-- Logo Space (A1:A3) --}}
            <td colspan="6" style="font-weight: bold; font-size: 16pt; text-align: center;">
                {{ $selectedOffice->name ?? 'The Mir Group' }}
            </td>
        </tr>
        <tr>
            <td colspan="6" style="text-align: center;">House-04, Road-21, Gulshan-1, Dhaka-1212</td>
        </tr>
        <tr>
            <td colspan="6" style="font-weight: bold; font-size: 12pt; text-align: right;">
                DAILY ATTENDANCE REPORT - {{ $date }}
            </td>
        </tr>
        <tr>
            <td style="background-color: #007A10; color: #ffffff; font-weight: bold; border: 1px solid #000000; text-align: center;">Employee</td>
            <td style="background-color: #007A10; color: #ffffff; font-weight: bold; border: 1px solid #000000; text-align: center;">Designation</td>
            <td style="background-color: #007A10; color: #ffffff; font-weight: bold; border: 1px solid #000000; text-align: center;">In Time</td>
            <td style="background-color: #007A10; color: #ffffff; font-weight: bold; border: 1px solid #000000; text-align: center;">Out Time</td>
            <td style="background-color: #007A10; color: #ffffff; font-weight: bold; border: 1px solid #000000; text-align: center;">Working Hours</td>
            <td style="background-color: #007A10; color: #ffffff; font-weight: bold; border: 1px solid #000000; text-align: center;">Late (H:M:S)</td>
            <td style="background-color: #007A10; color: #ffffff; font-weight: bold; border: 1px solid #000000; text-align: center;">Status</td>
        </tr>
    </thead>
    <tbody>
        @php
            $groupedRecords = $records->groupBy(fn($r) => $r->employee->office->name ?? 'Unassigned');
        @endphp

        @foreach($groupedRecords as $officeName => $officeRecords)
            <tr>
                <td colspan="7" style="background-color: #000000; color: #ffffff; font-weight: bold; vertical-align: middle;">
                    Office: {{ $officeName }}
                </td>
            </tr>
            @php
                $deptGrouped = $officeRecords->groupBy(fn($r) => $r->employee->department->name ?? 'Unassigned');
            @endphp
            @foreach($deptGrouped as $deptName => $deptRecords)
                <tr>
                    <td colspan="7" style="background-color: #f8fafc; color: #000000; font-weight: bold; border-top: 1px solid #000000; border-bottom: 1px solid #000000;">
                        Department: {{ $deptName }} ({{ $deptRecords->count() }})
                    </td>
                </tr>
                @foreach($deptRecords as $record)
                    <tr>
                        <td style="border: 1px solid #dee2e6;">
                            {{ $record->employee->name }} ({{ $record->employee->employee_code }})
                        </td>
                        <td style="border: 1px solid #dee2e6;">
                            {{ $record->employee->designation->name ?? 'N/A' }}
                        </td>
                        <td style="border: 1px solid #dee2e6; text-align: center;">
                            {{ $record->in_time ? $record->in_time->format('h:i A') : '-' }}
                        </td>
                        <td style="border: 1px solid #dee2e6; text-align: center;">
                            {{ $record->out_time ? $record->out_time->format('h:i A') : '-' }}
                        </td>
                        <td style="border: 1px solid #dee2e6; text-align: center;">
                            {{ $record->working_hours }}h
                        </td>
                        <td style="border: 1px solid #dee2e6; text-align: center;">
                            {{ $record->late_timing }}
                        </td>
                        <td style="border: 1px solid #dee2e6; text-align: center;">
                            {{ ucfirst($record->status) }}
                        </td>
                    </tr>
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table>
