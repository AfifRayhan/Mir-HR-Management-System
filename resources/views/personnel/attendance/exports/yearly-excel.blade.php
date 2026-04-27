<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        table { width: 100%; border-collapse: collapse; font-family: sans-serif; font-size: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        .header { text-align: center; margin-bottom: 20px; }
        .logo { width: 150px; }
        .office-header { background-color: #f0f0f0; font-weight: bold; text-align: left; padding-left: 10px; }
        .dept-header { background-color: #f9f9f9; font-weight: bold; text-align: left; padding-left: 20px; }
        .emp-header { background-color: #ffffff; font-weight: bold; text-align: left; padding-left: 5px; }
        .bg-light { background-color: #f8f9fa; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>
<table>
    <thead>
        <tr>
            <td rowspan="3" colspan="2"></td> {{-- Logo Space (A1:B3) --}}
            <td colspan="10" style="font-size: 18pt; font-weight: bold; text-align: left; vertical-align: bottom;">Mir Telecom Ltd.</td>
        </tr>
        <tr>
            <td colspan="10" style="font-size: 11pt; text-align: left; vertical-align: top;">House-04, Road-21, Gulshan-1, Dhaka-1212</td>
        </tr>
        <tr>
            <td colspan="10"></td>
        </tr>
        <tr>
            <td colspan="12" style="font-size: 14pt; font-weight: bold; text-align: center; background-color: #f2f2f2;">Yearly Attendance Report - {{ $year }}</td>
        </tr>
        <tr><td colspan="12"></td></tr>
    </thead>
    
    <thead>
        <tr style="background-color: #007a10; color: #ffffff;">
            <th style="width: 100px; font-weight: bold; text-align: center;">Emp ID</th>
            <th style="width: 200px; font-weight: bold; text-align: center;">Name</th>
            <th style="width: 150px; font-weight: bold; text-align: center;">Designation</th>
            <th style="width: 100px; font-weight: bold; text-align: center;">Month</th>
            <th style="font-weight: bold; text-align: center;">P</th>
            <th style="font-weight: bold; text-align: center;">A</th>
            <th style="font-weight: bold; text-align: center;">LP</th>
            <th style="font-weight: bold; text-align: center;">LA</th>
            <th style="font-weight: bold; text-align: center;">L</th>
            <th style="font-weight: bold; text-align: center;">H</th>
            <th style="font-weight: bold; text-align: center;">WD</th>
            <th style="font-weight: bold; text-align: center;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($groupedData as $officeName => $departments)
            <tr>
                <td colspan="12" style="background-color: #000000; color: #ffffff; font-weight: bold; text-align: left;">Office: {{ $officeName }}</td>
            </tr>
            @foreach($departments as $deptName => $employees)
                <tr>
                    <td colspan="12" style="background-color: #f2f2f2; font-weight: bold; text-align: left;">Department: {{ $deptName }}</td>
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
                                <td rowspan="13" style="vertical-align: middle; text-align: center;">{{ $emp->employee_code }}</td>
                                <td rowspan="13" style="vertical-align: middle; text-align: left;">{{ $emp->name }}</td>
                                <td rowspan="13" style="vertical-align: middle; text-align: left;">{{ $emp->designation->name ?? 'N/A' }}</td>
                            @endif
                            <td style="text-align: left; padding-left: 10px;">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</td>
                            <td style="text-align: center;">{{ $s['P'] }}</td>
                            <td style="text-align: center;">{{ $s['A'] }}</td>
                            <td style="text-align: center;">{{ $s['LP'] }}</td>
                            <td style="text-align: center;">{{ $s['LA'] }}</td>
                            <td style="text-align: center;">{{ $s['L'] }}</td>
                            <td style="text-align: center;">{{ $s['H'] }}</td>
                            <td style="text-align: center;">{{ $s['WD'] }}</td>
                            <td style="text-align: center; font-weight: bold;">{{ $monthTotal }}</td>
                        </tr>
                    @endfor
                    <tr style="background-color: #f8f9fa; font-weight: bold;">
                        <td style="text-align: left; padding-left: 10px;">{{ __('YEAR TOTAL') }}</td>
                        <td style="text-align: center;">{{ $yearP }}</td>
                        <td style="text-align: center;">{{ $yearA }}</td>
                        <td style="text-align: center;">{{ $yearLP }}</td>
                        <td style="text-align: center;">{{ $yearLA }}</td>
                        <td style="text-align: center;">{{ $yearL }}</td>
                        <td style="text-align: center;">{{ $yearH }}</td>
                        <td style="text-align: center;">{{ $yearWD }}</td>
                        <td style="text-align: center;">{{ $yearP + $yearLP + $yearL }}</td>
                    </tr>
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table>
</body>
</html>




