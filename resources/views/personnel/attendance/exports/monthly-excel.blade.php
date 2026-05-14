<table>
    <thead>
        <tr>
            <td rowspan="4" colspan="2" style="vertical-align: middle; text-align: center;"></td> {{-- Logo Space (A1:B4) --}}
            <td colspan="{{ floor(($daysInMonth + 8) / 2) }}" style="font-size: 16pt; font-weight: bold; text-align: left; vertical-align: bottom;">{{ $selectedOffice->name ?? 'The Mir Group' }}</td>
            <td colspan="{{ ceil(($daysInMonth + 8) / 2) }}" style="font-size: 8pt; border: 1px solid #000000; text-align: center; vertical-align: middle;">
                <strong>Attendance Legends</strong><br>
                P=Present, A=Absent, LP=Late Present, L=1d Leave, HL=0.5d Leave, LA=Late Absent, H=Holiday
            </td>
        </tr>
        <tr>
            <td colspan="{{ floor(($daysInMonth + 8) / 2) }}" style="font-size: 10pt; text-align: left; vertical-align: top;">House-04, Road-21, Gulshan-1, Dhaka-1212</td>
            <td colspan="{{ ceil(($daysInMonth + 8) / 2) }}" rowspan="2" style="font-size: 12pt; font-weight: bold; text-align: right; vertical-align: middle;">Monthly Attendance Report of {{ $monthName }}, {{ $year }}</td>
        </tr>
        <tr>
            <td colspan="{{ floor(($daysInMonth + 8) / 2) }}"></td>
        </tr>
        <tr>
            <td colspan="{{ $daysInMonth + 8 }}"></td>
        </tr>
    </thead>
    
    <thead>
        <tr style="background-color: #007A10; color: #ffffff;">
            <th rowspan="2" style="width: 50px; font-weight: bold; text-align: center; border: 1px solid #005a0b;">Emp Id</th>
            <th rowspan="2" style="width: 150px; font-weight: bold; text-align: center; border: 1px solid #005a0b;">Name</th>
            <th rowspan="2" style="width: 120px; font-weight: bold; text-align: center; border: 1px solid #005a0b;">Designation</th>
            <th colspan="{{ $daysInMonth }}" style="font-weight: bold; text-align: center; border: 1px solid #005a0b;">Days</th>
            <th colspan="7" style="font-weight: bold; text-align: center; border: 1px solid #005a0b;">Summary</th>
        </tr>
        <tr style="background-color: #007A10; color: #ffffff;">
            @for($d = 1; $d <= $daysInMonth; $d++)
                <th style="width: 25px; font-weight: bold; text-align: center; border: 1px solid #005a0b;">{{ $d }}</th>
            @endfor
            <th style="width: 30px; background-color: #fff2cc; color: #000000; font-weight: bold; text-align: center; border: 1px solid #005a0b;">P</th>
            <th style="width: 30px; background-color: #fff2cc; color: #000000; font-weight: bold; text-align: center; border: 1px solid #005a0b;">A</th>
            <th style="width: 30px; background-color: #fff2cc; color: #000000; font-weight: bold; text-align: center; border: 1px solid #005a0b;">LP</th>
            <th style="width: 30px; background-color: #fff2cc; color: #000000; font-weight: bold; text-align: center; border: 1px solid #005a0b;">LA</th>
            <th style="width: 30px; background-color: #fff2cc; color: #000000; font-weight: bold; text-align: center; border: 1px solid #005a0b;">L</th>
            <th style="width: 30px; background-color: #fff2cc; color: #000000; font-weight: bold; text-align: center; border: 1px solid #005a0b;">H</th>
            <th style="width: 40px; background-color: #d9ead3; color: #000000; font-weight: bold; text-align: center; border: 1px solid #005a0b;">WD</th>
        </tr>
    </thead>
    <tbody>
        @foreach($groupedData as $officeName => $departments)
            <tr>
                <td colspan="{{ $daysInMonth + 10 }}" style="background-color: #000000; color: #ffffff; font-weight: bold; text-align: left;">Office: {{ $officeName }}</td>
            </tr>
            @foreach($departments as $deptName => $items)
                <tr>
                    <td colspan="{{ $daysInMonth + 10 }}" style="background-color: #f2f2f2; font-weight: bold; text-align: left;">Department: {{ $deptName }} ({{ count($items) }})</td>
                </tr>
                @foreach($items as $item)
                    <tr>
                        <td style="text-align: center;">{{ $item['employee']->employee_code }}</td>
                        <td style="text-align: left;">{{ $item['employee']->name }}</td>
                        <td style="text-align: left;">{{ $item['employee']->designation->name ?? 'N/A' }}</td>
                        
                        @foreach($item['days'] as $day => $status)
                            @php
                                $color = '#000000';
                                $bg = '#ffffff';
                                if ($status == 'P') $color = '#059669';
                                elseif ($status == 'A') { $color = '#721c24'; $bg = '#f8d7da'; }
                                elseif ($status == 'LP') $color = '#d97706';
                                elseif ($status == 'L') $color = '#2563eb';
                                elseif ($status == 'H') { $color = '#383d41'; $bg = '#e2e3e5'; }
                                $cellStyle = "style=\"text-align: center; color: $color; background-color: $bg; font-weight: bold;\"";
                            @endphp
                            <td {!! $cellStyle !!}>{{ $status }}</td>
                        @endforeach

                        <td style="text-align: center; background-color: #fff2cc; font-weight: bold;">{{ $item['summary']['P'] }}</td>
                        <td style="text-align: center; background-color: #fff2cc; font-weight: bold;">{{ $item['summary']['A'] }}</td>
                        <td style="text-align: center; background-color: #fff2cc; font-weight: bold;">{{ $item['summary']['LP'] }}</td>
                        <td style="text-align: center; background-color: #fff2cc; font-weight: bold;">{{ $item['summary']['LA'] }}</td>
                        <td style="text-align: center; background-color: #fff2cc; font-weight: bold;">{{ $item['summary']['L'] }}</td>
                        <td style="text-align: center; background-color: #fff2cc; font-weight: bold;">{{ $item['summary']['H'] }}</td>
                        <td style="text-align: center; background-color: #d9ead3; font-weight: bold;">{{ $item['summary']['WD'] }}</td>
                    </tr>
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table>




