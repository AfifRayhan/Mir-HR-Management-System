<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 text-2xl font-bold">{{ __('Employee Management') }}</h5>
                        <p class="mb-0 text-gray-500">{{ __('Manage your organization\'s workforce') }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('personnel.employees.export.excel', request()->query()) }}" class="btn btn-sm btn-outline-success d-flex align-items-center">
                            <i class="bi bi-file-earmark-excel me-2"></i>{{ __('Export Excel') }}
                        </a>
                        <a href="{{ route('personnel.employees.export.csv', request()->query()) }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center">
                            <i class="bi bi-filetype-csv me-2"></i>{{ __('Export CSV') }}
                        </a>
                        <a href="{{ route('personnel.employees.create') }}" class="btn btn-sm btn-primary d-flex align-items-center">
                            <i class="bi bi-person-plus me-2"></i>{{ __('Add Employee') }}
                        </a>
                    </div>
                </div>
            </div>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- Filter Bar -->
            <div class="filter-bar">
                <form action="{{ route('personnel.employees.index') }}" method="GET" class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label small font-bold text-gray-600">{{ __('Search') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-gray-400">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Name or ID..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
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
                    <div class="col-md-2">
                        <label class="form-label small font-bold text-gray-600">{{ __('Office') }}</label>
                        <select name="office_id" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            @foreach($offices as $office)
                            <option value="{{ $office->id }}" {{ request('office_id') == $office->id ? 'selected' : '' }}>
                                {{ $office->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
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
                    <div class="col-md-2">
                        <label class="form-label small font-bold text-gray-600">{{ __('Status') }}</label>
                        <select name="status" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-outline-secondary px-2" title="{{ __('Filter') }}">
                            <i class="bi bi-funnel"></i>
                        </button>
                        <a href="{{ route('personnel.employees.index') }}" class="btn btn-link text-gray-500 p-0 mb-1" title="{{ __('Clear') }}">
                            <i class="bi bi-x-circle text-xl"></i>
                        </a>
                    </div>
                </form>
            </div>

            <div class="hr-panel p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table hr-table mb-0">
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
                                <th>{{ __('Department') }}</th>
                                <th>{{ __('Office') }}</th>
                                <th>{{ __('Designation') }}</th>
                                <th>{{ __('Section') }}</th>
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
                                <th>{{ __('Account') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end pe-4">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $emp)
                            <tr>
                                <td class="ps-4 font-bold text-gray-700">{{ $emp->employee_code }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="emp-avatar-sm me-3">
                                            {{ strtoupper(substr($emp->name, 0, 1)) }}
                                        </div>
                                        <div style="min-width: 150px;">
                                            <div class="fw-bold mb-0 text-gray-800 text-nowrap">{{ $emp->name }}</div>
                                            <div class="small text-muted text-nowrap">{{ $emp->phone ?? 'No phone' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark">{{ $emp->department->name ?? 'N/A' }}</span></td>
                                <td>{{ $emp->office->name ?? 'N/A' }}</td>
                                <td>{{ $emp->designation->name ?? 'N/A' }}</td>
                                <td>{{ $emp->section->name ?? 'N/A' }}</td>
                                <td>{{ number_format($emp->gross_salary, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($emp->joining_date)->format('d M Y') }}</td>
                                <td>
                                    @if($emp->user)
                                    <span class="text-success small" title="{{ $emp->user->email }}">
                                        <i class="bi bi-link-45deg me-1"></i>{{ __('Linked') }}
                                    </span>
                                    @else
                                    <span class="text-gray-400 small">
                                        <i class="bi bi-link-45deg me-1"></i>{{ __('Not Linked') }}
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'active' => 'bg-success-soft text-success',
                                            'inactive' => 'bg-secondary-soft text-secondary',
                                        ];
                                        $badgeClass = $statusColors[$emp->status] ?? 'bg-light text-dark';
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ ucfirst($emp->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('personnel.employees.edit', $emp->id) }}" class="btn btn-sm btn-outline-primary border-0" title="{{ __('Edit') }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        @php $confirmMsg = __('Are you sure you want to delete this employee?'); @endphp
                                        <form action="{{ route('personnel.employees.destroy', $emp->id) }}" method="POST" onsubmit="return confirm('{{ $confirmMsg }}');">
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
                                <td colspan="11" class="text-center py-5">
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
</x-app-layout>