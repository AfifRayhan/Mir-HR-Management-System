<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 1cm; }
        body { font-family: 'Helvetica', sans-serif; font-size: 8px; color: #333; }
        .report-table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        .report-table th, .report-table td { border: 1px solid #999; padding: 2px 1px; text-align: center; word-break: break-all; overflow: hidden; line-height: 1; }
        .company-name { font-size: 16px; font-weight: bold; color: #007a10; text-align: left; }
        .company-address { font-size: 9px; color: #666; text-align: left; margin-bottom: 5px; }
        .report-title { font-size: 12px; font-weight: bold; text-align: right; margin-top: 10px; border-top: 1px solid #eee; padding-top: 5px; }
        .office-header { background-color: #000000; color: #ffffff; font-weight: bold; text-align: left; padding-left: 10px; font-size: 9px; }
        .dept-header { background-color: #f1f5f9; font-weight: bold; text-align: left; padding-left: 20px; font-size: 8.5px; }
        .bg-light { background-color: #f9fafb; }
        .fw-bold { font-weight: bold; }
        .status-p { color: #007a10; }
        .status-a { color: #dc2626; }
        .total-col { background-color: #888888ff; font-weight: bold; }
        tbody { page-break-inside: avoid; }
        thead { display: table-header-group; }
        .rowspan-cell { vertical-align: top !important; padding-top: 10px !important; }
    </style>
</head>
<body>
    <table style="width: 100%; border: none; margin-bottom: 10px;">
        <tr>
            <td style="width: 33%; border: none; text-align: left; vertical-align: top;">
                <div style="display: table;">
                    <div style="display: table-cell; width: 65px; vertical-align: top;">
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
            <td style="width: 34%; border: none; text-align: center; vertical-align: top;">
                <div style="display: inline-block; font-size: 7.5px; border: 1px solid #999; padding: 5px 10px; text-align: center;">
                    <div style="font-weight: bold; margin-bottom: 2px;">Attendance Legends</div>
                    <div>P=Present, A=Absent, LP=Late Present</div>
                    <div>L=1d Leave, HL=0.5d Leave, LA=Late Absent, H=Holiday</div>
                </div>
            </td>
            <td style="width: 33%; border: none; text-align: right; vertical-align: top;">
                <div class="report-title" style="margin-top: 0; border-top: none; padding-top: 0;">Yearly Attendance Report - {{ $year }}</div>
            </td>
        </tr>
    </table>

    <table class="report-table">
        <colgroup>
            <col style="width: 5%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 5%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
        </colgroup>
        <thead>
            <tr style="background-color: #007a10; color: #ffffff;">
                <th>ID</th>
                <th>Name</th>
                <th>Designation</th>
                <th>Mon</th>
                <th>P</th>
                <th>A</th>
                <th>LP</th>
                <th>LA</th>
                <th>L</th>
                <th>H</th>
                <th>WD</th>
                <th class="total-col">Tot</th>
            </tr>
        </thead>
        @foreach($groupedData as $officeName => $departments)
            <tbody>
                <tr style="page-break-inside: avoid;">
                    <td colspan="12" class="office-header">Office: {{ $officeName }}</td>
                </tr>
            </tbody>
            @foreach($departments as $deptName => $employees)
                <tbody>
                    <tr style="page-break-inside: avoid;">
                        <td colspan="12" class="dept-header">Dept: {{ $deptName }}</td>
                    </tr>
                </tbody>
                @foreach($employees as $data)
                    <tbody style="page-break-inside: avoid;">
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
                                    <td rowspan="14" class="rowspan-cell" style="font-size: 6px;">{{ $emp->employee_code }}</td>
                                    <td rowspan="14" class="rowspan-cell" style="text-align: left; padding-left: 3px; font-weight: bold; font-size: 7px;">{{ $emp->name }}</td>
                                    <td rowspan="14" class="rowspan-cell" style="text-align: left; padding-left: 3px; font-size: 7px;">{{ $emp->designation->name ?? 'N/A' }}</td>
                                @endif
                                <td style="text-align: left; padding-left: 2px;">{{ date('M', mktime(0, 0, 0, $m, 1)) }}</td>
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
                            <td style="text-align: left; padding-left: 2px;">{{ __('TOT') }}</td>
                            <td>{{ $yearP }}</td>
                            <td class="status-a">{{ $yearA }}</td>
                            <td>{{ $yearLP }}</td>
                            <td>{{ $yearLA }}</td>
                            <td>{{ $yearL }}</td>
                            <td>{{ $yearH }}</td>
                            <td>{{ $yearWD }}</td>
                            <td class="total-col">{{ $yearP + $yearLP + $yearL }}</td>
                        </tr>
                    </tbody>
                @endforeach
            @endforeach
        @endforeach
    </table>
</body>
</html>




