<x-app-layout>
    @push('styles')
    
    <style>
        .monthly-grid-table {
            font-size: 0.7rem;
            border-collapse: collapse;
            width: 100%;
        }
        .monthly-grid-table th, .monthly-grid-table td {
            border: 1px solid #dee2e6;
            padding: 4px 2px;
            text-align: center;
        }
        .monthly-grid-table thead th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        .status-p { color: #059669; font-weight: bold; } /* Present */
        .status-a { color: #dc2626; font-weight: bold; } /* Absent */
        .status-lp { color: #d97706; font-weight: bold; } /* Late Present */
        .status-l { color: #2563eb; font-weight: bold; } /* Leave */
        .status-h { background-color: #fef2f2; color: #991b1b; } /* Holiday */
        
        .office-group-header {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: left !important;
            padding-left: 1rem !important;
            font-size: 0.85rem;
        }
        .dept-group-header {
            background-color: #fff;
            font-weight: bold;
            text-align: left !important;
            padding-left: 2rem !important;
            font-size: 0.8rem;
            color: #475569;
        }
        .summary-col {
            background-color: #fffbeb;
            font-weight: bold;
        }
        .wd-col {
            background-color: #f0fdf4;
            font-weight: bold;
        }
        .legend-box {
            font-size: 0.75rem;
            border: 1px solid #e2e8f0;
            padding: 10px;
            border-radius: 6px;
            background: #f8fafc;
        }
        .ui-download-bar {
            position: sticky;
            bottom: 0;
            background: #fff;
            border-top: 1px solid var(--hr-border, #e5e7eb);
            padding: 0.75rem 1.5rem;
            z-index: 10;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.06);
        }
        @media print {
            .ui-sidebar, .ui-download-bar, .no-print { display: none !important; }
            .ui-main { padding: 0 !important; margin: 0 !important; }
            .monthly-grid-table { font-size: 0.6rem; }
        }
    </style>
    @endpush

    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            {{-- Header --}}
            <div class="row mb-4 no-print">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        @php
                            $logoUrl = asset('images/MIRORIGINAL.jpeg');

                            if (isset($selectedOffice) && $selectedOffice->logo) {
                                $officeLogo = $selectedOffice->logo;
                                $logoUrl = \Illuminate\Support\Str::startsWith($officeLogo, 'images/')
                                    ? asset($officeLogo)
                                    : asset('storage/' . $officeLogo);
                            }
                        @endphp
                        <div class="me-4 border-end pe-4">
                            <img src="{{ $logoUrl }}" alt="Office Logo" style="height: 60px; object-fit: contain;">
                        </div>
                        <div>
                            <h5 class="mb-1 text-2xl font-bold">{{ __('Monthly Attendance Preview') }}</h5>
                            <p class="mb-0 text-gray-500">{{ __('Detailed grid view of employee attendance for the month') }}</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('personnel.attendances.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center">
                            <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                        </a>
                        <button onclick="window.print()" class="btn btn-sm btn-primary">
                            <i class="bi bi-printer me-1"></i>{{ __('Print') }}
                        </button>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('personnel.reports.attendances.monthly.export.preview') }}" id="exportForm" class="no-print">
                {{-- Filters --}}
                <div class="ui-panel mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-gray-600">{{ __('Month') }}</label>
                            <select name="month" class="form-select form-select-sm">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-gray-600">{{ __('Year') }}</label>
                            <select name="year" class="form-select form-select-sm">
                                @foreach(range(now()->year - 2, now()->year) as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-gray-600">{{ __('Office') }}</label>
                            <select name="office_id" class="form-select form-select-sm">
                                <option value="">{{ __('All Offices') }}</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}" {{ request('office_id') == $office->id ? 'selected' : '' }}>
                                        {{ $office->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-gray-600">{{ __('Department') }}</label>
                            <select name="department_id" class="form-select form-select-sm">
                                <option value="">{{ __('All Departments') }}</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-success w-100 fw-bold">
                                <i class="bi bi-filter me-1"></i>{{ __('Apply') }}
                            </button>
                            <a href="{{ route('personnel.reports.attendances.monthly.export.preview') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <div class="legend-box mb-4 no-print d-flex justify-content-between align-items-center">
                <div class="d-flex gap-4">
                    <span><b class="status-p">P</b>: Present</span>
                    <span><b class="status-a">A</b>: Absent</span>
                    <span><b class="status-lp">LP</b>: Late Present</span>
                    <span><b class="status-l">L</b>: Leave</span>
                    <span><b class="status-h" style="padding: 1px 4px;">H</b>: Holiday/Off</span>
                </div>
                <div class="small text-gray-500">
                    * WD = Working Days
                </div>
            </div>

            {{-- Grid Table --}}
            <div class="ui-panel p-0 overflow-hidden mb-4">
                <div class="table-responsive">
                    <table class="monthly-grid-table">
                        <thead>
                            <tr>
                                <th rowspan="2" style="width: 80px;">Emp Id</th>
                                <th rowspan="2" style="width: 150px;">Name</th>
                                <th rowspan="2" style="width: 120px;">Designation</th>
                                <th colspan="{{ $daysInMonth }}">Days</th>
                                <th colspan="7">Summary</th>
                            </tr>
                            <tr>
                                @for($d = 1; $d <= $daysInMonth; $d++)
                                    <th style="width: 25px;">{{ $d }}</th>
                                @endfor
                                <th class="summary-col" style="width: 25px;">P</th>
                                <th class="summary-col" style="width: 25px;">A</th>
                                <th class="summary-col" style="width: 25px;">LP</th>
                                <th class="summary-col" style="width: 25px;">LA</th>
                                <th class="summary-col" style="width: 25px;">L</th>
                                <th class="summary-col" style="width: 25px;">H</th>
                                <th class="wd-col" style="width: 25px;">WD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $groupedData = collect($processedData)->groupBy(fn($item) => $item['employee']->office->name ?? 'Unassigned');
                            @endphp

                            @foreach($groupedData as $officeName => $officeItems)
                                <tr>
                                    <td colspan="{{ $daysInMonth + 10 }}" class="office-group-header">Office: {{ $officeName }}</td>
                                </tr>
                                
                                @php
                                    $deptGrouped = $officeItems->groupBy(fn($item) => $item['employee']->department->name ?? 'Unassigned');
                                @endphp

                                @foreach($deptGrouped as $deptName => $deptItems)
                                    <tr>
                                        <td colspan="{{ $daysInMonth + 10 }}" class="dept-group-header">Department: {{ $deptName }}</td>
                                    </tr>

                                    @foreach($deptItems as $item)
                                        <tr>
                                            <td>{{ $item['employee']->employee_code }}</td>
                                            <td class="text-start ps-2">{{ $item['employee']->name }}</td>
                                            <td class="text-start ps-2">{{ $item['employee']->designation->name ?? 'N/A' }}</td>
                                            
                                            @foreach($item['days'] as $day => $status)
                                                <td class="status-{{ strtolower($status) }}">{{ $status }}</td>
                                            @endforeach

                                            <td class="summary-col">{{ $item['summary']['P'] }}</td>
                                            <td class="summary-col">{{ $item['summary']['A'] }}</td>
                                            <td class="summary-col">{{ $item['summary']['LP'] }}</td>
                                            <td class="summary-col">{{ $item['summary']['LA'] }}</td>
                                            <td class="summary-col">{{ $item['summary']['L'] }}</td>
                                            <td class="summary-col">{{ $item['summary']['H'] }}</td>
                                            <td class="wd-col">{{ $item['summary']['WD'] }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
            <div class="mb-5 no-print">
                {{ $employees->links() }}
            </div>

            {{-- Download Bar --}}
            <div class="ui-download-bar d-flex justify-content-end align-items-center no-print">
                <div class="dropdown">
                    <button class="btn btn-success dropdown-toggle d-flex align-items-center justify-content-center" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 45px; height: 45px; border-radius: 12px; padding: 0;">
                        <i class="bi bi-download fs-5"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4">
                        <li>
                            <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('personnel.reports.attendances.monthly.export.excel', request()->all()) }}">
                                <div class="bg-success bg-opacity-10 p-2 rounded-3 me-3">
                                    <i class="bi bi-file-earmark-excel text-success"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">{{ __('Excel Spreadsheet') }}</div>
                                    <div class="small text-muted">{{ __('Data with formatting (.xlsx)') }}</div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('personnel.reports.attendances.monthly.export.csv', request()->all()) }}">
                                <div class="bg-secondary bg-opacity-10 p-2 rounded-3 me-3">
                                    <i class="bi bi-filetype-csv text-secondary"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">{{ __('CSV File') }}</div>
                                    <div class="small text-muted">{{ __('Raw data for other systems (.csv)') }}</div>
                                </div>
                            </a>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('personnel.reports.attendances.monthly.export.pdf', request()->all()) }}">
                                <div class="bg-danger bg-opacity-10 p-2 rounded-3 me-3">
                                    <i class="bi bi-file-earmark-pdf text-danger"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">{{ __('PDF Document') }}</div>
                                    <div class="small text-muted">{{ __('Print-ready document (.pdf)') }}</div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('personnel.reports.attendances.monthly.export.word', request()->all()) }}">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                                    <i class="bi bi-file-earmark-word text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">{{ __('Word Document') }}</div>
                                    <div class="small text-muted">{{ __('Editable document (.doc)') }}</div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>




