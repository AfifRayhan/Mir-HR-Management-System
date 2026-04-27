<x-app-layout>
    @push('styles')
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @endpush

    @php 
        $isTeamLeadRole = optional(auth()->user()->role)->name === 'Team Lead'; 
        // Need to fetch employee context since it isn't explicitly passed to this blade variable list, but let's check auth user
        $employeeRecord = \App\Models\Employee::where('user_id', auth()->id())->first();
        $isReportingManager = \App\Models\Employee::where('reporting_manager_id', $employeeRecord?->id ?? 0)->exists();
        $isDeptHead = \App\Models\Department::where('incharge_id', $employeeRecord?->id ?? 0)->exists();
        $isTeamLeadLayout = $isTeamLeadRole || $isReportingManager || $isDeptHead;
    @endphp

    <div class="{{ $isTeamLeadLayout ? 'ui-layout' : 'ui-layout' }}">
        @if($isTeamLeadLayout)
            @include('partials.team-lead-sidebar')
        @else
            @include('partials.employee-sidebar')
        @endif

        <main class="{{ $isTeamLeadLayout ? 'ui-main' : 'ui-main' }}">

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
            <div class="ui-panel mb-4">
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
                        <button type="submit" class="btn ui-btn-search">{{ __('Search') }}</button>
                        <a href="{{ route('employee.attendance.index') }}" class="btn ui-btn-clear">{{ __('Clear') }}</a>
                    </div>
                </form>
            </div>

            <!-- Summary Stats -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="ui-metric-card">
                        <div class="ui-metric-icon bg-success-soft text-success">
                            <i class="bi bi-person-check-fill text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="ui-metric-label">{{ __('Present') }}</div>
                            <div class="ui-metric-value">{{ $totalPresent }}</div>
                            <div class="metric-sub">{{ __('Days present') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="ui-metric-card">
                        <div class="ui-metric-icon bg-warning-soft text-warning">
                            <i class="bi bi-clock-history text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="ui-metric-label">{{ __('Late') }}</div>
                            <div class="ui-metric-value">{{ $totalLate }}</div>
                            <div class="metric-sub">{{ __('Days late') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="ui-metric-card">
                        <div class="ui-metric-icon bg-danger-soft text-danger">
                            <i class="bi bi-person-x-fill text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="ui-metric-label">{{ __('Absent') }}</div>
                            <div class="ui-metric-value">{{ $totalAbsent }}</div>
                            <div class="metric-sub">{{ __('Days absent') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="ui-metric-card">
                        <div class="ui-metric-icon bg-info-soft text-info">
                            <i class="bi bi-journal-text text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="ui-metric-label">{{ __('Total Records') }}</div>
                            <div class="ui-metric-value">{{ $totalRecords }}</div>
                            <div class="metric-sub">{{ __('For this period') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Records Table -->
            <div class="ui-panel p-0 overflow-hidden" style="height: auto;">
                <div class="p-4 border-bottom">
                    <h6 class="mb-0 font-bold text-gray-800"><i class="bi bi-table me-2 text-success"></i>{{ __('Attendance Logs') }}</h6>
                </div>
                <div class="table-responsive">
                    <table class="table ui-table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>Day</th>
                                <th>In Time</th>
                                <th>Out Time</th>
                                <th>Working Hours</th>
                                <th>Late (H:M:S)</th>
                                <th class="text-end">Status</th>
                                <th class="pe-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $record)
                                <tr>
                                    <td class="ps-4 fw-bold small">{{ \Carbon\Carbon::parse($record->date)->format('d M Y') }}</td>
                                    <td class="small text-muted">{{ \Carbon\Carbon::parse($record->date)->format('l') }}</td>
                                    <td class="small">
                                        {{ $record->in_time ? \Carbon\Carbon::parse($record->in_time)->format('h:i A') : '-' }}
                                        @if($record->is_manual)
                                            <span class="badge bg-secondary-soft text-secondary ms-1 shadow-sm" style="font-size: 0.65rem;">{{ __('Manual') }}</span>
                                        @endif
                                    </td>
                                    <td class="small">{{ $record->out_time ? \Carbon\Carbon::parse($record->out_time)->format('h:i A') : '-' }}</td>
                                    <td class="small">{{ $record->working_hours ? number_format($record->working_hours, 2) . 'h' : '0.00h' }}</td>
                                    <td class="small">
                                        <span class="{{ $record->late_seconds > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                            {{ $record->late_timing }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @php $lowStatus = strtolower($record->status); @endphp
                                        @if($lowStatus == 'present')
                                            <span class="badge bg-success-soft text-success" style="font-size: 0.7rem;">Present</span>
                                        @elseif($lowStatus == 'late')
                                            <span class="badge bg-warning-soft text-warning" style="font-size: 0.7rem;">Late</span>
                                        @elseif($lowStatus == 'absent')
                                            <span class="badge bg-danger-soft text-danger" style="font-size: 0.7rem;">Absent</span>
                                        @elseif($lowStatus == 'leave')
                                            <span class="badge bg-info-soft text-info" style="font-size: 0.7rem;">On Leave</span>
                                        @elseif($lowStatus == 'holiday')
                                            <span class="badge bg-success-soft text-success" style="font-size: 0.7rem;">Holiday</span>
                                        @elseif($lowStatus == 'weekly_holiday')
                                            <span class="badge bg-success-soft text-success" style="font-size: 0.7rem;">Weekly Holiday</span>
                                        @elseif($lowStatus == 'off_day')
                                            <span class="badge bg-secondary-soft text-secondary" style="font-size: 0.7rem;">Off Day</span>
                                        @else
                                            <span class="badge bg-secondary text-white" style="font-size: 0.7rem;">{{ ucfirst($record->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-center">
                                        @php
                                            $recordDateStr = \Carbon\Carbon::parse($record->date)->format('Y-m-d');
                                            $pendingAdj = $pendingAdjustments[$recordDateStr] ?? null;
                                        @endphp

                                        @if($pendingAdj)
                                            @if($pendingAdj->status === 'pending')
                                                <span class="badge bg-warning text-dark cursor-pointer shadow-sm" style="font-size: 0.7rem;" 
                                                      onclick="showAdjustmentReason('Pending Request', '{{ addslashes($pendingAdj->reason) }}', null, '{{ $recordDateStr }}')">
                                                    <i class="bi bi-hourglass-split me-1"></i>Pending
                                                </span>
                                            @elseif($pendingAdj->status === 'rejected')
                                                <span class="badge bg-danger text-white cursor-pointer shadow-sm" style="font-size: 0.7rem;"
                                                      onclick="showAdjustmentReason('Request Rejected', '{{ addslashes($pendingAdj->reason) }}', '{{ addslashes($pendingAdj->reject_reason) }}', '{{ $recordDateStr }}')">
                                                    <i class="bi bi-x-circle me-1"></i>Rejected
                                                </span>
                                            @elseif($pendingAdj->status === 'approved')
                                                <span class="badge bg-success text-white cursor-pointer shadow-sm" style="font-size: 0.7rem;"
                                                      onclick="showAdjustmentReason('Request Approved', '{{ addslashes($pendingAdj->reason) }}', null, '{{ $recordDateStr }}')">
                                                    <i class="bi bi-check-circle me-1"></i>Approved
                                                </span>
                                            @endif
                                        @elseif(strtolower($record->status) == 'absent')
                                            <a href="{{ route('employee.attendance.adjust', ['date' => $recordDateStr]) }}" class="btn btn-sm btn-outline-primary border-0" title="{{ __('Edit') }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
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

        function showAdjustmentReason(title, reason, rejectReason, date) {
            let html = `
                <div class="text-start">
                    <div class="mb-3">
                        <label class="fw-bold small text-muted text-uppercase d-block mb-1">Your Reason:</label>
                        <div class="p-3 bg-light rounded border small text-gray-700">${reason}</div>
                    </div>`;
            
            if (rejectReason) {
                html += `
                    <div class="mb-0">
                        <label class="fw-bold small text-muted text-uppercase text-danger d-block mb-1">Rejection Reason:</label>
                        <div class="p-3 bg-danger-soft text-danger rounded border border-danger-subtle small">${rejectReason}</div>
                    </div>`;
            }
            
            html += `</div>`;

            Swal.fire({
                title: `<span class="fw-bold">${title}</span>`,
                html: html,
                icon: rejectReason ? 'error' : (title.includes('Approved') ? 'success' : 'info'),
                showDenyButton: !!rejectReason,
                confirmButtonText: 'Close',
                denyButtonText: 'Apply Again',
                customClass: {
                    actions: 'd-flex gap-2',
                    confirmButton: 'btn btn-primary rounded-pill px-4',
                    denyButton: 'btn btn-outline-primary rounded-pill px-4'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isDenied && date) {
                    window.location.href = `{{ route('employee.attendance.adjust') }}?date=${date}`;
                }
            });
        }
    </script>
    <style>
        .cursor-pointer { cursor: pointer; }
        .bg-danger-soft { background-color: #fee2e2; }
        .swal2-actions { 
            display: flex !important; 
            flex-direction: row-reverse !important; 
            gap: 15px !important; 
            justify-content: center !important; 
            margin-top: 25px !important;
            border-top: none !important;
        }
        .swal2-confirm, .swal2-deny {
            min-width: 110px !important;
            padding: 10px 24px !important;
        }
    </style>
    @endpush
</x-app-layout>




