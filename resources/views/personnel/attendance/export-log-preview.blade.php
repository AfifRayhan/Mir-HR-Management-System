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
        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
    </style>
    @endpush

    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            {{-- Header --}}
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 text-2xl font-bold">{{ __('Employee Attendance Log') }}</h5>
                        <p class="mb-0 text-gray-500">{{ __('View detailed attendance history for a specific employee') }}</p>
                    </div>
                </div>
            </div>

            {{-- Filter Bar --}}
            <div class="ui-filter-bar">
                <form action="{{ route('personnel.reports.attendances.log.preview') }}" method="GET" class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small font-bold text-gray-600">{{ __('Employee') }}</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">{{ __('-- Select Employee --') }}</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ $selectedEmployeeId == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }} ({{ $emp->employee_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small font-bold text-gray-600">{{ __('From Date') }}</label>
                        <input type="text" name="from_date" id="from_date" class="form-control" value="{{ $fromDate }}" placeholder="Select date" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small font-bold text-gray-600">{{ __('To Date') }}</label>
                        <input type="text" name="to_date" id="to_date" class="form-control" value="{{ $toDate }}" placeholder="Select date" readonly>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn ui-btn-search flex-grow-1">{{ __('Search') }}</button>
                    </div>
                </form>
            </div>

            @if($selectedEmployeeId)
                @php $selectedEmp = $employees->find($selectedEmployeeId); @endphp

                <div class="info-card">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="mb-1 font-bold text-xl text-dark">{{ $selectedEmp->name }}</h4>
                            <p class="mb-0 text-muted">
                                <span class="badge bg-light text-dark border me-2">{{ $selectedEmp->employee_code }}</span>
                                {{ $selectedEmp->designation->name ?? 'N/A' }} | {{ $selectedEmp->department->name ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="text-sm text-gray-500 mb-1">Attendance Period</div>
                            <div class="font-semibold text-lg text-success">
                                {{ Carbon\Carbon::parse($fromDate)->format('M d, Y') }} - {{ Carbon\Carbon::parse($toDate)->format('M d, Y') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ui-panel p-0 overflow-hidden mb-4">
                    <div class="table-responsive">
                        <table class="table ui-table mb-0">
                            <thead>
                                <tr class="text-center">
                                    <th class="ps-4">Date</th>
                                    <th>Day</th>
                                    <th>In Time</th>
                                    <th>Out Time</th>
                                    <th>Working Hours</th>
                                    <th>Late (H:M:S)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($records as $record)
                                    <tr class="text-center">
                                        <td class="ps-4">{{ $record->date->format('Y-m-d') }}</td>
                                        <td>{{ $record->date->format('l') }}</td>
                                        <td>{{ $record->in_time ? $record->in_time->format('h:i A') : '-' }}</td>
                                        <td>{{ $record->out_time ? $record->out_time->format('h:i A') : '-' }}</td>
                                        <td>{{ $record->working_hours ?: '-' }}h</td>
                                        <td>{{ $record->late_timing ?: '-' }}</td>
                                        <td>
                                            @php
                                                $statusClass = [
                                                    'present' => 'bg-success',
                                                    'late' => 'bg-warning text-dark',
                                                    'absent' => 'bg-danger',
                                                    'leave' => 'bg-info text-dark',
                                                    'holiday' => 'bg-secondary',
                                                ][strtolower($record->status)] ?? 'bg-secondary';
                                            @endphp
                                            <span class="badge {{ $statusClass }}">
                                                {{ ucfirst($record->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Download Bar --}}
                <div class="ui-download-bar d-flex justify-content-between align-items-center mb-4 rounded-4">
                    <span class="text-gray-600 small">
                        <i class="bi bi-info-circle me-1"></i>
                        {{ __('Showing :count records for the selected period', ['count' => count($records)]) }}
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
                                        <div class="small text-muted">{{ __('Print-ready log (.pdf)') }}</div>
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
            @else
                <div class="ui-panel text-center py-5">
                    <i class="bi bi-person-badge text-gray-200" style="font-size: 5rem;"></i>
                    <h5 class="mt-3 text-gray-500">Please select an employee and date range to view the attendance log.</h5>
                </div>
            @endif
        </main>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            flatpickr('#from_date', {
                dateFormat: 'Y-m-d',
                allowInput: false
            });
            flatpickr('#to_date', {
                dateFormat: 'Y-m-d',
                allowInput: false
            });

            var routes = {
                excel: "{{ route('personnel.reports.attendances.log.export.excel') }}",
                csv: "{{ route('personnel.reports.attendances.log.export.csv') }}",
                pdf: "{{ route('personnel.reports.attendances.log.export.pdf') }}",
                word: "{{ route('personnel.reports.attendances.log.export.word') }}",
            };

            function buildDownloadUrl(baseRoute) {
                var params = new URLSearchParams(window.location.search);
                return baseRoute + '?' + params.toString();
            }

            document.getElementById('downloadExcel')?.addEventListener('click', function (e) {
                e.preventDefault();
                window.location.href = buildDownloadUrl(routes.excel);
            });
            document.getElementById('downloadCsv')?.addEventListener('click', function (e) {
                e.preventDefault();
                window.location.href = buildDownloadUrl(routes.csv);
            });
            document.getElementById('downloadPdf')?.addEventListener('click', function (e) {
                e.preventDefault();
                window.location.href = buildDownloadUrl(routes.pdf);
            });
            document.getElementById('downloadWord')?.addEventListener('click', function (e) {
                e.preventDefault();
                window.location.href = buildDownloadUrl(routes.word);
            });
        });
    </script>
    @endpush
</x-app-layout>




