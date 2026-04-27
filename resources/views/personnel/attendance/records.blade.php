<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attendance Records') }}
        </h2>
    </x-slot>

    @push('styles')
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <style>
        .ts-control { border-radius: 0.5rem !important; padding: 0.5rem 0.75rem !important; }
        .ts-dropdown { border-radius: 0.5rem !important; margin-top: 5px !important; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1) !important; }
    </style>
    @endpush

    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="mb-0">{{ __('Attendance Records') }}</h4>
                        <p class="text-muted small">{{ __('Search and view attendance history for individual employees.') }}</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="ui-panel mb-4">
                    <form action="{{ route('personnel.attendances.records') }}" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('Select Employee') }}</label>
                            <select id="employee_select" name="employee_id" class="form-select" required>
                                <option value="">{{ __('Search by name or code...') }}</option>
                                @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }} ({{ $emp->employee_code }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">{{ __('From Date') }}</label>
                            <input type="text" id="from_date" name="from_date" class="form-control date-picker" value="{{ $fromDateStr }}" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">{{ __('To Date') }}</label>
                            <input type="text" id="to_date" name="to_date" class="form-control date-picker" value="{{ $toDateStr }}" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">{{ __('Status') }}</label>
                            <select name="status" class="form-select">
                                <option value="">{{ __('All Status') }}</option>
                                <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>{{ __('Present') }}</option>
                                <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>{{ __('Late') }}</option>
                                <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>{{ __('Absent') }}</option>
                                <option value="leave" {{ request('status') == 'leave' ? 'selected' : '' }}>{{ __('Leave') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn ui-btn-search flex-grow-1">{{ __('Search') }}</button>
                            <a href="{{ route('personnel.attendances.records') }}" class="btn ui-btn-clear flex-grow-1">{{ __('Clear') }}</a>
                        </div>
                    </form>
                </div>

                @if($selectedEmployee)
                <!-- Stats Summary -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="ui-panel text-center p-3 border-start border-4 border-primary">
                            <div class="text-muted small mb-1">{{ __('Total Records') }}</div>
                            <div class="h4 mb-0 fw-bold">{{ $stats['totalRecords'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="ui-panel text-center p-3 border-start border-4 border-success">
                            <div class="text-muted small mb-1">{{ __('Present') }}</div>
                            <div class="h4 mb-0 fw-bold text-success">{{ $stats['totalPresent'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="ui-panel text-center p-3 border-start border-4 border-warning">
                            <div class="text-muted small mb-1">{{ __('Late') }}</div>
                            <div class="h4 mb-0 fw-bold text-warning">{{ $stats['totalLate'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="ui-panel text-center p-3 border-start border-4 border-danger">
                            <div class="text-muted small mb-1">{{ __('Absent') }}</div>
                            <div class="h4 mb-0 fw-bold text-danger">{{ $stats['totalAbsent'] }}</div>
                        </div>
                    </div>
                </div>

                <!-- Records Table -->
                <div class="ui-panel">
                    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                        <h5 class="mb-0">
                            <i class="bi bi-person-badge me-2 text-success"></i>
                            {{ $selectedEmployee->name }}'s {{ __('Attendance History') }}
                        </h5>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Day') }}</th>
                                    <th>{{ __('In Time') }}</th>
                                    <th>{{ __('Out Time') }}</th>
                                    <th>{{ __('Working Hours') }}</th>
                                    <th>{{ __('Late Time') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($records as $record)
                                <tr>
                                    <td class="fw-semibold">{{ $record->date->format('d M Y') }}</td>
                                    <td class="text-muted">{{ $record->date->format('l') }}</td>
                                    <td>
                                        {{ $record->in_time ? $record->in_time->format('h:i A') : '-' }}
                                        @if($record->is_manual)
                                            <span class="badge bg-secondary-soft text-secondary ms-1 shadow-sm" style="font-size: 0.65rem;">{{ __('Manual') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $record->out_time ? $record->out_time->format('h:i A') : '-' }}</td>
                                    <td>{{ $record->working_hours ? $record->working_hours . 'h' : '-' }}</td>
                                    <td>
                                        @if($record->late_seconds > 0)
                                            <span class="text-danger fw-bold">{{ $record->late_timing }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                        $statusClass = [
                                            'present' => 'bg-success',
                                            'late' => 'bg-warning text-dark',
                                            'absent' => 'bg-danger',
                                            'leave' => 'bg-info text-dark',
                                        ][strtolower($record->status)] ?? 'bg-secondary';
                                        @endphp
                                        <span class="badge {{ $statusClass }}">
                                            {{ ucfirst($record->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="bi bi-inbox h2 text-muted d-block mb-3"></i>
                                        <span class="text-muted">{{ __('No attendance records found for this selection.') }}</span>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($records->hasPages())
                    <div class="mt-4 px-2">
                        {{ $records->links() }}
                    </div>
                    @endif
                </div>
                @else
                <div class="ui-panel text-center py-5">
                    <div class="py-5">
                        <i class="bi bi-search display-4 text-muted mb-3 d-block"></i>
                        <h5>{{ __('Please select an employee to view their records') }}</h5>
                        <p class="text-muted">{{ __('Use the search box above to get started.') }}</p>
                    </div>
                </div>
                @endif
            </div>
        </main>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Employee Search Dropdown
            new TomSelect('#employee_select', {
                create: false,
                sortField: { field: "text", direction: "asc" }
            });

            // Date Pickers
            flatpickr('.date-picker', {
                dateFormat: 'Y-m-d',
                allowInput: false
            });
        });
    </script>
    @endpush
</x-app-layout>




