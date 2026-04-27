<table>
    <thead>
        <tr>
            <th colspan="4" style="text-align: center; font-size: 16px; font-weight: bold;">
                Monthly Roster: {{ $employee->name }} ({{ $employee->employee_code }})
            </th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: center; font-size: 12px;">
                {{ $monthStart->format('F Y') }} - {{ $employee->roster_group }}
            </th>
        </tr>
        <tr>
            <th>Date</th>
            <th>Day</th>
            <th>Shift</th>
            <th>Timings</th>
        </tr>
    </thead>
    <tbody>
        @php
            $cursor = $monthStart->copy();
        @endphp
        @while($cursor->lte($monthEnd))
            @php
                $dateStr = $cursor->toDateString();
                $assignment = $myRoster[$dateStr] ?? null;
                $shiftKey = $assignment ? $assignment->shift_type : 'Off';
                $def = $shiftDefinitions[$shiftKey] ?? null;
            @endphp
            <tr>
                <td>{{ $cursor->format('d M, Y') }}</td>
                <td>{{ $cursor->format('l') }}</td>
                <td>{{ $def ? $def->display_label : ($shiftKey === 'Off' ? 'Off Day' : $shiftKey) }}</td>
                <td>{{ $def ? ($def->start_time ? \Carbon\Carbon::parse($def->start_time)->format('g:i A') . ' - ' . \Carbon\Carbon::parse($def->end_time)->format('g:i A') : '--') : '--' }}</td>
            </tr>
            @php $cursor->addDay(); @endphp
        @endwhile
    </tbody>
</table>




