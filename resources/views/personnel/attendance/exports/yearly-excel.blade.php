<table>
    <thead>
        <tr>
            <td rowspan="4" colspan="2" style="vertical-align: middle; text-align: center;"></td> {{-- Logo Space (A1:B4) --}}
            <td colspan="5" style="font-size: 16pt; font-weight: bold; text-align: left; vertical-align: bottom;">{{ $selectedOffice->name ?? 'The Mir Group' }}</td>
            <td colspan="5" style="font-size: 8pt; border: 1px solid #000000; text-align: center; vertical-align: middle;">
                <strong>Attendance Legends</strong><br>
                P=Present, A=Absent, LP=Late Present<br>
                L=1d Leave, HL=0.5d Leave, LA=Late Absent, H=Holiday
            </td>
        </tr>
        <tr>
            <td colspan="5" style="font-size: 10pt; text-align: left; vertical-align: top;">House-04, Road-21, Gulshan-1, Dhaka-1212</td>
            <td colspan="5" rowspan="2" style="font-size: 12pt; font-weight: bold; text-align: right; vertical-align: middle;">Yearly Attendance Report - {{ $year }}</td>
        </tr>
        <tr>
            <td colspan="5"></td>
        </tr>
        <tr>
            <td colspan="10"></td>
        </tr>
    </thead>
    
    <thead>
        <tr style="background-color: #007a10; color: #ffffff;">
            <th style="width: 80px; font-weight: bold; text-align: center; border: 1px solid #005a0b;">Emp ID</th>
            <th style="width: 150px; font-weight: bold; text-align: center; border: 1px solid #005a0b;">Name</th>
            <th style="width: 120px; font-weight: bold; text-align: center; border: 1px solid #005a0b;">Designation</th>
            <th style="width: 80px; font-weight: bold; text-align: center; border: 1px solid #005a0b;">Month</th>
            <th style="width: 30px; font-weight: bold; text-align: center; border: 1px solid #005a0b;">P</th>
            <th style="width: 30px; font-weight: bold; text-align: center; border: 1px solid #005a0b;">A</th>
            <th style="width: 30px; font-weight: bold; text-align: center; border: 1px solid #005a0b;">LP</th>
            <th style="width: 30px; font-weight: bold; text-align: center; border: 1px solid #005a0b;">LA</th>
            <th style="width: 30px; font-weight: bold; text-align: center; border: 1px solid #005a0b;">L</th>
            <th style="width: 30px; font-weight: bold; text-align: center; border: 1px solid #005a0b;">H</th>
            <th style="width: 40px; font-weight: bold; text-align: center; border: 1px solid #005a0b;">WD</th>
            <th style="width: 50px; font-weight: bold; text-align: center; border: 1px solid #005a0b;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($groupedData as $officeName => $departments)
            <tr>
                <td colspan="12" style="background-color: #000000; color: #ffffff; font-weight: bold; text-align: left;">Office: {{ $officeName }}</td>
            </tr>
            @foreach($departments as $deptName => $employees)
                <tr>
                    <td colspan="12" style="background-color: #f8fafc; color: #000000; font-weight: bold; text-align: left; border-top: 1px solid #000000; border-bottom: 1px solid #000000;">Department: {{ $deptName }} ({{ count($employees) }})</td>
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
                                <td rowspan="13" style="vertical-align: middle; text-align: center; border: 1px solid #dee2e6;">{{ $emp->employee_code }}</td>
                                <td rowspan="13" style="vertical-align: middle; text-align: left; border: 1px solid #dee2e6;">{{ $emp->name }}</td>
                                <td rowspan="13" style="vertical-align: middle; text-align: left; border: 1px solid #dee2e6;">{{ $emp->designation->name ?? 'N/A' }}</td>
                            @endif
                            <td style="text-align: left; padding-left: 10px; border: 1px solid #dee2e6;">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</td>
                            <td style="text-align: center; border: 1px solid #dee2e6;">{{ $s['P'] }}</td>
                            <td style="text-align: center; border: 1px solid #dee2e6;">{{ $s['A'] }}</td>
                            <td style="text-align: center; border: 1px solid #dee2e6;">{{ $s['LP'] }}</td>
                            <td style="text-align: center; border: 1px solid #dee2e6;">{{ $s['LA'] }}</td>
                            <td style="text-align: center; border: 1px solid #dee2e6;">{{ $s['L'] }}</td>
                            <td style="text-align: center; border: 1px solid #dee2e6;">{{ $s['H'] }}</td>
                            <td style="text-align: center; border: 1px solid #dee2e6;">{{ $s['WD'] }}</td>
                            <td style="text-align: center; font-weight: bold; border: 1px solid #dee2e6;">{{ $monthTotal }}</td>
                        </tr>
                    @endfor
                    <tr style="background-color: #f8f9fa; font-weight: bold;">
                        <td style="text-align: left; padding-left: 10px; border: 1px solid #dee2e6;">{{ __('YEAR TOTAL') }}</td>
                        <td style="text-align: center; border: 1px solid #dee2e6;">{{ $yearP }}</td>
                        <td style="text-align: center; border: 1px solid #dee2e6;">{{ $yearA }}</td>
                        <td style="text-align: center; border: 1px solid #dee2e6;">{{ $yearLP }}</td>
                        <td style="text-align: center; border: 1px solid #dee2e6;">{{ $yearLA }}</td>
                        <td style="text-align: center; border: 1px solid #dee2e6;">{{ $yearL }}</td>
                        <td style="text-align: center; border: 1px solid #dee2e6;">{{ $yearH }}</td>
                        <td style="text-align: center; border: 1px solid #dee2e6;">{{ $yearWD }}</td>
                        <td style="text-align: center; border: 1px solid #dee2e6;">{{ $yearP + $yearLP + $yearL }}</td>
                    </tr>
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table>
