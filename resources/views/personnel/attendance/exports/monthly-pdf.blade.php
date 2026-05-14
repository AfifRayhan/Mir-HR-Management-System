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
            <td style="width: 33%; vertical-align: top;">
                <div style="display: table;">
                    <div style="display: table-cell; vertical-align: top;">
                        @php
                            $logoPath = public_path('images/MIRORIGINAL.jpeg');
                            if (isset($selectedOffice) && $selectedOffice->logo) {
                                $officeLogo = $selectedOffice->logo;
                                $resolvedLogoPath = \Illuminate\Support\Str::startsWith($officeLogo, 'images/')
                                    ? public_path($officeLogo)
                                    : storage_path('app/public/' . $officeLogo);
                                if (file_exists($resolvedLogoPath)) $logoPath = $resolvedLogoPath;
                            }
                            $logoData = '';
                            if (file_exists($logoPath)) {
                                $mimeType = mime_content_type($logoPath);
                                $logoData = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($logoPath));
                            }
                        @endphp
                        @if($logoData)
                            <img src="{{ $logoData }}" class="logo">
                        @endif
                    </div>
                    <div style="display: table-cell; vertical-align: top; padding-left: 10px;">
                        <div class="company-name">{{ $selectedOffice->name ?? 'The Mir Group' }}</div>
                        <div class="address">House-04, Road-21, Gulshan-1, Dhaka-1212</div>
                    </div>
                </div>
            </td>
            <td style="width: 34%; text-align: center; vertical-align: top;">
                <div style="display: inline-block; font-size: 7.5pt; border: 1px solid #999; padding: 4px 8px; text-align: center;">
                    <div style="font-weight: bold; margin-bottom: 2px;">Attendance Legends</div>
                    <div>P=Present, A=Absent, LP=Late Present</div>
                    <div>L=1d Leave, HL=0.5d Leave, LA=Late Absent, H=Holiday</div>
                </div>
            </td>
            <td style="width: 33%; text-align: right; vertical-align: top;">
                <div class="report-title" style="margin-top: 0; border-bottom: none;">Monthly Attendance Report of {{ $monthName }}, {{ $year }}</div>
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




