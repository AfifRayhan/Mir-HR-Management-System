<x-app-layout>
    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 0.5rem;
            border-color: #dee2e6;
            min-height: 31px;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height: 18px;
            padding-top: 5px;
            font-size: 0.875rem;
        }
    
    <style>
        .column-selector-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
        }
        .column-selector-grid .form-check {
            margin: 0;
            padding: 0.4rem 0.6rem 0.4rem 2rem;
            border-radius: 0.375rem;
            transition: background-color 0.15s;
            font-size: 0.875rem;
        }
        .column-selector-grid .form-check:hover {
            background-color: rgba(79, 70, 229, 0.05);
        }
        .column-selector-grid .form-check-input:checked + .form-check-label {
            font-weight: 600;
            color: #4F46E5;
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
        .preview-count {
            font-size: 0.8rem;
            color: #6b7280;
        }
        @media (max-width: 992px) {
            .column-selector-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .column-selector-grid {
                grid-template-columns: 1fr;
            }
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
                        @if(isset($selectedOffice) && $selectedOffice->logo)
                            <div class="me-4 border-end pe-4">
                                <img src="{{ asset('storage/' . $selectedOffice->logo) }}" alt="Office Logo" style="height: 60px; object-fit: contain;">
                            </div>
                        @else
                            <div class="me-4 border-end pe-4">
                                <img src="{{ asset('images/MIRORIGINAL.jpeg') }}" alt="Default Logo" style="height: 60px; object-fit: contain;">
                            </div>
                        @endif
                        <div>
                            <h5 class="mb-1 text-2xl font-bold">{{ __('Preview & Export') }}</h5>
                            <p class="mb-0 text-gray-500">{{ __('Select columns, preview data, then download') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('personnel.employees.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center">
                        <i class="bi bi-arrow-left me-2"></i>{{ __('Back to Employees') }}
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('personnel.reports.employees.export.preview') }}" id="exportForm">
                {{-- Filters --}}
                <div class="ui-panel mb-4">
                    <div class="row g-3">
                        {{-- Search --}}
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-gray-600">{{ __('Search Employee/ID') }}</label>
                            <select name="search" class="form-select form-select-sm select2">
                                <option value="">{{ __('All Employees') }}</option>
                                @foreach($allEmployees as $empOpt)
                                    <option value="{{ $empOpt->employee_code }}" {{ request('search') == $empOpt->employee_code ? 'selected' : '' }}>
                                        {{ $empOpt->name }} ({{ $empOpt->employee_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Office --}}
                        <div class="col-md-4">
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
                        <div class="col-md-4">
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

                        {{-- Section --}}
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-gray-600">{{ __('Section') }}</label>
                            <select name="section_id" class="form-select form-select-sm">
                                <option value="">{{ __('All Sections') }}</option>
                                @foreach($sections as $sec)
                                    <option value="{{ $sec->id }}" {{ request('section_id') == $sec->id ? 'selected' : '' }}>
                                        {{ $sec->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Designation --}}
                        <div class="col-md-4">
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
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-gray-600">{{ __('Status') }}</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">{{ __('All Status') }}</option>
                                <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="InActive" {{ request('status') == 'InActive' ? 'selected' : '' }}>InActive</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Column Selector + Sort --}}
                <div class="ui-panel mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-columns-gap me-2 text-success"></i>{{ __('Select Columns') }}
                                <span class="preview-count ms-2" id="columnCount">({{ count($selectedColumns) }} of {{ count($allColumns) }})</span>
                            </h6>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-success" id="selectAll">{{ __('Select All') }}</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">{{ __('Deselect All') }}</button>
                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="collapse" data-bs-target="#columnPanel" aria-expanded="false">
                                <i class="bi bi-chevron-down" id="collapseIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="collapse" id="columnPanel">
                        <div class="column-selector-grid mb-3">
                            @foreach($allColumns as $key => $label)
                                <div class="form-check">
                                    <input class="form-check-input column-check" type="checkbox"
                                        name="columns[]" value="{{ $key }}" id="col_{{ $key }}"
                                        {{ in_array($key, $selectedColumns) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="col_{{ $key }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Sort Controls & Action Buttons (Single Row) --}}
                    <div class="d-flex align-items-center flex-wrap gap-3 py-3 border-top mt-3">
                        <div class="d-flex align-items-center gap-2">
                            <label class="form-label mb-0 fw-bold small text-gray-600">{{ __('Sort by:') }}</label>
                            <select name="sort" class="form-select form-select-sm" style="width: auto; min-width: 140px;">
                                <option value="created_at" {{ $sortColumn === 'created_at' ? 'selected' : '' }}>{{ __('Created Date') }}</option>
                                <option value="employee_code" {{ $sortColumn === 'employee_code' ? 'selected' : '' }}>{{ __('Employee Code') }}</option>
                                <option value="name" {{ $sortColumn === 'name' ? 'selected' : '' }}>{{ __('Full Name') }}</option>
                                <option value="email" {{ $sortColumn === 'email' ? 'selected' : '' }}>{{ __('Email') }}</option>
                                <option value="joining_date" {{ $sortColumn === 'joining_date' ? 'selected' : '' }}>{{ __('Joining Date') }}</option>
                                <option value="date_of_birth" {{ $sortColumn === 'date_of_birth' ? 'selected' : '' }}>{{ __('Date of Birth') }}</option>
                                <option value="gross_salary" {{ $sortColumn === 'gross_salary' ? 'selected' : '' }}>{{ __('Gross Salary') }}</option>
                                <option value="status" {{ $sortColumn === 'status' ? 'selected' : '' }}>{{ __('Status') }}</option>
                            </select>
                            <select name="direction" class="form-select form-select-sm" style="width: auto;">
                                <option value="asc" {{ $sortDirection === 'asc' ? 'selected' : '' }}>{{ __('Ascending') }}</option>
                                <option value="desc" {{ $sortDirection === 'desc' ? 'selected' : '' }}>{{ __('Descending') }}</option>
                            </select>
                        </div>
                        
                        <div class="ms-auto d-flex gap-2">
                            <a href="{{ route('personnel.reports.employees.export.preview') }}" class="btn btn-sm btn-outline-success px-4 fw-bold">
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
                                @foreach($selectedColumns as $key)
                                    <th>{{ $allColumns[$key] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $index => $emp)
                                <tr>
                                    <td class="ps-4 text-gray-500">{{ $employees->firstItem() + $index }}</td>
                                    @foreach($selectedColumns as $key)
                                        <td>
                                            @if($key === 'name' || $key === 'employee_code')
                                                <a href="{{ route('personnel.employees.edit', $emp->id) }}" class="text-decoration-none fw-bold text-gray-800 hover:text-success transition-colors">
                                                    {{ \App\Exports\EmployeesExport::getColumnValue($emp, $key) }}
                                                </a>
                                            @else
                                                {{ \App\Exports\EmployeesExport::getColumnValue($emp, $key) }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($selectedColumns) + 1 }}" class="text-center py-5">
                                        <i class="bi bi-people text-4xl text-gray-200 d-block mb-3"></i>
                                        <span class="text-gray-500">{{ __('No employees found.') }}</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
            <div class="mb-5">
                {{ $employees->links() }}
            </div>

            {{-- Download Bar --}}
            <div class="ui-download-bar d-flex justify-content-between align-items-center">
                <span class="text-gray-600 small">
                    <i class="bi bi-info-circle me-1"></i>
                    {{ __('Showing :count columns, :total total records', ['count' => count($selectedColumns), 'total' => $employees->total()]) }}
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="application/json" id="exportPreviewRoutes">
        @php
            echo json_encode([
                'excel' => route('personnel.reports.employees.export.excel'),
                'csv' => route('personnel.reports.employees.export.csv'),
                'pdf' => route('personnel.reports.employees.export.pdf'),
                'word' => route('personnel.reports.employees.export.word'),
            ]);
        @endphp
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var checkboxes = document.querySelectorAll('.column-check');
            var countEl = document.getElementById('columnCount');
            var totalCols = {{ count($allColumns) }};
            var routes = JSON.parse(document.getElementById('exportPreviewRoutes').textContent);

            function updateCount() {
                var checked = document.querySelectorAll('.column-check:checked').length;
                countEl.textContent = '(' + checked + ' of ' + totalCols + ')';
            }

            checkboxes.forEach(function(cb) { cb.addEventListener('change', updateCount); });

            document.getElementById('selectAll').addEventListener('click', function () {
                checkboxes.forEach(function(cb) { cb.checked = true; });
                updateCount();
            });

            document.getElementById('deselectAll').addEventListener('click', function () {
                checkboxes.forEach(function(cb) { cb.checked = false; });
                updateCount();
            });

            // Collapse icon toggle
            var collapseEl = document.getElementById('columnPanel');
            var collapseIcon = document.getElementById('collapseIcon');
            collapseEl.addEventListener('hidden.bs.collapse', function() {
                collapseIcon.classList.replace('bi-chevron-up', 'bi-chevron-down');
            });
            collapseEl.addEventListener('shown.bs.collapse', function() {
                collapseIcon.classList.replace('bi-chevron-down', 'bi-chevron-up');
            });

            function buildDownloadUrl(baseRoute) {
                var params = new URLSearchParams();
                var filterInputs = ['search', 'office_id', 'department_id', 'section_id', 'designation_id', 'grade_id', 'status'];

                filterInputs.forEach(function(name) {
                    var el = document.querySelector('[name="' + name + '"]');
                    if (el && el.value) {
                        params.set(name, el.value);
                    }
                });

                document.querySelectorAll('.column-check:checked').forEach(function(cb) {
                    params.append('columns[]', cb.value);
                });

                var sortEl = document.querySelector('[name="sort"]');
                var dirEl = document.querySelector('[name="direction"]');
                if (sortEl) params.set('sort', sortEl.value);
                if (dirEl) params.set('direction', dirEl.value);

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

                var selectedCount = document.querySelectorAll('.column-check:checked').length;
                if (selectedCount > 9) {
                    Swal.fire({
                        title: 'Too Many Columns',
                        text: 'PDF exports are limited to 9 columns to ensure readability. Please deselect some columns or use Excel/CSV for larger datasets.',
                        icon: 'warning',
                        confirmButtonColor: '#4F46E5'
                    });
                    return;
                }

                window.location.href = buildDownloadUrl(routes.pdf);
            });
            document.getElementById('downloadWord').addEventListener('click', function (e) {
                e.preventDefault();
                window.location.href = buildDownloadUrl(routes.word);
            });

            if (typeof $.fn.select2 !== 'undefined') {
                $('.select2').select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }
        });
    </script>
    @endpush
</x-app-layout>




