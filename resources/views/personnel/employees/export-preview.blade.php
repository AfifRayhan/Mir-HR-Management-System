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
        .ui-table {
            border: 1px solid #e2e8f0;
            table-layout: fixed;
            width: 100%;
        }
        .ui-table th, .ui-table td {
            border: 1px solid #e2e8f0 !important;
            padding: 4px 6px !important;
            font-size: 0.7rem;
            vertical-align: middle;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .ui-table thead th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-align: center;
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
                                $logoUrl = \Illuminate\Support\Str::startsWith($selectedOffice->logo, 'images/')
                                    ? asset($selectedOffice->logo)
                                    : asset('storage/' . $selectedOffice->logo);
                            }
                        @endphp
                        <div class="me-4 border-end pe-4">
                            <img src="{{ $logoUrl }}" alt="Office Logo" style="height: 60px; object-fit: contain;">
                        </div>
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
                        @php
                            $columnGroups = [
                                'Personal Information' => [
                                    'employee_code', 'name', 'personal_email', 'phone', 'blood_group', 
                                    'gender', 'religion', 'marital_status', 'national_id', 'tin', 'nationality', 
                                    'no_of_children', 'contact_no', 'date_of_birth', 'present_address', 'permanent_address'
                                ],
                                'Emergency Contact Information' => [
                                    'father_name', 'mother_name', 'spouse_name', 'emergency_contact_name', 
                                    'emergency_contact_relation', 'emergency_contact_no', 'emergency_contact_address'
                                ],
                                'Organization & Role' => [
                                    'email', 'joining_date', 'discontinuation_date', 'discontinuation_reason', 'department', 
                                    'section', 'designation', 'grade', 'office', 'office_time', 'gross_salary', 'status'
                                ]
                            ];
                        @endphp

                        <div class="row g-4 mb-3">
                            @foreach($columnGroups as $groupName => $keys)
                                <div class="col-md-4">
                                    <h6 class="small fw-bold text-success border-bottom pb-1 mb-2">{{ __($groupName) }}</h6>
                                    <div class="d-flex flex-column gap-1">
                                        @foreach($keys as $key)
                                            @if(isset($allColumns[$key]))
                                                <div class="form-check">
                                                    <input class="form-check-input column-check" type="checkbox"
                                                        name="columns[]" value="{{ $key }}" id="col_{{ $key }}"
                                                        {{ in_array($key, $selectedColumns) ? 'checked' : '' }}>
                                                    <label class="form-check-label small" for="col_{{ $key }}">
                                                        {{ $allColumns[$key] }}
                                                    </label>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Sort Controls & Action Buttons (Single Row) --}}
                    <div class="d-flex align-items-center flex-wrap gap-3 py-3 border-top mt-3">
                        <div class="d-flex align-items-center gap-2">
                            {{-- Sorting is fixed to Tree View --}}
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
                                <th style="width: 40px;">#</th>
                                @foreach($selectedColumns as $key)
                                    @php
                                        $width = match($key) {
                                            'employee_code' => '80px',
                                            'name' => '150px',
                                            'contact_no' => '90px',
                                            'joining_date' => '90px',
                                            'department' => '110px',
                                            'section' => '100px',
                                            'designation' => '110px',
                                            'office' => '110px',
                                            'status' => '70px',
                                            'personal_email' => '150px',
                                            default => '100px'
                                        };
                                    @endphp
                                    <th style="width: {{ $width }}; text-align: center;">{{ $allColumns[$key] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $currentOfficeId = null;
                                $currentDeptId = null;
                            @endphp
                            @forelse($employees as $index => $emp)
                                @if($currentOfficeId !== $emp->office_id)
                                    <tr class="bg-gray-900 text-white">
                                        <td colspan="{{ count($selectedColumns) + 1 }}" class="fw-bold py-2 px-3">
                                            <i class="bi bi-building me-2"></i>Office: {{ $emp->office->name ?? 'N/A' }}
                                        </td>
                                    </tr>
                                    @php $currentOfficeId = $emp->office_id; $currentDeptId = null; @endphp
                                @endif

                                @if($currentDeptId !== $emp->department_id)
                                    <tr class="bg-gray-100 border-top border-bottom">
                                        <td colspan="{{ count($selectedColumns) + 1 }}" class="fw-bold py-2 px-4 text-gray-700">
                                            <i class="bi bi-diagram-3 me-2 text-success"></i>Department: {{ $emp->department->name ?? 'N/A' }}
                                        </td>
                                    </tr>
                                    @php $currentDeptId = $emp->department_id; @endphp
                                @endif

                                <tr>
                                    <td class="text-center text-gray-500">{{ $employees->firstItem() + $index }}</td>
                                    @foreach($selectedColumns as $key)
                                        @php
                                            $val = \App\Exports\EmployeesExport::getColumnValue($emp, $key);
                                        @endphp
                                        <td class="{{ in_array($key, ['employee_code', 'joining_date', 'status']) ? 'text-center' : '' }} text-truncate copyable-cell" 
                                            style="max-width: 150px; cursor: pointer;" title="{{ $val }}" data-copy-val="{{ $val }}">
                                            @if($key === 'name' || $key === 'employee_code')
                                                <a href="{{ route('personnel.employees.edit', $emp->id) }}" class="text-decoration-none fw-bold text-gray-800 hover:text-success transition-colors">
                                                    {{ $val }}
                                                </a>
                                            @else
                                                {{ $val }}
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
                if (selectedCount > 20) {
                    Swal.fire({
                        title: 'Too Many Columns',
                        text: 'PDF exports are limited to 20 columns to ensure readability. Please deselect some columns or use Excel/CSV for larger datasets.',
                        icon: 'warning',
                        confirmButtonColor: '#007A10'
                    });
                    return;
                }

                window.location.href = buildDownloadUrl(routes.pdf);
            });
            document.getElementById('downloadWord').addEventListener('click', function (e) {
                e.preventDefault();
                window.location.href = buildDownloadUrl(routes.word);
            });

            document.querySelectorAll('.copyable-cell').forEach(function(cell) {
                cell.addEventListener('dblclick', function() {
                    var val = this.getAttribute('data-copy-val');
                    if (val) {
                        navigator.clipboard.writeText(val).then(function() {
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true,
                                didOpen: (toast) => {
                                    toast.addEventListener('mouseenter', Swal.stopTimer)
                                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                                }
                            });
                            Toast.fire({
                                icon: 'success',
                                title: 'Copied to clipboard'
                            });
                        });
                    }
                });
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




