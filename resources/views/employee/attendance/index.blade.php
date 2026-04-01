<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css', 'resources/css/custom-employee-dashboard.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @endpush

    @php $isTeamLead = optional(auth()->user()->role)->name === 'Team Lead'; @endphp

    <div class="{{ $isTeamLead ? 'hr-layout' : 'emp-layout' }}">
        @if($isTeamLead)
            @include('partials.team-lead-sidebar')
        @else
            @include('partials.employee-sidebar')
        @endif

        <main class="{{ $isTeamLead ? 'hr-main' : 'emp-main' }}">

            <div class="row mb-4 align-items-center">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1">{{ __('My Attendance') }}</h4>
                        <p class="text-muted mb-0 small">{{ __('View your attendance history and filter by date range') }}</p>
                    </div>
                    <div class="text-end text-sm text-gray-500">
                        <i class="bi bi-calendar-event me-2"></i>{{ now()->format('l, d M Y') }}
                    </div>
                </div>
            </div>

            <!-- Filters Panel -->
            <div class="hr-panel mb-4">
                <form action="{{ route('employee.attendance.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">{{ __('From Date') }}</label>
                        <input type="text" id="att_from_date" name="from_date" class="form-control rounded-3" value="{{ request('from_date', $fromDateStr) }}" placeholder="Select from date" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">{{ __('To Date') }}</label>
                        <input type="text" id="att_to_date" name="to_date" class="form-control rounded-3" value="{{ request('to_date', $toDateStr) }}" placeholder="Select to date" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">{{ __('Status') }}</label>
                        <select name="status" class="form-select rounded-3">
                            <option value="">{{ __('All Statuses') }}</option>
                            <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>{{ __('Present') }}</option>
                            <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>{{ __('Late') }}</option>
                            <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>{{ __('Absent') }}</option>
                            <option value="leave" {{ request('status') === 'leave' ? 'selected' : '' }}>{{ __('On Leave') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-funnel me-1"></i>{{ __('Filter') }}
                        </button>
                        <a href="{{ route('employee.attendance.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Summary Stats -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="hr-metric-card">
                        <div class="metric-icon bg-success-soft text-success">
                            <i class="bi bi-person-check-fill text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Present') }}</div>
                            <div class="metric-value">{{ $totalPresent }}</div>
                            <div class="metric-sub">{{ __('Days present') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="hr-metric-card">
                        <div class="metric-icon bg-warning-soft text-warning">
                            <i class="bi bi-clock-history text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Late') }}</div>
                            <div class="metric-value">{{ $totalLate }}</div>
                            <div class="metric-sub">{{ __('Days late') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="hr-metric-card">
                        <div class="metric-icon bg-danger-soft text-danger">
                            <i class="bi bi-person-x-fill text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Absent') }}</div>
                            <div class="metric-value">{{ $totalAbsent }}</div>
                            <div class="metric-sub">{{ __('Days absent') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="hr-metric-card">
                        <div class="metric-icon bg-info-soft text-info">
                            <i class="bi bi-journal-text text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Total Records') }}</div>
                            <div class="metric-value">{{ $totalRecords }}</div>
                            <div class="metric-sub">{{ __('For this period') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Records Table -->
            <div class="hr-panel p-0 overflow-hidden" style="height: auto;">
                <div class="p-4 border-bottom">
                    <h6 class="mb-0 font-bold text-gray-800"><i class="bi bi-table me-2 text-success"></i>{{ __('Attendance Logs') }}</h6>
                </div>
                <div class="table-responsive">
                    <table class="table hr-table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>Day</th>
                                <th>In Time</th>
                                <th>Out Time</th>
                                <th>Working Hours</th>
                                <th>Late (H:M:S)</th>
                                <th class="pe-4 text-end">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $record)
                                <tr>
                                    <td class="ps-4 fw-bold small">{{ \Carbon\Carbon::parse($record->date)->format('d M Y') }}</td>
                                    <td class="small text-muted">{{ \Carbon\Carbon::parse($record->date)->format('l') }}</td>
                                    <td class="small">{{ $record->in_time ? \Carbon\Carbon::parse($record->in_time)->format('h:i A') : '-' }}</td>
                                    <td class="small">{{ $record->out_time ? \Carbon\Carbon::parse($record->out_time)->format('h:i A') : '-' }}</td>
                                    <td class="small">{{ $record->working_hours ? number_format($record->working_hours, 2) . 'h' : '0.00h' }}</td>
                                    <td class="small">
                                        <span class="{{ $record->late_seconds > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                            {{ $record->late_timing }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        @if(strtolower($record->status) == 'present')
                                            <span class="badge bg-success-soft text-success" style="font-size: 0.7rem;">Present</span>
                                        @elseif(strtolower($record->status) == 'late')
                                            <span class="badge bg-warning-soft text-warning" style="font-size: 0.7rem;">Late</span>
                                        @elseif(strtolower($record->status) == 'absent')
                                            <span class="badge bg-danger-soft text-danger" style="font-size: 0.7rem;">Absent</span>
                                        @elseif(strtolower($record->status) == 'leave')
                                            <span class="badge bg-info-soft text-info" style="font-size: 0.7rem;">On Leave</span>
                                        @else
                                            <span class="badge bg-secondary text-white" style="font-size: 0.7rem;">{{ ucfirst($record->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                        No attendance records found for the selected period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($records->hasPages())
                    <div class="px-4 py-3 border-top bg-light">
                        {{ $records->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>

        </main>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('#att_from_date', {
                dateFormat: 'Y-m-d',
                allowInput: false,
            });
            flatpickr('#att_to_date', {
                dateFormat: 'Y-m-d',
                allowInput: false,
            });
        });
    </script>
    @endpush
</x-app-layout>
