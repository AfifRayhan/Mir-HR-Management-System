<table>
    <thead>
        <tr>
            <th colspan="{{ count($shiftTypes) + 2 }}" style="text-align: center; font-size: 16px; font-weight: bold;">
                {{ $groupLabel }} Roster - {{ $monthStart->format('F Y') }}
            </th>
        </tr>
        <tr>
            <th>Date</th>
            <th>Day</th>
            @foreach($shiftTypes as $type => $config)
                <th>{{ $config['label'] }} ({{ $config['time'] }})</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($days as $day)
            @php
                $dateStr = $day->format('Y-m-d');
                $shiftBuckets = [];
                foreach(array_keys($shiftTypes) as $st) { $shiftBuckets[$st] = []; }
                
                foreach ($employees as $emp) {
                    $assigned = $scheduleMap[$emp->id][$dateStr] ?? 'Off';
                    if (isset($shiftBuckets[$assigned])) {
                        $shiftBuckets[$assigned][] = $emp->name;
                    } else {
                        $shiftBuckets['Off'][] = $emp->name;
                    }
                }
            @endphp
            <tr>
                <td>{{ $day->format('d M, Y') }}</td>
                <td>{{ $day->format('l') }}</td>
                @foreach($shiftTypes as $shift => $config)
                    <td>{{ implode(', ', $shiftBuckets[$shift]) }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>




