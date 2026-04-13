<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Employee Dashboard') }}
        </h2>
    </x-slot>

    <!-- Specific styles for this dashboard -->
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css', 'resources/css/custom-employee-dashboard.css'])
    <style>
        @keyframes highlightFade {
            0% { background-color: #fef3c7; box-shadow: 0 0 20px rgba(245, 158, 11, 0.3); }
            100% { background-color: transparent; box-shadow: none; }
        }
        .highlight-section {
            animation: highlightFade 4s ease-out forwards;
            border-radius: 1rem;
        }
    </style>
    @endpush

    @php 
        $isTeamLeadRole = optional(auth()->user()->role)->name === 'Team Lead';
        $isReportingManager = $isReportingManager ?? false;
        $isTeamLeadLayout = $isTeamLeadRole || $isReportingManager;
    @endphp

    <div class="{{ $isTeamLeadLayout ? 'hr-layout' : 'emp-layout' }}">
        @if($isTeamLeadLayout)
        @include('partials.team-lead-sidebar')
        @else
        @include('partials.employee-sidebar')
        @endif

        <main class="{{ $isTeamLeadLayout ? 'hr-main' : 'emp-main' }}">
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ __('Employee Dashboard') }}</h5>
                        <p class="mb-0 small text-muted">
                            {{ __('Welcome,') }}
                            {{ $employee ? $employee->name : ($user->name ?? __('Employee')) }}
                            @if($employee)
                                • {{ $employee->designation->name ?? 'No Designation' }} 
                                • {{ $employee->department->name ?? 'No Department' }} 
                                • ID: {{ $employee->employee_code }}
                            @else
                                • {{ $roleName }}
                            @endif
                        </p>
                    </div>
                    <div class="text-end text-sm text-gray-500">
                        <i class="bi bi-calendar-event me-2 text-success"></i>{{ now()->format('l, d M Y') }}
                    </div>
                </div>
            </div>
            

            <!-- Employee Summary Metrics -->
            <div class="row g-4 mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="hr-metric-card">
                        <div class="metric-icon bg-success-soft text-success">
                            <i class="bi bi-person-check-fill text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Present Days') }}</div>
                            <div class="metric-value">{{ $presentDays }}</div>
                            <div class="metric-sub">{{ __("This month " . "(" . now()->format('F') . ")") }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="hr-metric-card">
                        <div class="metric-icon bg-warning-soft text-warning">
                            <i class="bi bi-clock-history text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Late Days') }}</div>
                            <div class="metric-value">{{ $lateDays }}</div>
                            <div class="metric-sub">{{ __("This month " . "(" . now()->format('F') . ")") }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="hr-metric-card">
                        <div class="metric-icon bg-danger-soft text-danger">
                            <i class="bi bi-person-x-fill text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Absent Days') }}</div>
                            <div class="metric-value">{{ $absentDays }}</div>
                            <div class="metric-sub">{{ __("This month " . "(" . now()->format('F') . ")") }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="hr-metric-card">
                        <div class="metric-icon bg-info-soft text-info">
                            <i class="bi bi-calendar2-range text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Leaves Taken') }}</div>
                            <div class="metric-value">{{ $approvedLeaves }}</div>
                            <div class="metric-sub">{{ __("This year " . "(" . now()->format('Y') . ")") }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Leave Balance Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="row align-items-center mb-3">
                        <div class="col">
                            <h6 class="font-bold text-gray-800 mb-0">
                                <i class="bi bi-calendar-check me-2 text-success"></i>{{ __('My Leave Balances') }}
                            </h6>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('employee.leave.index') }}" class="btn btn-success btn-sm text-white px-3 font-bold rounded-pill btn-pill-action flex-shrink-0">
                                <i class="bi bi-plus-circle me-1"></i>{{ __('Apply Leave') }}
                            </a>
                        </div>
                    </div>
                </div>
                @forelse($leaveBalances as $balance)
                <div class="col-md-3 col-sm-6">
                    <div class="balance-card h-100 shadow-sm border-0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-uppercase fw-bold text-muted" style="font-size: 0.65rem; letter-spacing: 0.05em;">{{ $balance->leaveType->name }}</span>
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                                <i class="bi bi-calculator text-success" style="font-size: 0.75rem;"></i>
                            </div>
                        </div>
                        <div class="fw-bold text-dark mb-1" style="font-size: 1.5rem; line-height: 1;">{{ $balance->remaining_days }}</div>
                        <div class="text-muted mb-2" style="font-size: 0.7rem;">{{ __('Days Remaining') }}</div>
                        <div class="pt-2 border-top">
                            <div class="d-flex justify-content-between text-muted" style="font-size: 0.65rem;">
                                <span>{{ __('Used:') }} <span class="fw-bold text-dark">{{ $balance->used_days }}</span></span>
                                <span>{{ __('Total:') }} <span class="fw-bold text-dark">{{ $balance->opening_balance }}</span></span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info border-0 shadow-sm rounded-4 small">
                        <i class="bi bi-info-circle-fill me-2"></i>{{ __('No leave balances initialized.') }}
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Bottom Row: Recent Attendance + Holidays -->
            <div class="row g-4 mb-4">
                <!-- Recent Attendance Table -->
                <div class="col-lg-8">
                    <div class="hr-panel p-0 overflow-hidden">
                        <div class="p-4 border-bottom d-flex align-items-center">
                            <h6 class="mb-0 font-bold text-gray-800 flex-grow-1"><i class="bi bi-activity me-2 text-success"></i>{{ __('Monthly Attendance') }} ({{ now()->format('F') }})</h6>
                        </div>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table hr-table mb-0">
                                <thead style="position: sticky; top: 0; background: #f8fafc; z-index: 1;">
                                    <tr>
                                        <th class="ps-4">{{ __('Date') }}</th>
                                        <th>{{ __('In Time') }}</th>
                                        <th>{{ __('Out Time') }}</th>
                                        <th class="pe-4 text-end">{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($fullMonthAttendance as $record)
                                    <tr>
                                        <td class="ps-4 small">{{ $record->date->format('d M Y') }}</td>
                                        <td class="small">{{ $record->in_time ? \Carbon\Carbon::parse($record->in_time)->format('h:i A') : '--' }}</td>
                                        <td class="small">{{ $record->out_time ? \Carbon\Carbon::parse($record->out_time)->format('h:i A') : '--' }}</td>
                                        <td class="pe-4 text-end">
                                            @if($record->status === 'absent')
                                                <span class="badge bg-danger-soft text-danger" style="font-size: 0.7rem;">{{ __('Absent') }}</span>
                                            @elseif($record->status === 'leave')
                                                <span class="badge bg-info-soft text-info" style="font-size: 0.7rem;">{{ __('Leave') }}</span>
                                            @elseif($record->late_seconds > 0)
                                                <span class="badge bg-warning-soft text-warning" style="font-size: 0.7rem;">{{ __('Late') }} ({{ $record->late_timing }})</span>
                                            @else
                                                <span class="badge bg-success-soft text-success" style="font-size: 0.7rem;">{{ __('Present') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">{{ __('No attendance records found.') }}</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
 
                <!-- Sidebar Column -->
                <div class="col-lg-4">
                    @if($isTeamLeadLayout)
                    <!-- Pending Leave Requests (Team Lead & Reporting Managers) -->
                    <div class="hr-panel mb-4 shadow-sm">
                        <h6 class="font-bold text-gray-800 mb-3"><i class="bi bi-envelope-exclamation me-2 text-warning"></i>{{ __('Pending Leave Requests') }}</h6>
                        <div class="d-flex align-items-center justify-content-between p-3 bg-warning-soft rounded-4 border-start border-warning border-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-inboxes text-3xl text-warning me-3"></i>
                                <div>
                                    <div class="h4 mb-0 font-bold text-warning">{{ $pendingTeamLeavesCount }}</div>
                                    <div class="small text-warning-emphasis">{{ __('New Applications') }}</div>
                                </div>
                            </div>
                            <a href="{{ route('team-lead.leave-applications.index') }}" class="btn btn-outline-success btn-sm px-3 font-bold rounded-pill btn-pill-action">{{ __('Process') }}</a>
                        </div>
                    </div>
                    @endif

                    <!-- Supervisor Remarks -->
                    <div class="hr-panel mb-4 shadow-sm" id="supervisor-remarks">
                        <h6 class="font-bold text-gray-800 mb-3 small uppercase tracking-wider">
                            <i class="bi bi-chat-left-text me-2 text-warning"></i>{{ __('Supervisor Remarks') }}
                        </h6>
                        <div class="remark-scroll-container" style="max-height: 180px; overflow-y: auto; overflow-x: hidden;">
                            <ul class="hr-list px-2">
                                @forelse($supervisorRemarks as $remark)
                                <li class="small mb-2 border-bottom-0 pb-1">
                                    <a href="#" class="text-decoration-none d-block remark-item" 
                                       data-bs-toggle="modal" 
                                       data-bs-target="#remarkModal" 
                                       data-title="{{ $remark->title }}" 
                                       data-message="{{ $remark->message }}"
                                       data-date="{{ $remark->created_at->format('d M Y, h:i A') }}"
                                       data-supervisor="{{ $remark->supervisor->name }}">
                                        <div class="d-flex justify-content-between align-items-center mb-0">
                                            <span class="fw-bold text-gray-700 text-success text-truncate" style="max-width: 220px;">{{ Str::limit($remark->title, 40) }}</span>
                                            <i class="bi bi-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                        </div>
                                        <div class="text-muted d-flex align-items-center" style="font-size: 0.7rem;">
                                            <span>{{ $remark->supervisor->name }}</span>
                                            <span class="mx-1">•</span>
                                            <span>{{ $remark->created_at->diffForHumans() }}</span>
                                        </div>
                                    </a>
                                </li>
                                @empty
                                <li class="small text-center text-muted py-2">{{ __('No remarks from supervisor yet.') }}</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <!-- Upcoming Holidays -->
                    <div class="hr-panel mb-4 shadow-sm">
                        <h6 class="font-bold text-gray-800 mb-3"><i class="bi bi-calendar-check me-2 text-info"></i>{{ __('Upcoming Holidays') }}</h6>
                        <div class="holiday-scroll-container" style="max-height: 180px; overflow-y: auto; overflow-x: hidden;">
                            <ul class="hr-list px-2">
                                @forelse($upcomingHolidays as $holiday)
                                <li class="small d-flex justify-content-between border-bottom-0 pb-1 mb-2">
                                    <div>
                                        <div class="font-bold text-gray-700">{{ $holiday->title }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $holiday->from_date->format('d M') }} @if($holiday->total_days > 1) - {{ $holiday->to_date->format('d M') }} @endif</div>
                                    </div>
                                    <span class="badge bg-info-soft text-info align-self-center" style="font-size: 0.7rem;">{{ $holiday->total_days }} {{ __('Day(s)') }}</span>
                                </li>
                                @empty
                                <li class="small text-center text-muted py-2">{{ __('No upcoming holidays.') }}</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
 
                    <!-- Notices & Events -->
                    <div class="hr-panel mb-4 shadow-sm" id="notices-events">
                        <h6 class="font-bold text-gray-800 mb-3"><i class="bi bi-megaphone me-2 text-success"></i>{{ __('Notices & Events') }}</h6>
                        <div class="notice-scroll-container" style="max-height: 250px; overflow-y: auto; overflow-x: hidden;">
                            <ul class="hr-list px-2">
                                @forelse($activeNotices as $notice)
                                <li class="small mb-3 border-bottom-0 pb-2">
                                    <a href="#" class="text-decoration-none d-block notice-item" 
                                       data-bs-toggle="modal" 
                                       data-bs-target="#noticeModal" 
                                       data-title="{{ $notice->title }}" 
                                       data-content="{{ $notice->content }}"
                                       data-type="{{ ucfirst($notice->type) }}"
                                       data-date="{{ $notice->created_at->format('d M Y, h:i A') }}">
                                        <div class="d-flex justify-content-between align-items-center mb-0">
                                            <span class="fw-bold text-gray-800 text-success text-truncate" style="max-width: 220px;">{{ Str::limit($notice->title, 40) }}</span>
                                            <i class="bi bi-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                        </div>
                                        <p class="text-muted mb-1 text-truncate" style="font-size: 0.75rem; line-height: 1.4; max-width: 250px;">{{ Str::limit($notice->content, 70) }}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="text-muted" style="font-size: 0.65rem;">
                                                <i class="bi bi-clock me-1"></i>{{ $notice->created_at->diffForHumans() }}
                                            </div>
                                            @if($notice->type === 'event')
                                                <span class="badge bg-success-soft text-success" style="font-size: 0.6rem;">{{ __('Event') }}</span>
                                            @else
                                                <span class="badge bg-info-soft text-info" style="font-size: 0.6rem;">{{ __('Notice') }}</span>
                                            @endif
                                        </div>
                                    </a>
                                </li>
                                @empty
                                <li class="small text-center text-muted py-2">{{ __('No active notices.') }}</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <!-- Supervisor Remark Modal -->
    <div class="modal fade" id="remarkModal" tabindex="-1" aria-labelledby="remarkModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-0">
                    <h5 class="modal-title fw-bold" id="remarkModalLabel">{{ __('Supervisor Remark') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-grow-1">
                            <h5 id="modalRemarkTitle" class="fw-bold mb-0 text-success" style="overflow-wrap: anywhere;"></h5>
                            <div class="text-muted small mt-1">
                                <span id="modalRemarkSupervisor" class="fw-bold text-dark"></span>
                                <span class="mx-1">•</span>
                                <span id="modalRemarkDate"></span>
                            </div>
                        </div>
                    </div>
                    <hr class="my-3 opacity-10">
                    <div id="modalRemarkMessage" class="text-gray-700 font-medium" style="line-height: 1.6; white-space: pre-wrap; overflow-wrap: anywhere;"></div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notice & Event Modal -->
    <div class="modal fade" id="noticeModal" tabindex="-1" aria-labelledby="noticeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-0">
                    <h5 class="modal-title fw-bold" id="noticeModalLabel">{{ __('Notice & Event Detail') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-grow-1">
                            <h5 id="modalNoticeTitle" class="fw-bold mb-0 text-success" style="overflow-wrap: anywhere;"></h5>
                            <div class="text-muted small mt-2">
                                <span id="modalNoticeType" class="badge bg-success-soft text-success"></span>
                                <span class="mx-1">•</span>
                                <span id="modalNoticeDate" class="text-muted"></span>
                            </div>
                        </div>
                    </div>
                    <hr class="my-3 opacity-10">
                    <div id="modalNoticeContent" class="text-gray-700 font-medium" style="line-height: 1.6; white-space: pre-wrap; overflow-wrap: anywhere;"></div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Generic highlight logic for deep-linked sections (e.g., #supervisor-remarks, #notices-events)
            const hash = window.location.hash;
            if (hash) {
                const targetSection = document.querySelector(hash);
                if (targetSection) {
                    targetSection.classList.add('highlight-section');
                    // Smooth scroll to the section
                    setTimeout(() => {
                        targetSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 300);
                }
            }

            const remarkModal = document.getElementById('remarkModal');
            if (remarkModal) {
                remarkModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const title = button.getAttribute('data-title');
                    const message = button.getAttribute('data-message');
                    const date = button.getAttribute('data-date');
                    const supervisor = button.getAttribute('data-supervisor');

                    document.getElementById('modalRemarkTitle').textContent = title;
                    document.getElementById('modalRemarkMessage').textContent = message;
                    document.getElementById('modalRemarkDate').textContent = date;
                    document.getElementById('modalRemarkSupervisor').textContent = supervisor;
                });
            }

            const noticeModal = document.getElementById('noticeModal');
            if (noticeModal) {
                noticeModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    document.getElementById('modalNoticeTitle').textContent = button.getAttribute('data-title');
                    document.getElementById('modalNoticeContent').textContent = button.getAttribute('data-content');
                    document.getElementById('modalNoticeDate').textContent = button.getAttribute('data-date');
                    document.getElementById('modalNoticeType').textContent = button.getAttribute('data-type');
                });
            }
        });
    </script>
    @endpush
</x-app-layout>