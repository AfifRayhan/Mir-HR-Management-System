<x-app-layout>
    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 0.5rem;
            border-color: #dee2e6;
            min-height: 38px;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height: 24px;
            padding-top: 6px;
        }
    </style>
    @endpush
    

    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            <div class="row mb-4 align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1 text-2xl font-bold">{{ __('Employee Management') }}</h5>
                    <p class="mb-0 text-gray-500">{{ __('Manage your organization\'s workforce') }}</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="{{ route('personnel.employees.create') }}" class="btn btn-primary px-4 d-inline-flex align-items-center" style="min-width: max-content;">
                        <i class="bi bi-person-plus me-2"></i>
                        <span class="text-nowrap">{{ __('Add Employee') }}</span>
                    </a>
                </div>
            </div>


            <!-- Filter Bar -->
            <div class="ui-filter-bar">
                <form action="{{ route('personnel.employees.index') }}" method="GET" class="row g-2 align-items-end" id="employee-filter-form">
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <label class="form-label small font-bold text-gray-600">{{ __('Search Employee/ID') }}</label>
                        <select name="search" class="form-select select2" id="employee-search">
                            <option value="">{{ __('All Employees') }}</option>
                            @foreach($allEmployees as $empOpt)
                                <option value="{{ $empOpt->employee_code }}" {{ request('search') == $empOpt->employee_code ? 'selected' : '' }}>
                                    {{ $empOpt->name }} ({{ $empOpt->employee_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <label class="form-label small font-bold text-gray-600">{{ __('Department') }}</label>
                        <select name="department_id" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <label class="form-label small font-bold text-gray-600">{{ __('Section') }}</label>
                        <select name="section_id" class="form-select text-xs">
                            <option value="">{{ __('All') }}</option>
                            @foreach($sections as $sec)
                            <option value="{{ $sec->id }}" {{ request('section_id') == $sec->id ? 'selected' : '' }}>
                                {{ $sec->name }} ({{ $sec->department->name ?? 'N/A' }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <label class="form-label small font-bold text-gray-600">{{ __('Office') }}</label>
                        <select name="office_id" class="form-select text-xs">
                            <option value="">{{ __('All') }}</option>
                            @foreach($offices as $office)
                            <option value="{{ $office->id }}" {{ request('office_id') == $office->id ? 'selected' : '' }}>
                                {{ $office->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <label class="form-label small font-bold text-gray-600">{{ __('Designation') }}</label>
                        <select name="designation_id" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            @foreach($designations as $des)
                            <option value="{{ $des->id }}" {{ request('designation_id') == $des->id ? 'selected' : '' }}>
                                {{ $des->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <label class="form-label small font-bold text-gray-600">{{ __('Status') }}</label>
                        <select name="status" class="form-select text-xs">
                            @php $selectedStatus = request()->has('status') ? request('status') : 'active'; @endphp
                            <option value="">{{ __('All') }}</option>
                            <option value="active" {{ $selectedStatus == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                            <option value="inactive" {{ $selectedStatus == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                        </select>
                    </div>
                    <div class="col-xl-auto col-lg-auto col-md-12 col-sm-12 d-flex gap-2 pb-1">
                        <button type="submit" class="btn ui-btn-search px-3">{{ __('Search') }}</button>
                        <a href="{{ route('personnel.employees.index') }}" class="btn ui-btn-clear px-3">{{ __('Clear') }}</a>
                    </div>
                </form>
            </div>

            <div class="ui-panel p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table ui-table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">
                                    <a href="{{ route('personnel.employees.index', array_merge(request()->query(), ['sort' => 'employee_code', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="sort-link">
                                        {{ __('Employee ID') }}
                                        @if(request('sort') === 'employee_code')
                                        <i class="bi bi-sort-{{ request('direction') === 'asc' ? 'down' : 'up' }} sort-icon text-success"></i>
                                        @else
                                        <i class="bi bi-sort-down sort-icon"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('personnel.employees.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="sort-link">
                                        {{ __('Full Name') }}
                                        @if(request('sort') === 'name')
                                        <i class="bi bi-sort-{{ request('direction') === 'asc' ? 'down' : 'up' }} sort-icon text-success"></i>
                                        @else
                                        <i class="bi bi-sort-down sort-icon"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>{{ __('Department / Section') }}</th>
                                <th>{{ __('Office') }}</th>
                                <th>{{ __('Designation') }}</th>
                                <th>{{ __('Gross Salary') }}</th>
                                <th>
                                    <a href="{{ route('personnel.employees.index', array_merge(request()->query(), ['sort' => 'joining_date', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="sort-link">
                                        {{ __('Joining Date') }}
                                        @if(request('sort') === 'joining_date')
                                        <i class="bi bi-sort-{{ request('direction') === 'asc' ? 'down' : 'up' }} sort-icon text-success"></i>
                                        @else
                                        <i class="bi bi-sort-down sort-icon"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-end pe-4">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $emp)
                            <tr>
                                <td class="ps-4">
                                    <div class="font-bold text-gray-700 mb-1">{{ $emp->employee_code }}</div>
                                    @php
                                        $statusColors = [
                                            'active' => 'bg-success-soft text-success',
                                            'inactive' => 'bg-secondary-soft text-secondary',
                                        ];
                                        $badgeClass = $statusColors[$emp->status] ?? 'bg-light text-dark';
                                    @endphp
                                    <span class="badge {{ $badgeClass }} text-xs" style="padding: 0.35em 0.5em;">
                                        {{ ucfirst($emp->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('personnel.employees.edit', $emp->id) }}" class="text-decoration-none d-flex align-items-center group">
                                        <div class="emp-avatar-sm me-3 transition-all group-hover:scale-105">
                                            {{ strtoupper(substr($emp->name, 0, 1)) }}
                                        </div>
                                        <div style="min-width: 150px;">
                                            <div class="fw-bold mb-0 text-gray-800 text-nowrap group-hover:text-success transition-colors">{{ $emp->name }}</div>
                                            <div class="small text-muted text-nowrap">{{ $emp->phone ?? $emp->contact_no ?? 'No phone' }}</div>
                                        </div>
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-bold text-gray-800 mb-1">{{ $emp->department->name ?? 'N/A' }}</div>
                                    <div class="small text-muted">{{ $emp->section->name ?? 'No Section' }}</div>
                                </td>
                                <td>{{ $emp->office->name ?? 'N/A' }}</td>
                                <td>{{ $emp->designation->name ?? 'N/A' }}</td>
                                <td>{{ number_format($emp->gross_salary, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($emp->joining_date)->format('d M Y') }}</td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('personnel.employees.edit', $emp->id) }}" class="btn btn-sm btn-outline-primary border-0" title="{{ __('Edit') }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        @php $confirmMsg = __('Are you sure you want to delete this employee?'); @endphp
                                        <form action="{{ route('personnel.employees.destroy', $emp->id) }}" method="POST" data-confirm data-confirm-message="{{ $confirmMsg }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="{{ __('Delete') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <i class="bi bi-people text-4xl text-gray-200 d-block mb-3"></i>
                                    <span class="text-gray-500">{{ __('No employees found.') }}</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $employees->links() }}
            </div>
        </main>
    </div>
    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            $('#employee-search').on('select2:select select2:clear change', function() {
                $('#employee-filter-form').trigger('submit');
            });
        });
    </script>
    @endpush
</x-app-layout>



