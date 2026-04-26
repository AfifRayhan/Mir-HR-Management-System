<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { 
            size: A3 landscape; 
            margin: 5mm; 
        }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 8pt; 
            color: #333;
            line-height: 1.1;
        }
        .header-table { width: 100%; border: none; margin-bottom: 8px; border-collapse: collapse; }
        .logo { width: 55px; height: auto; }
        .company-name { font-size: 15pt; font-weight: bold; color: #000; margin-bottom: 1px; }
        .address { font-size: 8.5pt; color: #555; }
        .legend-table { border: 1px solid #999; font-size: 7pt; padding: 4px; background: #fff; }
        .report-title { font-size: 10.5pt; font-weight: bold; margin-top: 5px; color: #444; border-bottom: 1px solid #ddd; padding-bottom: 3px; }
        
        table.attendance-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.attendance-table th, table.attendance-table td { 
            border: 1px solid #000; 
            padding: 1px; 
            text-align: center; 
            vertical-align: middle;
            font-size: 7.5pt;
        }
        .bg-gray { background-color: #007A10; color: #ffffff; font-weight: bold; }
        .bg-summary { background-color: #fff2cc; color: #000; font-weight: bold; }
        .bg-wd { background-color: #d9ead3; color: #000; font-weight: bold; }
        
        .office-header { background-color: #000; color: #fff; text-align: left !important; padding: 3px 8px !important; font-weight: bold; font-size: 9pt; }
        .dept-header { background-color: #f8f9fa; font-weight: bold; text-align: left !important; padding: 2px 15px !important; color: #333; border-top: 1.5px solid #ccc; font-size: 8pt; }
        
        .status-p { color: #000; }
        .status-a { background-color: #f5c6cb; color: #721c24; font-weight: bold; }
        .status-lp { color: #856404; font-weight: bold; }
        .status-l { color: #004085; font-weight: bold; }
        .status-h { background-color: #e2e3e5; color: #383d41; font-weight: bold; }
        
        .text-left { text-align: left !important; padding-left: 3px !important; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 60px; vertical-align: top;">
                @php
                    $path = public_path('images/Mirtel Group Logo .png');
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_exists($path) ? file_get_contents($path) : null;
                    $base64 = $data ? 'data:image/' . $type . ';base64,' . base64_encode($data) : '';
                @endphp
                @if($base64)
                    <img src="{{ $base64 }}" class="logo">
                @endif
            </td>
            <td style="vertical-align: top;">
                <div class="company-name">Mir Telecom Ltd.</div>
                <div class="address">House-04, Road-21, Gulshan-1, Dhaka-1212</div>
                <div class="report-title">Monthly Attendance Report of {{ $monthName }}, {{ $year }}</div>
            </td>
            <td style="width: 200px; text-align: right; vertical-align: top;">
                <table class="legend-table" style="float: right;">
                    <tr><td style="padding: 1px 4px;">P = Present, A = Absent</td></tr>
                    <tr><td style="padding: 1px 4px;">L = 1 day Leave, HL = 0.5 day Leave</td></tr>
                    <tr><td style="padding: 1px 4px;">LP = Late Present</td></tr>
                    <tr><td style="padding: 1px 4px;">LA = Late Absent, H = Holiday</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="attendance-table">
        <thead>
            <tr class="bg-gray">
                <th rowspan="2" style="width: 55px;">Emp Id</th>
                <th rowspan="2" style="width: 130px;">Name</th>
                <th rowspan="2" style="width: 105px;">Designation</th>
                <th colspan="{{ $daysInMonth }}" style="background: #eee;">Days</th>
                <th colspan="7" style="background: #e8d8ae;">Summary</th>
            </tr>
            <tr class="bg-gray">
                @for($d = 1; $d <= $daysInMonth; $d++)
                    <th style="width: 17px;">{{ $d }}</th>
                @endfor
                <th class="bg-summary" style="width: 18px;">P</th>
                <th class="bg-summary" style="width: 18px;">A</th>
                <th class="bg-summary" style="width: 18px;">LP</th>
                <th class="bg-summary" style="width: 18px;">LA</th>
                <th class="bg-summary" style="width: 18px;">L</th>
                <th class="bg-summary" style="width: 18px;">H</th>
                <th class="bg-wd" style="width: 20px;">WD</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupedData as $officeName => $departments)
                <tr>
                    <td colspan="{{ $daysInMonth + 10 }}" class="office-header">Office: {{ $officeName }}</td>
                </tr>
                @foreach($departments as $deptName => $items)
                    <tr>
                        <td colspan="{{ $daysInMonth + 10 }}" class="dept-header">Department: {{ $deptName }} ({{ count($items) }})</td>
                    </tr>
                    @foreach($items as $item)
                        <tr>
                            <td>{{ $item['employee']->employee_code }}</td>
                            <td class="text-left" style="font-size: 7pt;">{{ $item['employee']->name }}</td>
                            <td class="text-left" style="font-size: 7pt;">{{ $item['employee']->designation->name ?? 'N/A' }}</td>
                            
                            @foreach($item['days'] as $day => $status)
                                @php
                                    $class = '';
                                    if ($status == 'P') $class = 'status-p';
                                    elseif ($status == 'A') $class = 'status-a';
                                    elseif ($status == 'LP') $class = 'status-lp';
                                    elseif ($status == 'L') $class = 'status-l';
                                    elseif ($status == 'H') $class = 'status-h';
                                @endphp
                                <td class="{{ $class }}">{{ $status }}</td>
                            @endforeach

                            <td class="bg-summary">{{ $item['summary']['P'] }}</td>
                            <td class="bg-summary">{{ $item['summary']['A'] }}</td>
                            <td class="bg-summary">{{ $item['summary']['LP'] }}</td>
                            <td class="bg-summary">{{ $item['summary']['LA'] }}</td>
                            <td class="bg-summary">{{ $item['summary']['L'] }}</td>
                            <td class="bg-summary">{{ $item['summary']['H'] }}</td>
                            <td class="bg-wd">{{ $item['summary']['WD'] }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>
