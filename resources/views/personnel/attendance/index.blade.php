<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daily Attendance') }}
        </h2>
    </x-slot>

    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ __('Attendance Records') }}</h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('personnel.attendances.adjust') }}" class="btn btn-outline-primary">
                                <i class="bi bi-pencil-square me-2"></i>{{ __('Manual Adjustment') }}
                            </a>
                            <form action="{{ route('personnel.attendances.process') }}" method="POST">
                                @csrf
                                <input type="hidden" name="date" value="{{ $date }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-arrow-repeat me-2"></i>{{ __('Process Logs') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="hr-panel mb-4">
                    <form action="{{ route('personnel.attendances.index') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Date') }}</label>
                            <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Search Employee') }}</label>
                            <input type="text" name="search" class="form-control" placeholder="{{ __('Name or Employee Code') }}" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Office') }}</label>
                            <select name="office_id" class="form-select" onchange="this.form.submit()">
                                <option value="">{{ __('All Offices') }}</option>
                                @foreach($offices as $office)
                                <option value="{{ $office->id }}" {{ request('office_id') == $office->id ? 'selected' : '' }}>
                                    {{ $office->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Department') }}</label>
                            <select name="department_id" class="form-select" onchange="this.form.submit()">
                                <option value="">{{ __('All Departments') }}</option>
                                @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">{{ __('All') }}</option>
                                @foreach($statuses as $s)
                                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                                    {{ ucfirst($s) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="hr-panel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
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
                                @forelse($records as $record)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="ms-2">
                                                <div class="fw-bold text-dark">{{ $record->employee->name }}</div>
                                                <div class="small text-muted">{{ $record->employee->employee_code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">{{ $record->employee->department->name ?? 'N/A' }}</div>
                                        <div class="small text-muted">{{ $record->employee->designation->name ?? 'N/A' }}</div>
                                    </td>
                                    <td>{{ $record->in_time ? $record->in_time->format('H:i') : '-' }}</td>
                                    <td>{{ $record->out_time ? $record->out_time->format('H:i') : '-' }}</td>
                                    <td>{{ $record->working_hours }}h</td>
                                    <td>
                                        <span class="{{ $record->late_seconds > 0 ? 'text-danger fw-bold' : '' }}">
                                            {{ $record->late_timing }}
                                        </span>
                                    </td>
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
                                    <td colspan="7" class="text-center py-4 text-muted">{{ __('No attendance records found for this date.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>