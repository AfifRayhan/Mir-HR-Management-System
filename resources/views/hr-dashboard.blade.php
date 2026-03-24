<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <!-- Specific styles for this dashboard -->
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
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
                        <i class="bi bi-calendar-event me-2"></i>{{ now()->format('l, d M Y') }}
                    </div>
                </div>
            </div>

            <!-- Status Today Metrics -->
            <div class="row g-4 mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="hr-metric-card">
                        <div class="metric-icon bg-success-soft text-success">
                            <i class="bi bi-person-check-fill text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Present Today') }}</div>
                            <div class="metric-value">{{ $presentToday }}</div>
                            <div class="metric-sub">{{ __('Clocked-in employees') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="hr-metric-card">
                        <div class="metric-icon bg-danger-soft text-danger">
                            <i class="bi bi-person-x-fill text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Absent Today') }}</div>
                            <div class="metric-value">{{ $absentToday }}</div>
                            <div class="metric-sub">{{ __('Not yet in office') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="hr-metric-card">
                        <div class="metric-icon bg-warning-soft text-warning">
                            <i class="bi bi-clock-history text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Late Today') }}</div>
                            <div class="metric-value">{{ $lateToday }}</div>
                            <div class="metric-sub">{{ __('After late threshold') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="hr-metric-card">
                        <div class="metric-icon bg-info-soft text-info">
                            <i class="bi bi-calendar2-range text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('On Leave Today') }}</div>
                            <div class="metric-value">{{ $onLeaveToday }}</div>
                            <div class="metric-sub">{{ __('Approved leaves') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- General HR Metrics -->
            <div class="row g-4 mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="hr-metric-card">
                        <div class="metric-icon bg-primary-soft text-primary">
                            <i class="bi bi-people-fill text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Total Employee') }}</div>
                            <div class="metric-value">{{ $totalEmployees }}</div>
                            <div class="metric-sub">{{ __('Registered staff') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="hr-metric-card">
                        <div class="metric-icon bg-info-soft text-info">
                            <i class="bi bi-building-fill text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Total Dept.') }}</div>
                            <div class="metric-value">{{ $totalDepartments }}</div>
                            <div class="metric-sub">{{ __('Active Dept.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="hr-metric-card">
                        <div class="metric-icon bg-success-soft text-success">
                            <i class="bi bi-award-fill text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Total Grade') }}</div>
                            <div class="metric-value">{{ $totalGrades }}</div>
                            <div class="metric-sub">{{ __('Pay grade levels') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="hr-metric-card">
                        <div class="metric-icon bg-warning-soft text-warning">
                            <i class="bi bi-geo-alt-fill text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Total Office') }}</div>
                            <div class="metric-value">{{ $totalOffices }}</div>
                            <div class="metric-sub">{{ __('Work locations') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Panels -->
            <div class="row g-4 mb-4">
                <!-- Recent Attendance Summary -->
                <div class="col-lg-8">
                    <div class="hr-panel p-0 overflow-hidden">
                        <div class="p-4 border-bottom d-flex align-items-center">
                            <h6 class="mb-0 font-bold text-gray-800 flex-grow-1"><i class="bi bi-activity me-2 text-success"></i>{{ __('Recent Attendance Summary') }}</h6>
                            <a href="{{ route('personnel.attendances.index') }}" class="btn btn-success btn-sm text-white px-3 font-bold rounded-pill btn-pill-action flex-shrink-0">{{ __('View All') }}</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table hr-table mb-0">
                                <thead>
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
                                        <td class="small">{{ $record->in_time ? \Carbon\Carbon::parse($record->in_time)->format('h:i A') : '-' }}</td>
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
                    <div class="hr-panel mb-4">
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
                    <div class="hr-panel mb-4">
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
                    <div class="hr-panel mb-4">
                        <h6 class="font-bold text-gray-800 mb-3"><i class="bi bi-gift me-2 text-danger"></i>{{ __('Upcoming Birthdays') }}</h6>
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

                    <!-- Notices & Events -->
                    <div class="hr-panel mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-bold text-gray-800 mb-0"><i class="bi bi-megaphone me-2 text-primary"></i>{{ __('Notices & Events') }}</h6>
                            <a href="{{ route('settings.notices.index') }}" class="btn btn-link btn-sm p-0 text-decoration-none small">{{ __('Manage') }}</a>
                        </div>
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
                                <p class="text-muted mb-1" style="font-size: 0.75rem; line-height: 1.4;">{{ Str::limit($notice->content, 80) }}</p>
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
</x-app-layout>