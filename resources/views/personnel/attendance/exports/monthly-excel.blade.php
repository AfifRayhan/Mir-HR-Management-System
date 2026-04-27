<table>
    <thead>
        <tr>
            <td rowspan="3" colspan="2"></td> {{-- Logo Space (A1:B3) --}}
            <td colspan="{{ $daysInMonth + 8 }}" style="font-size: 18pt; font-weight: bold; text-align: left; vertical-align: bottom;">Mir Telecom Ltd.</td>
        </tr>
        <tr>
            <td colspan="{{ $daysInMonth + 8 }}" style="font-size: 11pt; text-align: left; vertical-align: top;">House-04, Road-21, Gulshan-1, Dhaka-1212</td>
        </tr>
        <tr>
            <td colspan="{{ $daysInMonth + 8 }}"></td>
        </tr>
        <tr>
            <td colspan="{{ $daysInMonth + 10 }}" style="font-size: 14pt; font-weight: bold; text-align: center; background-color: #f2f2f2;">Monthly Attendance Report of {{ $monthName }}, {{ $year }}</td>
        </tr>
        <tr><td colspan="{{ $daysInMonth + 10 }}"></td></tr>
    </thead>
    
    <thead>
        <tr style="background-color: #007A10; color: #ffffff;">
            <th rowspan="2" style="font-weight: bold; text-align: center; border: 1px solid #005a0b;">Emp Id</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; border: 1px solid #005a0b;">Name</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; border: 1px solid #005a0b;">Designation</th>
            <th colspan="{{ $daysInMonth }}" style="font-weight: bold; text-align: center; border: 1px solid #005a0b;">Days</th>
            <th colspan="7" style="font-weight: bold; text-align: center; border: 1px solid #005a0b;">Summary</th>
        </tr>
        <tr style="background-color: #007A10; color: #ffffff;">
            @for($d = 1; $d <= $daysInMonth; $d++)
                <th style="font-weight: bold; text-align: center; border: 1px solid #005a0b;">{{ $d }}</th>
            @endfor
            <th style="background-color: #fff2cc; color: #000000; font-weight: bold; text-align: center; border: 1px solid #005a0b;">P</th>
            <th style="background-color: #fff2cc; color: #000000; font-weight: bold; text-align: center; border: 1px solid #005a0b;">A</th>
            <th style="background-color: #fff2cc; color: #000000; font-weight: bold; text-align: center; border: 1px solid #005a0b;">LP</th>
            <th style="background-color: #fff2cc; color: #000000; font-weight: bold; text-align: center; border: 1px solid #005a0b;">LA</th>
            <th style="background-color: #fff2cc; color: #000000; font-weight: bold; text-align: center; border: 1px solid #005a0b;">L</th>
            <th style="background-color: #fff2cc; color: #000000; font-weight: bold; text-align: center; border: 1px solid #005a0b;">H</th>
            <th style="background-color: #d9ead3; color: #000000; font-weight: bold; text-align: center; border: 1px solid #005a0b;">WD</th>
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




