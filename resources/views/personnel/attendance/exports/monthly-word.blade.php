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
        
        .bg-gray { background-color: #007A10; color: #ffffff; font-weight: bold; }
        .bg-summary { background-color: #fff2cc; color: #000; font-weight: bold; }
        .bg-wd { background-color: #d9ead3; color: #000; font-weight: bold; }
        
        .office-header { background-color: #000000; color: #ffffff; font-weight: bold; text-align: left !important; padding-left: 10px !important; }
        .dept-header { background-color: #f2f2f2; font-weight: bold; text-align: left !important; padding-left: 15px !important; }
        
        .status-a { color: #dc3545; font-weight: bold; }
        .text-left { text-align: left !important; padding-left: 5px !important; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @php
                    $logoPath = public_path('images/Mirtel Group Logo .png');
                    $logoData = '';
                    if (file_exists($logoPath)) {
                        $logoData = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
                    }
                @endphp
                @if($logoData)
                    <img src="{{ $logoData }}" width="60" alt="Logo">
                @endif
            </td>
            <td class="address-cell">
                <div class="company-name">Mir Telecom Ltd.</div>
                <div class="company-address">House-04, Road-21, Gulshan-1, Dhaka-1212</div>
            </td>
        </tr>
    </table>

    <div class="report-title">MONTHLY ATTENDANCE REPORT - {{ $monthName }}, {{ $year }}</div>

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
