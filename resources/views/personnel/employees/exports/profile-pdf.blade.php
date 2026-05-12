<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Profile - {{ $employee->name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #333;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        /* ── Company Header ── */
        .header-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .logo { width: 55px; height: auto; }
        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: #007A10;
            margin: 0;
        }
        .company-address {
            font-size: 9pt;
            color: #555;
            margin: 2px 0 0 0;
        }

        /* ── Employee Top Info ── */
        .employee-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .employee-header-table td {
            vertical-align: top;
            padding: 2px 0;
        }
        .employee-name {
            font-size: 14pt;
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
        }
        .employee-info-label {
            font-weight: bold;
            color: #333;
            width: 110px;
            display: inline-block;
        }
        .photo-box {
            width: 90px;
            height: 100px;
            border: 1px solid #ccc;
            text-align: center;
            vertical-align: top;
        }

        /* ── Section Headers ── */
        .section-header {
            background-color: #007A10;
            color: #fff;
            font-weight: bold;
            font-size: 9pt;
            padding: 5px 10px;
            margin: 12px 0 0 0;
            border-radius: 0;
        }

        /* ── Info Tables (2-column key-value) ── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .info-table td {
            padding: 5px 10px;
            border: 1px solid #ddd;
            font-size: 8.5pt;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            color: #333;
            width: 18%;
            white-space: nowrap;
        }
        .info-value {
            color: #555;
            width: 32%;
        }

        /* ── Full-width row ── */
        .info-table .full-row td {
            width: auto;
        }

        /* ── Data Tables (qualifications, experiences) ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .data-table th {
            background-color: #f2f2f2;
            color: #333;
            padding: 5px 8px;
            text-align: left;
            font-size: 8pt;
            font-weight: bold;
            border: 1px solid #ccc;
        }
        .data-table td {
            padding: 5px 8px;
            border: 1px solid #ddd;
            font-size: 8pt;
            vertical-align: middle;
        }

        /* ── Address section ── */
        .address-section {
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-top: none;
            font-size: 8.5pt;
            color: #555;
        }

        .footer,
        .page-number {
            display: none;
        }

        /* ── Signature Footer (on all pages) ── */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            text-align: right;
            padding-right: 30px;
            height: 16mm;
        }
        .signature-line {
            border-top: 1px solid #333;
            display: inline-block;
            width: 200px;
            text-align: center;
            padding-top: 5px;
            font-size: 9pt;
            font-weight: bold;
            margin-top: 7mm;
        }

        /* ── Page number ── */
        .page-number {
            text-align: center;
            font-size: 8pt;
            color: #999;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            line-height: 16mm;
        }
        .page-number:before {
            content: "Page " counter(page);
        }
    </style>
