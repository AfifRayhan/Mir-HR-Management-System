<x-app-layout>
    @push('styles')
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .ui-download-bar {
            position: sticky;
            bottom: 0;
            background: #fff;
            border-top: 1px solid var(--hr-border, #e5e7eb);
            padding: 0.75rem 1.5rem;
            z-index: 10;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.06);
        }
        .preview-count {
            font-size: 0.8rem;
            color: #6b7280;
        }
    </style>
    @endpush

    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            {{-- Header --}}
            <div class="row mb-4">
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
                            <h5 class="mb-1 text-2xl font-bold">{{ __('Attendance Preview & Export') }}</h5>
                            <p class="mb-0 text-gray-500">{{ __('Filter data, preview records, then download') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('personnel.attendances.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center">
                        <i class="bi bi-arrow-left me-2"></i>{{ __('Back to Attendance') }}
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('personnel.reports.attendances.export.preview') }}" id="exportForm">
                {{-- Filters --}}
                <div class="ui-panel mb-4">
                    <div class="row g-3">
                        {{-- Date --}}
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-gray-600">{{ __('Date') }}</label>
                            <input type="text" id="attendance_date" name="date" class="form-control form-control-sm" value="{{ $date }}" placeholder="Select date" readonly>
                        </div>

                        {{-- Search --}}
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-gray-600">{{ __('Search') }}</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-gray-400">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Name or ID..." value="{{ request('search') }}">
                            </div>
                        </div>

                        {{-- Office --}}
                        <div class="col-md-2">
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

                        {{-- Department --}}
                        <div class="col-md-2">
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

                        {{-- Designation --}}
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-gray-600">{{ __('Designation') }}</label>
                            <select name="designation_id" class="form-select form-select-sm">
                                <option value="">{{ __('All Designations') }}</option>
                                @foreach($designations as $desig)
                                    <option value="{{ $desig->id }}" {{ request('designation_id') == $desig->id ? 'selected' : '' }}>
                                        {{ $desig->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Status --}}
                        <div class="col-md-1">
                            <label class="form-label small fw-bold text-gray-600">{{ __('Status') }}</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">{{ __('All') }}</option>
                                @foreach($statuses as $s)
                                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                                    {{ ucfirst($s) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12 d-flex justify-content-end gap-2 pt-3 border-top mt-3">
                            <a href="{{ route('personnel.reports.attendances.export.preview') }}" class="btn btn-sm btn-outline-success px-4 fw-bold">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('Reset') }}
                            </a>
                            <button type="submit" class="btn btn-sm btn-success px-4 fw-bold">
                                <i class="bi bi-filter me-1"></i>{{ __('Apply Filters') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Preview Table --}}
            <div class="ui-panel p-0 overflow-hidden mb-4">
                <div class="table-responsive">
                    <table class="table ui-table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">#</th>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Department/Designation') }}</th>
                                <th>{{ __('In Time') }}</th>
                                <th>{{ __('Out Time') }}</th>
                                <th>{{ __('Working Hours') }}</th>
                                <th>{{ __('Late (H:M:S)') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $index => $record)
                                <tr>
                                    <td class="ps-4 text-gray-500">{{ $records->firstItem() + $index }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $record->employee->name }}</div>
                                        <div class="small text-muted">{{ $record->employee->employee_code }}</div>
                                    </td>
                                    <td>
                                        <div class="small">{{ $record->employee->department->name ?? 'N/A' }}</div>
                                        <div class="small text-muted">{{ $record->employee->designation->name ?? 'N/A' }}</div>
                                    </td>
                                    <td>{{ $record->in_time ? $record->in_time->format('h:i A') : '-' }}</td>
                                    <td>{{ $record->out_time ? $record->out_time->format('h:i A') : '-' }}</td>
                                    <td>{{ $record->working_hours }}h</td>
                                    <td>{{ $record->late_timing }}</td>
                                    <td>
                                        @php
                                        $statusClass = [
                                            'present' => 'bg-success',
                                            'late' => 'bg-warning text-dark',
                                            'absent' => 'bg-danger',
                                            'leave' => 'bg-info text-dark',
                                        ][$record->status] ?? 'bg-secondary';
                                        @endphp
                                        <span class="badge {{ $statusClass }}">
                                            {{ ucfirst($record->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="bi bi-clock-history text-4xl text-gray-200 d-block mb-3"></i>
                                        <span class="text-gray-500">{{ __('No attendance records found.') }}</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
            <div class="mb-5">
                {{ $records->links() }}
            </div>

            {{-- Download Bar --}}
            <div class="ui-download-bar d-flex justify-content-between align-items-center">
                <span class="text-gray-600 small">
                    <i class="bi bi-info-circle me-1"></i>
                    {{ __('Showing :count records of :total total', ['count' => $records->count(), 'total' => $records->total()]) }}
                </span>
                <div class="dropdown">
                    <button class="btn btn-success dropdown-toggle d-flex align-items-center justify-content-center" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 45px; height: 45px; border-radius: 12px; padding: 0;">
                        <i class="bi bi-download fs-5"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4">
                        <li>
                            <a class="dropdown-item d-flex align-items-center py-2" href="#" id="downloadExcel">
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
                            <a class="dropdown-item d-flex align-items-center py-2" href="#" id="downloadCsv">
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
                            <a class="dropdown-item d-flex align-items-center py-2" href="#" id="downloadPdf">
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
                            <a class="dropdown-item d-flex align-items-center py-2" href="#" id="downloadWord">
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

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script type="application/json" id="exportPreviewData">
        @php
            echo json_encode([
                'routes' => [
                    'excel' => route('personnel.reports.attendances.export.excel'),
                    'csv' => route('personnel.reports.attendances.export.csv'),
                    'pdf' => route('personnel.reports.attendances.export.pdf'),
                    'word' => route('personnel.reports.attendances.export.word'),
                ],
            ]);
        @endphp
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            flatpickr('#attendance_date', {
                dateFormat: 'Y-m-d',
                allowInput: false
            });

            var config = JSON.parse(document.getElementById('exportPreviewData').textContent);
            var routes = config.routes;

            function buildDownloadUrl(baseRoute) {
                var params = new URLSearchParams();
                
                const filterInputs = [
                    'search', 'office_id', 'department_id', 'designation_id', 'status', 'date'
                ];
                filterInputs.forEach(name => {
                    const el = document.querySelector(`[name="${name}"]`);
                    if (el && el.value) {
                        params.set(name, el.value);
                    }
                });

                return baseRoute + '?' + params.toString();
            }

            document.getElementById('downloadExcel').addEventListener('click', function (e) {
                e.preventDefault();
                window.location.href = buildDownloadUrl(routes.excel);
            });
            document.getElementById('downloadCsv').addEventListener('click', function (e) {
                e.preventDefault();
                window.location.href = buildDownloadUrl(routes.csv);
            });
            document.getElementById('downloadPdf').addEventListener('click', function (e) {
                e.preventDefault();
                window.location.href = buildDownloadUrl(routes.pdf);
            });
            document.getElementById('downloadWord').addEventListener('click', function (e) {
                e.preventDefault();
                window.location.href = buildDownloadUrl(routes.word);
            });
        });
    </script>
    @endpush
</x-app-layout>




