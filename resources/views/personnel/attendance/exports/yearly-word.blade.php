<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Yearly Attendance Report</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 10pt; color: #333; }
        .header-table { width: 100%; border: none; margin-bottom: 20px; }
        .logo-cell { width: 80px; }
        .address-cell { padding-left: 20px; vertical-align: top; }
        .company-name { font-size: 16pt; font-weight: bold; color: #000; margin: 0; }
        .company-address { font-size: 10pt; color: #666; margin: 5px 0 0 0; }
        
        .report-title { text-align: right; font-size: 12pt; font-weight: bold; margin-bottom: 10px; }
        
        .report-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .report-table th { background-color: #007A10; color: #ffffff; padding: 8px 4px; text-align: center; font-size: 9pt; border: 1px solid #005a0b; }
        .report-table td { padding: 6px 4px; border: 1px solid #dee2e6; font-size: 8.5pt; vertical-align: middle; text-align: center; }
        
        .office-header { background-color: #000000; color: #ffffff; font-weight: bold; text-align: left !important; padding-left: 10px !important; }
        .dept-header { background-color: #f2f2f2; font-weight: bold; text-align: left !important; padding-left: 15px !important; }
        .bg-light { background-color: #f8f9fa; }
        .fw-bold { font-weight: bold; }
        
        .status-present { color: #007A10; font-weight: bold; }
        .status-absent { color: #dc3545; font-weight: bold; }
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

    <div class="report-title">YEARLY ATTENDANCE REPORT - {{ $year }}</div>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 80px;">Emp ID</th>
                <th style="width: 150px;">Name</th>
                <th style="width: 120px;">Designation</th>
                <th style="width: 80px;">Month</th>
                <th>P</th>
                <th>A</th>
                <th>LP</th>
                <th>LA</th>
                <th>L</th>
                <th>H</th>
                <th>WD</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupedData as $officeName => $departments)
                <tr>
                    <td colspan="12" class="office-header">Office: {{ $officeName }}</td>
                </tr>
                @foreach($departments as $deptName => $employees)
                    <tr>
                        <td colspan="12" class="dept-header">Department: {{ $deptName }}</td>
                    </tr>
                    @foreach($employees as $data)
                        @php
                            $emp = $data['employee'];
                            $summaries = $data['monthlySummaries'];
                            $yearP = 0; $yearA = 0; $yearLP = 0; $yearLA = 0; $yearL = 0; $yearH = 0; $yearWD = 0;
                        @endphp
                        @for($m = 1; $m <= 12; $m++)
                            @php
                                $s = $summaries[$m];
                                $monthTotal = $s['P'] + $s['LP'] + $s['L'];
                                $yearP += $s['P']; $yearA += $s['A']; $yearLP += $s['LP']; $yearLA += $s['LA'];
                                $yearL += $s['L']; $yearH += $s['H']; $yearWD += $s['WD'];
                            @endphp
                            <tr>
                                @if($m == 1)
                                    <td rowspan="13">{{ $emp->employee_code }}</td>
                                    <td rowspan="13" style="text-align: left;">{{ $emp->name }}</td>
                                    <td rowspan="13" style="text-align: left;">{{ $emp->designation->name ?? 'N/A' }}</td>
                                @endif
                                <td style="text-align: left; padding-left: 5px;">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</td>
                                <td>{{ $s['P'] }}</td>
                                <td class="status-absent">{{ $s['A'] }}</td>
                                <td>{{ $s['LP'] }}</td>
                                <td>{{ $s['LA'] }}</td>
                                <td>{{ $s['L'] }}</td>
                                <td>{{ $s['H'] }}</td>
                                <td>{{ $s['WD'] }}</td>
                                <td class="fw-bold">{{ $monthTotal }}</td>
                            </tr>
                        @endfor
                        <tr class="bg-light fw-bold">
                            <td style="text-align: left; padding-left: 5px;">{{ __('YEAR TOTAL') }}</td>
                            <td>{{ $yearP }}</td>
                            <td class="status-absent">{{ $yearA }}</td>
                            <td>{{ $yearLP }}</td>
                            <td>{{ $yearLA }}</td>
                            <td>{{ $yearL }}</td>
                            <td>{{ $yearH }}</td>
                            <td>{{ $yearWD }}</td>
                            <td>{{ $yearP + $yearLP + $yearL }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>
