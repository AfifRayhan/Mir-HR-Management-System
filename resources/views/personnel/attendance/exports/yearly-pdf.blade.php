<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 1cm; }
        body { font-family: 'Helvetica', sans-serif; font-size: 8px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 4px 2px; text-align: center; }
        .company-name { font-size: 16px; font-weight: bold; color: #007a10; text-align: left; }
        .company-address { font-size: 9px; color: #666; text-align: left; margin-bottom: 5px; }
        .report-title { font-size: 12px; font-weight: bold; text-align: right; margin-top: 10px; border-top: 1px solid #eee; padding-top: 5px; }
        .office-header { background-color: #000000; color: #ffffff; font-weight: bold; text-align: left; padding-left: 10px; font-size: 9px; }
        .dept-header { background-color: #f1f5f9; font-weight: bold; text-align: left; padding-left: 20px; font-size: 8.5px; }
        .bg-light { background-color: #f9fafb; }
        .fw-bold { font-weight: bold; }
        .status-p { color: #007a10; }
        .status-a { color: #dc2626; }
        .total-col { background-color: #f1f5f9; font-weight: bold; }
    </style>
</head>
<body>
    <table style="width: 100%; border: none; margin-bottom: 20px;">
        <tr>
            <td style="width: 80px; border: none; text-align: left;">
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
            <td style="border: none; text-align: left; vertical-align: top;">
                <div class="company-name">Mir Telecom Ltd.</div>
                <div class="company-address">House-04, Road-21, Gulshan-1, Dhaka-1212</div>
            </td>
        </tr>
    </table>

    <div class="report-title">Yearly Attendance Report - {{ $year }}</div>

    <table>
        <thead>
            <tr style="background-color: #007a10; color: #ffffff;">
                <th style="width: 50px;">Emp ID</th>
                <th style="width: 120px;">Name</th>
                <th style="width: 100px;">Designation</th>
                <th style="width: 70px;">Month</th>
                <th>P</th>
                <th>A</th>
                <th>LP</th>
                <th>LA</th>
                <th>L</th>
                <th>H</th>
                <th>WD</th>
                <th class="total-col">Total</th>
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
                                <td class="status-a">{{ $s['A'] }}</td>
                                <td>{{ $s['LP'] }}</td>
                                <td>{{ $s['LA'] }}</td>
                                <td>{{ $s['L'] }}</td>
                                <td>{{ $s['H'] }}</td>
                                <td>{{ $s['WD'] }}</td>
                                <td class="total-col">{{ $monthTotal }}</td>
                            </tr>
                        @endfor
                        <tr class="bg-light fw-bold">
                            <td style="text-align: left; padding-left: 5px;">{{ __('YEAR TOTAL') }}</td>
                            <td>{{ $yearP }}</td>
                            <td class="status-a">{{ $yearA }}</td>
                            <td>{{ $yearLP }}</td>
                            <td>{{ $yearLA }}</td>
                            <td>{{ $yearL }}</td>
                            <td>{{ $yearH }}</td>
                            <td>{{ $yearWD }}</td>
                            <td class="total-col">{{ $yearP + $yearLP + $yearL }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>




