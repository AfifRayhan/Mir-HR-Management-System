<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Employee Dashboard') }}
        </h2>
    </x-slot>

    <!-- Specific styles for this dashboard -->
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css', 'resources/css/custom-employee-dashboard.css'])
    @endpush

    @php $isTeamLead = optional(auth()->user()->role)->name === 'Team Lead'; @endphp

    <div class="{{ $isTeamLead ? 'hr-layout' : 'emp-layout' }}">
        @if($isTeamLead)
        @include('partials.team-lead-sidebar')
        @else
        @include('partials.employee-sidebar')
        @endif

        <main class="{{ $isTeamLead ? 'hr-main' : 'emp-main' }}">
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ __('Employee Dashboard') }}</h5>
                        <p class="mb-0 small text-muted">
                            {{ __('Welcome,') }}
                            {{ $employee ? $employee->first_name.' '.$employee->last_name : ($user->name ?? __('Employee')) }}
                            • {{ $roleName }}
                        </p>
                    </div>
                    <div class="text-end text-sm text-gray-500">
                        <i class="bi bi-calendar-event me-2"></i>{{ now()->format('l, d M Y') }}
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
                            <div class="metric-sub">{{ __('This month') }}</div>
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
                            <div class="metric-sub">{{ __('This month') }}</div>
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
                            <div class="metric-sub">{{ __('This month') }}</div>
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
                            <div class="metric-sub">{{ __('This year') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graphs Row -->
            <div class="row g-4 mb-4">
                <!-- Attendance Summary Graph -->
                <div class="col-lg-6">
                    <div class="hr-panel">
                        <h6 class="font-bold text-gray-800 mb-3"><i class="bi bi-pie-chart me-2 text-success"></i>{{ __('Attendance Summary') }}</h6>
                        <p class="small text-muted mb-3">{{ __('Current month breakdown') }}</p>
                        <div class="chart-container">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                        <div class="d-flex justify-content-center gap-4 mt-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="chart-legend-dot" style="background: #10B981;"></span>
                                <span class="small text-muted">{{ __('Present') }} ({{ $presentDays - $lateDays }})</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="chart-legend-dot" style="background: #F59E0B;"></span>
                                <span class="small text-muted">{{ __('Late') }} ({{ $lateDays }})</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="chart-legend-dot" style="background: #EF4444;"></span>
                                <span class="small text-muted">{{ __('Absent') }} ({{ $absentDays }})</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Previous Month Attendance Summary Graph -->
                <div class="col-lg-6">
                    <div class="hr-panel">
                        <h6 class="font-bold text-gray-800 mb-3"><i class="bi bi-pie-chart me-2 text-info"></i>{{ __('Attendance Summary') }}</h6>
                        <p class="small text-muted mb-3">{{ __('Previous month breakdown') }}</p>
                        <div class="chart-container">
                            <canvas id="prevAttendanceChart"></canvas>
                        </div>
                        <div class="d-flex justify-content-center gap-4 mt-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="chart-legend-dot" style="background: #10B981;"></span>
                                <span class="small text-muted">{{ __('Present') }} ({{ $prevPresentDays - $prevLateDays }})</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="chart-legend-dot" style="background: #F59E0B;"></span>
                                <span class="small text-muted">{{ __('Late') }} ({{ $prevLateDays }})</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="chart-legend-dot" style="background: #EF4444;"></span>
                                <span class="small text-muted">{{ __('Absent') }} ({{ $prevAbsentDays }})</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Recent Attendance + Holidays -->
            <div class="row g-4 mb-4">
                <!-- Recent Attendance Table -->
                <div class="col-lg-8">
                    <div class="hr-panel p-0 overflow-hidden">
                        <div class="p-4 border-bottom d-flex align-items-center">
                            <h6 class="mb-0 font-bold text-gray-800 flex-grow-1"><i class="bi bi-activity me-2 text-success"></i>{{ __('Recent Attendance') }}</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table hr-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">{{ __('Date') }}</th>
                                        <th>{{ __('In Time') }}</th>
                                        <th>{{ __('Out Time') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th class="pe-4 text-end">{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentAttendance as $record)
                                    <tr>
                                        <td class="ps-4 small">{{ $record->date->format('d M Y') }}</td>
                                        <td class="small">{{ $record->in_time ? \Carbon\Carbon::parse($record->in_time)->format('h:i A') : '--' }}</td>
                                        <td class="small">{{ $record->out_time ? \Carbon\Carbon::parse($record->out_time)->format('h:i A') : '--' }}</td>
                                        <td class="small">
                                            @if($record->status === 'absent')
                                                <span class="text-danger"><i class="bi bi-x-circle me-1"></i>{{ __('Absent') }}</span>
                                            @elseif($record->status === 'leave')
                                                <span class="text-info"><i class="bi bi-calendar2-range me-1"></i>{{ __('On Leave') }}</span>
                                            @elseif($record->late_seconds > 0)
                                                <span class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>{{ __('Late') }} ({{ $record->late_timing }})</span>
                                            @else
                                                <span class="text-success">{{ __('On Time') }}</span>
                                            @endif
                                        </td>
                                        <td class="pe-4 text-end">
                                            @if($record->status === 'absent')
                                                <span class="badge bg-danger-soft text-danger" style="font-size: 0.7rem;">{{ __('Absent') }}</span>
                                            @elseif($record->status === 'leave')
                                                <span class="badge bg-info-soft text-info" style="font-size: 0.7rem;">{{ __('Leave') }}</span>
                                            @elseif($record->status === 'late')
                                                <span class="badge bg-warning-soft text-warning" style="font-size: 0.7rem;">{{ __('Late') }}</span>
                                            @else
                                                <span class="badge bg-success-soft text-success" style="font-size: 0.7rem;">{{ __('Present') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted small">{{ __('No attendance records found.') }}</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Holidays -->
                <div class="col-lg-4">
                    <div class="hr-panel mb-4 shadow-sm">
                        <h6 class="font-bold text-gray-800 mb-3"><i class="bi bi-calendar-check me-2 text-info"></i>{{ __('Upcoming Holidays') }}</h6>
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

                    <!-- Upcoming Birthdays -->
                    <div class="hr-panel mb-4 shadow-sm">
                        <h6 class="font-bold text-gray-800 mb-3"><i class="bi bi-gift me-2 text-danger"></i>{{ __('Upcoming Birthdays') }}</h6>
                        <ul class="hr-list px-2">
                            @forelse($upcomingBirthdays as $birthdayEmp)
                            <li class="small d-flex justify-content-between border-bottom-0 pb-1 mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="emp-avatar-sm me-2" style="width: 30px; height: 30px; font-size: 0.75rem;">
                                        {{ strtoupper(substr($birthdayEmp->first_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-700">{{ $birthdayEmp->first_name }} {{ $birthdayEmp->last_name }}</div>
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

                    <!-- Notices & Events -->
                    <div class="hr-panel mb-4 shadow-sm">
                        <h6 class="font-bold text-gray-800 mb-3"><i class="bi bi-megaphone me-2 text-primary"></i>{{ __('Notices & Events') }}</h6>
                        <ul class="hr-list px-2">
                            @forelse($activeNotices as $notice)
                            <li class="small mb-3 border-bottom pb-2 last:border-bottom-0">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="fw-bold text-gray-800">{{ $notice->title }}</span>
                                    @if($notice->type === 'event')
                                        <span class="badge bg-primary-soft text-primary" style="font-size: 0.65rem;">{{ __('Event') }}</span>
                                    @else
                                        <span class="badge bg-info-soft text-info" style="font-size: 0.65rem;">{{ __('Notice') }}</span>
                                    @endif
                                </div>
                                <p class="text-muted mb-1" style="font-size: 0.75rem; line-height: 1.4;">{{ $notice->content }}</p>
                                <div class="text-muted" style="font-size: 0.65rem;">
                                    <i class="bi bi-clock me-1"></i>{{ $notice->created_at->diffForHumans() }}
                                </div>
                            </li>
                            @empty
                            <li class="small text-center text-muted py-2">{{ __('No active notices.') }}</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Chart.js CDN -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Attendance Summary Doughnut Chart
            const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
            const attendanceOnTime = Number("{{ $presentDays - $lateDays }}");
            const attendanceLate = Number("{{ $lateDays }}");
            const attendanceAbsent = Number("{{ $absentDays }}");
            const attendanceHasData = (attendanceOnTime + attendanceLate + attendanceAbsent) > 0;

            new Chart(attendanceCtx, {
                type: 'doughnut',
                data: {
                    labels: ['On Time', 'Late', 'Absent'],
                    datasets: [{
                        data: attendanceHasData ? [attendanceOnTime, attendanceLate, attendanceAbsent] : [1],
                        backgroundColor: attendanceHasData ? ['#10B981', '#F59E0B', '#EF4444'] : ['#E2E8F0'],
                        borderWidth: 0,
                        hoverOffset: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: attendanceHasData,
                            backgroundColor: '#1E293B',
                            titleFont: { size: 13, weight: '600' },
                            bodyFont: { size: 12 },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.raw + ' day(s)';
                                }
                            }
                        }
                    }
                }
            });
            // Previous Month Attendance Summary Doughnut Chart
            const prevAttendanceCtx = document.getElementById('prevAttendanceChart').getContext('2d');
            const prevAttendanceOnTime = Number("{{ $prevPresentDays - $prevLateDays }}");
            const prevAttendanceLate = Number("{{ $prevLateDays }}");
            const prevAttendanceAbsent = Number("{{ $prevAbsentDays }}");
            const prevAttendanceHasData = (prevAttendanceOnTime + prevAttendanceLate + prevAttendanceAbsent) > 0;
            
            new Chart(prevAttendanceCtx, {
                type: 'doughnut',
                data: {
                    labels: ['On Time', 'Late', 'Absent'],
                    datasets: [{
                        data: prevAttendanceHasData ? [prevAttendanceOnTime, prevAttendanceLate, prevAttendanceAbsent] : [1],
                        backgroundColor: prevAttendanceHasData ? ['#10B981', '#F59E0B', '#EF4444'] : ['#E2E8F0'],
                        borderWidth: 0,
                        hoverOffset: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: prevAttendanceHasData,
                            backgroundColor: '#1E293B',
                            titleFont: { size: 13, weight: '600' },
                            bodyFont: { size: 12 },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.raw + ' day(s)';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>