</head>
<body>
    {{-- ════════════ Fixed Footer (on all pages) ════════════ --}}
    <div class="footer">
        <div class="signature-line">{{ $employee->name }}</div>
    </div>
    <div class="page-number"></div>

    {{-- ════════════ Company Header ════════════ --}}
    <table class="header-table">
        <tr>
            <td style="width: 65px;">
                @php
                    $logoPath = public_path('images/MIRORIGINAL.jpeg');
                    if (isset($office) && $office && $office->logo) {
                        $resolvedLogoPath = \Illuminate\Support\Str::startsWith($office->logo, 'images/')
                            ? public_path($office->logo)
                            : storage_path('app/public/' . $office->logo);

                        if (file_exists($resolvedLogoPath)) {
                            $logoPath = $resolvedLogoPath;
                        }
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
            </td>
            <td>
                <div class="company-name">{{ $office ? $office->name : 'Mir Telecom Ltd.' }}</div>
                <div class="company-address">{{ $office ? $office->address : 'House-04, Road-21, Gulshan-1, Dhaka-1212' }}</div>
            </td>
        </tr>
    </table>

    {{-- ════════════ Employee Name / ID / Contact ════════════ --}}
    <table class="employee-header-table">
        <tr>
            <td style="width: 85%;">
                <div class="employee-name">{{ $employee->name }}</div>
                <table style="border-collapse: collapse; width: 100%;">
                    <tr>
                        <td style="padding: 1px 0; border: none; width: 110px; font-weight: bold; font-size: 9pt;">Employee Id</td>
                        <td style="padding: 1px 0; border: none; font-size: 9pt;">: {{ $employee->employee_code }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; border: none; font-weight: bold; font-size: 9pt;">Contact</td>
                        <td style="padding: 1px 0; border: none; font-size: 9pt;">: {{ $employee->phone }}{{ $employee->contact_no ? '/' . $employee->contact_no : '' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; border: none; font-weight: bold; font-size: 9pt;">Address</td>
                        <td style="padding: 1px 0; border: none; font-size: 9pt;">: {{ $employee->present_address ?? 'N/A' }}</td>
                    </tr>
                    @if($employee->permanent_address && $employee->permanent_address !== $employee->present_address)
                    <tr>
                        <td style="padding: 1px 0; border: none;"></td>
                        <td style="padding: 1px 0; border: none; font-size: 9pt;">: {{ $employee->permanent_address }}</td>
                    </tr>
                    @endif
                </table>
            </td>
            <td style="width: 15%; text-align: right; vertical-align: top;">
                <div class="photo-box">&nbsp;</div>
            </td>
        </tr>
    </table>

    {{-- ════════════ Personal Information ════════════ --}}
    <div class="section-header">Personal Information</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Father Name</td>
            <td class="info-value">: {{ $employee->father_name ?? '' }}</td>
            <td class="info-label">Mother Name</td>
            <td class="info-value">: {{ $employee->mother_name ?? '' }}</td>
        </tr>
        <tr>
            <td class="info-label">Date of Birth</td>
            <td class="info-value">: {{ $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('d-m-Y') : '' }}</td>
            <td class="info-label">Sex</td>
            <td class="info-value">: {{ $employee->gender ?? '' }}</td>
        </tr>
        <tr>
            <td class="info-label">Religion</td>
            <td class="info-value">: {{ $employee->religion ?? '' }}</td>
            <td class="info-label">Nationality</td>
            <td class="info-value">: {{ $employee->nationality ?? '' }}</td>
        </tr>
        <tr>
            <td class="info-label">National Id No</td>
            <td class="info-value">: {{ $employee->national_id ?? '' }}</td>
            <td class="info-label">TIN</td>
            <td class="info-value">: {{ $employee->tin ?? '' }}</td>
        </tr>
        <tr>
            <td class="info-label">Blood Group</td>
            <td class="info-value">: {{ $employee->blood_group ?? '' }}</td>
            <td class="info-label"></td>
            <td class="info-value"></td>
        </tr>
    </table>

    {{-- ════════════ Present Address ════════════ --}}
    <div class="section-header">Present Address</div>
    <div class="address-section">{{ $employee->present_address ?? 'N/A' }}</div>

    {{-- ════════════ Permanent Address ════════════ --}}
    <div class="section-header">Permanent Address</div>
    <div class="address-section">{{ $employee->permanent_address ?? 'N/A' }}</div>

    {{-- ════════════ Marital Status ════════════ --}}
    <div class="section-header">Marital Status</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Marital Status</td>
            <td class="info-value">: {{ $employee->marital_status ?? '' }}</td>
            <td class="info-label">No of Child</td>
            <td class="info-value">: {{ $employee->no_of_children ?? '' }}</td>
        </tr>
    </table>

    {{-- ════════════ Emergency Contact ════════════ --}}
    <div class="section-header">Emergency Contact</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Contact Name</td>
            <td class="info-value">: {{ $employee->emergency_contact_name ?? '' }}</td>
            <td class="info-label">Relation</td>
            <td class="info-value">: {{ $employee->emergency_contact_relation ?? '' }}</td>
        </tr>
        <tr>
            <td class="info-label">Address</td>
            <td class="info-value" colspan="1">: {{ $employee->emergency_contact_address ?? '' }}</td>
            <td class="info-label">Contact No</td>
            <td class="info-value">: {{ $employee->emergency_contact_no ?? '' }}</td>
        </tr>
    </table>

    {{-- ════════════ Current Job Status ════════════ --}}
    <div class="section-header">Current Job Status</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Joining Date</td>
            <td class="info-value">: {{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d-m-Y') : '' }}</td>
            <td class="info-label">Designation</td>
            <td class="info-value">: {{ $employee->designation->name ?? '' }}</td>
        </tr>
        <tr>
            <td class="info-label">Department</td>
            <td class="info-value">: {{ $employee->department->name ?? '' }}</td>
            <td class="info-label">Section</td>
            <td class="info-value">: {{ $employee->section->name ?? '' }}</td>
        </tr>
        <tr>
            <td class="info-label">Office</td>
            <td class="info-value">: {{ $employee->office->name ?? '' }}</td>
            <td class="info-label"></td>
            <td class="info-value"></td>
        </tr>
    </table>

    {{-- ════════════ Academic Qualifications ════════════ --}}
    <div class="section-header">Academic Qualifications</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Institution</th>
                <th>Board</th>
                <th>Group Name</th>
                <th>Year</th>
                <th>Session</th>
                <th>Result</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employee->qualifications as $qual)
            <tr>
                <td>{{ $qual->qualification ?? '' }}</td>
                <td>{{ $qual->institution ?? '' }}</td>
                <td>{{ $qual->board_university ?? '' }}</td>
                <td>{{ $qual->group_major ?? '' }}</td>
                <td>{{ $qual->passing_year ?? '' }}</td>
                <td>{{ $qual->level ?? '' }}</td>
                <td>{{ $qual->result ?? '' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; color: #999;">No qualification records</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ════════════ Page 2: Experiences ════════════ --}}
    @if($employee->experiences && $employee->experiences->count() > 0)
    <div style="page-break-before: always;"></div>

    {{-- Repeat Company Header on Page 2 --}}
    <table class="header-table">
        <tr>
            <td style="width: 65px;">
                @if($logoData)
                    <img src="{{ $logoData }}" class="logo">
                @endif
            </td>
            <td>
                <div class="company-name">{{ $office ? $office->name : 'Mir Telecom Ltd.' }}</div>
                <div class="company-address">{{ $office ? $office->address : 'House-04, Road-21, Gulshan-1, Dhaka-1212' }}</div>
            </td>
        </tr>
    </table>

    <div class="section-header">Experiences</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Organization</th>
                <th>Responsibilities</th>
                <th>Department</th>
                <th>Designation</th>
                <th>Date From</th>
                <th>Date To</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employee->experiences as $exp)
            <tr>
                <td>{{ $exp->organization ?? '' }}</td>
                <td>{{ $exp->responsibilities ?? '' }}</td>
                <td>{{ $exp->department ?? '' }}</td>
                <td>{{ $exp->designation ?? '' }}</td>
                <td>{{ $exp->date_from ?? '' }}</td>
                <td>{{ $exp->date_to ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

</body>
</html>
