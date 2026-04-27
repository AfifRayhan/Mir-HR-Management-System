<x-app-layout>
    <!-- Specific styles for this dashboard -->
    

    <div class="ui-layout ui-scope-admin">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ __('HR Dashboard') }}</h5>
                        <p class="mb-0 small text-muted">
                            {{ __('Welcome,') }}
                            {{ $employee ? $employee->name : ($user->name ?? __('HR Administrator')) }}
                            • {{ $roleName }}
                        </p>
                    </div>
                    <div class="text-end text-sm text-gray-500">
                        <i class="bi bi-calendar-event me-2 text-success"></i>{{ now()->format('l, d M Y') }}
                    </div>
                </div>
            </div>

            <!-- Status and HR Metrics -->
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-5 g-4 mb-4">
                <div class="col">
                    <div class="ui-metric-card px-3 py-4 gap-3">
                        <div class="ui-metric-icon bg-primary-soft text-primary">
                            <i class="bi bi-people-fill text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="ui-metric-label">{{ __('Total Staff') }}</div>
                            <div class="ui-metric-value">{{ $totalEmployees }}</div>
                            <div class="metric-sub">{{ __('Registered') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="ui-metric-card px-3 py-4 gap-3">
                        <div class="ui-metric-icon bg-success-soft text-success">
                            <i class="bi bi-person-check-fill text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="ui-metric-label">{{ __('Present Today') }}</div>
                            <div class="ui-metric-value">{{ $presentToday }}</div>
                            <div class="metric-sub">{{ __('Clocked-in') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="ui-metric-card px-3 py-4 gap-3">
                        <div class="ui-metric-icon bg-danger-soft text-danger">
                            <i class="bi bi-person-x-fill text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="ui-metric-label">{{ __('Absent Today') }}</div>
                            <div class="ui-metric-value">{{ $absentToday }}</div>
                            <div class="metric-sub">{{ __('Not in office') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="ui-metric-card px-3 py-4 gap-3">
                        <div class="ui-metric-icon bg-warning-soft text-warning">
                            <i class="bi bi-clock-history text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="ui-metric-label">{{ __('Late Today') }}</div>
                            <div class="ui-metric-value">{{ $lateToday }}</div>
                            <div class="metric-sub">{{ __('After threshold') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="ui-metric-card px-3 py-4 gap-3">
                        <div class="ui-metric-icon bg-info-soft text-info">
                            <i class="bi bi-calendar2-range text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="ui-metric-label">{{ __('On Leave') }}</div>
                            <div class="ui-metric-value">{{ $onLeaveToday }}</div>
                            <div class="metric-sub">{{ __('Approved') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Panels -->
            <div class="row g-4 mb-4">
                <!-- Recent Attendance Summary & Chart -->
                <div class="col-lg-8">
                    <!-- Office Attendance Chart -->
                    <div class="ui-panel p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="mb-0 font-bold text-gray-800"><i class="bi bi-bar-chart-fill me-2 text-success"></i>{{ __('Office Attendance Overview') }}</h6>
                        </div>
                        <div style="height: 250px; position: relative;">
                            <canvas id="officeAttendanceChart"></canvas>
                        </div>
                    </div>

                    <div class="ui-panel p-0 overflow-hidden">
                        <div class="p-4 border-bottom d-flex align-items-center">
                            <h6 class="mb-0 font-bold text-gray-800 flex-grow-1"><i class="bi bi-activity me-2 text-success"></i>{{ __('Recent Attendance Summary') }}</h6>
                            <a href="{{ route('personnel.attendances.index') }}" class="btn btn-success btn-sm text-white px-3 font-bold rounded-pill btn-pill-action flex-shrink-0">{{ __('View All') }}</a>
                        </div>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table ui-table mb-0">
                                <thead style="position: sticky; top: 0; background: #f8fafc; z-index: 1;">
                                    <tr>
                                        <th class="ps-4">{{ __('Employee') }}</th>
                                        <th>{{ __('In Time') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th class="pe-4 text-end">{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentAttendance as $record)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="emp-avatar-sm me-2" style="width: 30px; height: 30px; font-size: 0.75rem;">
                                                    {{ strtoupper(substr($record->employee->name, 0, 1)) }}
                                                </div>
                                                <span class="small font-bold">{{ $record->employee->name }}</span>
                                            </div>
                                        </td>
                                        <td class="small">
                                            {{ $record->in_time ? \Carbon\Carbon::parse($record->in_time)->format('h:i A') : '-' }}
                                            @if($record->is_manual)
                                            <span class="badge bg-secondary-soft text-secondary ms-1 shadow-sm" style="font-size: 0.65rem;">{{ __('Manual') }}</span>
                                            @endif
                                        </td>
                                        <td class="small">
                                            @if($record->status == 'absent')
                                            <span class="text-danger"><i class="bi bi-x-circle me-1"></i>{{ __('Absent') }}</span>
                                            @elseif($record->status == 'leave')
                                            <span class="text-info"><i class="bi bi-calendar2-range me-1"></i>{{ __('On Leave') }}</span>
                                            @elseif($record->late_seconds > 0)
                                            <span class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>{{ __('Late') }} ({{ $record->late_timing }})</span>
                                            @else
                                            <span class="text-success"><i class="bi bi-check-circle me-1"></i>{{ __('On Time') }}</span>
                                            @endif
                                        </td>
                                        <td class="pe-4 text-end">
                                            @if($record->status == 'absent')
                                            <span class="badge bg-danger-soft text-danger" style="font-size: 0.7rem;">Absent</span>
                                            @elseif($record->status == 'leave')
                                            <span class="badge bg-info-soft text-info" style="font-size: 0.7rem;">Leave</span>
                                            @else
                                            <span class="badge bg-success-soft text-success" style="font-size: 0.7rem;">In</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">{{ __('No logs recorded today.') }}</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Side Panels -->
                <div class="col-lg-4">
                    <!-- Pending Leave Requests -->
                    <div class="ui-panel mb-4">
                        <h6 class="font-bold text-gray-800 mb-3"><i class="bi bi-envelope-exclamation me-2 text-warning"></i>{{ __('Pending Leave Requests') }}</h6>
                        <div class="d-flex align-items-center justify-content-between p-3 bg-warning-soft rounded-4 border-start border-warning border-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-inboxes text-3xl text-warning me-3"></i>
                                <div>
                                    <div class="h4 mb-0 font-bold text-warning">{{ $pendingLeavesCount }}</div>
                                    <div class="small text-warning-emphasis">{{ __('New Applications') }}</div>
                                </div>
                            </div>
                            <a href="{{ route('personnel.leave-applications.index') }}" class="btn btn-outline-success btn-sm px-3 font-bold rounded-pill btn-pill-action">{{ __('Process') }}</a>
                        </div>
                    </div>

                    <!-- Upcoming Holidays -->
                    <div class="ui-panel mb-4">
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
                                <li class="small text-center text-muted">{{ __('No upcoming holidays.') }}</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <!-- Upcoming Birthdays -->
                    <div class="ui-panel mb-4">
                        <h6 class="font-bold text-gray-800 mb-3"><i class="bi bi-gift me-2 text-danger"></i>{{ __('Upcoming Birthdays') }}</h6>
                        <div class="birthday-scroll-container" style="max-height: 180px; overflow-y: auto; overflow-x: hidden;">
                            <ul class="hr-list px-2">
                                @forelse($upcomingBirthdays as $birthdayEmp)
                                <li class="small d-flex justify-content-between border-bottom-0 pb-1 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="emp-avatar-sm me-2" style="width: 30px; height: 30px; font-size: 0.75rem;">
                                            {{ strtoupper(substr($birthdayEmp->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-700">{{ $birthdayEmp->name }}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">{{ $birthdayEmp->next_birthday->format('d M') }}</div>
                                        </div>
                                    </div>
                                    @if($birthdayEmp->days_until_birthday === 0)
                                    <span class="badge bg-danger-soft text-danger align-self-center" style="font-size: 0.7rem;">{{ __('Today!') }}</span>
                                    @elseif($birthdayEmp->days_until_birthday === 1)
                                    <span class="badge bg-warning-soft text-warning align-self-center" style="font-size: 0.7rem;">{{ __('Tomorrow') }}</span>
                                    @else
                                    <span class="badge bg-light text-dark align-self-center" style="font-size: 0.7rem;">In {{ $birthdayEmp->days_until_birthday }} days</span>
                                    @endif
                                </li>
                                @empty
                                <li class="small text-center text-muted">{{ __('No upcoming birthdays.') }}</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <!-- Notices & Events -->
                    <div class="ui-panel mb-4" id="notices-events">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-bold text-gray-800 mb-0"><i class="bi bi-megaphone me-2 text-primary"></i>{{ __('Notices & Events') }}</h6>
                            <a href="{{ route('settings.notices.index') }}" class="btn btn-link btn-sm text-decoration-none small px-3 font-bold rounded-pill btn-pill-action">{{ __('Manage') }}</a>
                        </div>
                        <div class="notice-scroll-container" style="max-height: 250px; overflow-y: auto; overflow-x: hidden;">
                            <ul class="hr-list px-2">
                                @forelse($activeNotices as $notice)
                                <li class="small mb-3 border-bottom pb-2 last:border-bottom-0">
                                    <a href="#" class="text-decoration-none d-block notice-item"
                                       data-bs-toggle="modal"
                                       data-bs-target="#hrNoticeModal"
                                       data-title="{{ $notice->title }}"
                                       data-content="{{ $notice->content }}"
                                       data-type="{{ ucfirst($notice->type) }}"
                                       data-date="{{ $notice->created_at->format('d M Y, h:i A') }}">
                                        <div class="d-flex justify-content-between align-items-center mb-0">
                                            <span class="fw-bold text-gray-800 text-truncate" style="max-width: 180px;">{{ Str::limit($notice->title, 35) }}</span>
                                            <i class="bi bi-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                        </div>
                                        <p class="text-muted mb-1 text-truncate" style="font-size: 0.75rem; line-height: 1.4; max-width: 210px;">{{ Str::limit($notice->content, 70) }}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="text-muted" style="font-size: 0.65rem;">
                                                <i class="bi bi-clock me-1"></i>{{ $notice->created_at->diffForHumans() }}
                                            </div>
                                            @if($notice->type === 'event')
                                                <span class="badge bg-primary-soft text-primary" style="font-size: 0.6rem;">{{ __('Event') }}</span>
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

    <!-- HR Notice & Event Modal -->
    <div class="modal fade" id="hrNoticeModal" tabindex="-1" aria-labelledby="hrNoticeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-0">
                    <h5 class="modal-title fw-bold" id="hrNoticeModalLabel">{{ __('Notice & Event Detail') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-grow-1">
                            <h5 id="hrModalNoticeTitle" class="fw-bold mb-0 text-primary" style="overflow-wrap: anywhere;"></h5>
                            <div class="text-muted small mt-2">
                                <span id="hrModalNoticeType" class="badge bg-info-soft text-info"></span>
                                <span class="mx-1">•</span>
                                <span id="hrModalNoticeDate" class="text-muted"></span>
                            </div>
                        </div>
                    </div>
                    <hr class="my-3 opacity-10">
                    <div id="hrModalNoticeContent" class="text-gray-700 font-medium" style="line-height: 1.6; white-space: pre-wrap; overflow-wrap: anywhere;"></div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script id="office-attendance-data" type="application/json">
        {!! json_encode($officeAttendanceData) !!}
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Generic highlight for deep-linked sections
            const hash = window.location.hash;
            if (hash) {
                const target = document.querySelector(hash);
                if (target) {
                    target.classList.add('ui-highlight-section');
                    setTimeout(() => target.scrollIntoView({ behavior: 'smooth', block: 'center' }), 300);
                }
            }

            // HR Notice modal population
            const hrNoticeModal = document.getElementById('hrNoticeModal');
            if (hrNoticeModal) {
                hrNoticeModal.addEventListener('show.bs.modal', function (event) {
                    const btn = event.relatedTarget;
                    document.getElementById('hrModalNoticeTitle').textContent  = btn.getAttribute('data-title');
                    document.getElementById('hrModalNoticeContent').textContent = btn.getAttribute('data-content');
                    document.getElementById('hrModalNoticeDate').textContent    = btn.getAttribute('data-date');
                    document.getElementById('hrModalNoticeType').textContent    = btn.getAttribute('data-type');
                });
            }

            const rawData = document.getElementById('office-attendance-data').textContent;
            const officeData = JSON.parse(rawData);

            if (!document.getElementById('officeAttendanceChart')) return;

            const labels = officeData.map(data => {
                let name = data.name;
                if (name === 'Mir Telecom Ltd.') return 'Mir Telecom Ltd.';
                if (name === 'Bangla Telecom Ltd.') return 'Bangla Telecom Ltd.';
                if (name === 'Coloasia Ltd.') return 'Coloasia Ltd.';
                if (name.includes('BTS')) return 'BTS Communications';
                return name;
            });
            const presentData = officeData.map(data => data.present);
            const absentData = officeData.map(data => data.absent);
            const lateData = officeData.map(data => data.late);
            const leaveData = officeData.map(data => data.leave);

            const ctx = document.getElementById('officeAttendanceChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: '{{ __("Present") }}',
                            data: presentData,
                            backgroundColor: '#10b981', // Green
                            borderRadius: 4,
                        },
                        {
                            label: '{{ __("Absent") }}',
                            data: absentData,
                            backgroundColor: '#ef4444', // Red
                            borderRadius: 4,
                        },
                        {
                            label: '{{ __("Late") }}',
                            data: lateData,
                            backgroundColor: '#f59e0b', // Yellow
                            borderRadius: 4,
                        },
                        {
                            label: '{{ __("On Leave") }}',
                            data: leaveData,
                            backgroundColor: '#3b82f6', // Blue
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>



