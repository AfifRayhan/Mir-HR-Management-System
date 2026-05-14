<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Attendance Report</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 9pt; color: #333; }
        .header-table { width: 100%; border: none; margin-bottom: 20px; }
        .logo-cell { width: 80px; }
        .address-cell { padding-left: 20px; vertical-align: top; }
        .company-name { font-size: 16pt; font-weight: bold; color: #000; margin: 0; }
        .company-address { font-size: 10pt; color: #666; margin: 5px 0 0 0; }
        
        .report-title { text-align: right; font-size: 12pt; font-weight: bold; margin-bottom: 10px; }
        
        .attendance-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .attendance-table th, .attendance-table td { border: 1px solid #dee2e6; padding: 4px 2px; text-align: center; vertical-align: middle; }
        
        .bg-gray { background-color: #007A10 !important; color: #ffffff !important; font-weight: bold; }
        .bg-summary { background-color: #fff2cc !important; color: #000 !important; font-weight: bold; }
        .bg-wd { background-color: #d9ead3 !important; color: #000 !important; font-weight: bold; }
        
        .office-header { background-color: #000000 !important; color: #ffffff !important; font-weight: bold; text-align: left !important; padding: 8px 10px !important; }
        .dept-header { background-color: #f8fafc !important; color: #000000 !important; font-weight: bold; text-align: left !important; padding: 6px 15px !important; border-top: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6; }
        
        .status-a { color: #dc3545 !important; font-weight: bold; }
        .text-left { text-align: left !important; padding-left: 5px !important; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="logo-cell" style="width: 30%;">
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
                            <img src="{{ $logoData }}" width="55" alt="Logo">
                        @endif
                    </div>
                    <div style="display: table-cell; vertical-align: top; padding-left: 10px;">
                        <div class="company-name">{{ $selectedOffice->name ?? 'The Mir Group' }}</div>
                        <div class="company-address">House-04, Road-21, Gulshan-1, Dhaka-1212</div>
                    </div>
                </div>
            </td>
            <td style="width: 40%; text-align: center; vertical-align: top;">
                <div style="display: inline-block; font-size: 8pt; border: 1px solid #999; padding: 5px 10px; text-align: center;">
                    <div style="font-weight: bold; margin-bottom: 2px;">Attendance Legends</div>
                    <div>P=Present, A=Absent, LP=Late Present</div>
                    <div>L=1d Leave, HL=0.5d Leave, LA=Late Absent, H=Holiday</div>
                </div>
            </td>
            <td style="width: 30%; text-align: right; vertical-align: top;">
                <div class="report-title" style="margin-top: 0;">Monthly Attendance Report of {{ $monthName }}, {{ $year }}</div>
            </td>
        </tr>
    </table>

    <table class="attendance-table">
        <thead>
            <tr class="bg-gray">
                <th rowspan="2" style="width: 60px;">Emp Id</th>
                <th rowspan="2" style="width: 150px;">Name</th>
                <th rowspan="2" style="width: 120px;">Designation</th>
                <th colspan="{{ $daysInMonth }}">Days</th>
                <th colspan="7">Summary</th>
            </tr>
            <tr class="bg-gray">
                @for($d = 1; $d <= $daysInMonth; $d++)
                    <th>{{ $d }}</th>
                @endfor
                <th class="bg-summary">P</th>
                <th class="bg-summary">A</th>
                <th class="bg-summary">LP</th>
                <th class="bg-summary">LA</th>
                <th class="bg-summary">L</th>
                <th class="bg-summary">H</th>
                <th class="bg-wd">WD</th>
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
                            <td class="text-left">{{ $item['employee']->name }}</td>
                            <td class="text-left">{{ $item['employee']->designation->name ?? 'N/A' }}</td>
                            
                            @foreach($item['days'] as $day => $status)
                                <td @if($status == 'A') class="status-a" @endif>{{ $status }}</td>
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




