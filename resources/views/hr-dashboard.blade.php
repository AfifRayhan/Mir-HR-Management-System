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
                            {{ $employee ? $employee->first_name.' '.$employee->last_name : ($user->name ?? __('HR Administrator')) }}
                            • {{ $roleName }}
                        </p>
                    </div>
                    <div class="text-end text-sm text-gray-500">
                        <i class="bi bi-calendar-event me-2"></i>{{ now()->format('l, d M Y') }}
                    </div>
                </div>
            </div>

            <!-- Top metric cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="hr-metric-card">
                        <div class="metric-icon bg-info-soft text-info">
                            <i class="bi bi-people-fill text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Total Employees') }}</div>
                            <div class="metric-value">{{ $totalEmployees }}</div>
                            <div class="metric-sub">{{ __('Including active & inactive') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="hr-metric-card">
                        <div class="metric-icon bg-success-soft text-success">
                            <i class="bi bi-person-check-fill text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Active Employees') }}</div>
                            <div class="metric-value">{{ $activeEmployees }}</div>
                            <div class="metric-sub">
                                @if($totalEmployees > 0)
                                {{ round(($activeEmployees / max($totalEmployees,1)) * 100) }}% {{ __('active') }}
                                @else
                                {{ __('No data yet') }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="hr-metric-card">
                        <div class="metric-icon bg-primary-soft text-primary">
                            <i class="bi bi-building text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Departments') }}</div>
                            <div class="metric-value">{{ $totalDepartments }}</div>
                            <div class="metric-sub">{{ __('Organization units') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="hr-metric-card">
                        <div class="metric-icon bg-warning-soft text-warning">
                            <i class="bi bi-diagram-2 text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Sections') }}</div>
                            <div class="metric-value">{{ $totalSections }}</div>
                            <div class="metric-sub">{{ __('Department subunits') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main panels -->
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="hr-panel">
                        <div class="hr-panel-title"><i class="bi bi-graph-up me-2"></i>{{ __('Attendance (Last 30 Days)') }}</div>
                        <div class="hr-panel-subtitle">
                            {{ __('Visual preview placeholder – integrate charts later.') }}
                        </div>
                        <div class="hr-chart-placeholder"></div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="hr-panel">
                        <div class="hr-panel-title"><i class="bi bi-pie-chart me-2"></i>{{ __('Attendance Status') }}</div>
                        <ul class="hr-list">
                            <li>{{ __('On leave: 0') }}</li>
                            <li>{{ __('Absent: 15') }}</li>
                            <li>{{ __('Late (Last 15 days): 9') }}</li>
                            <li>{{ __('New joins (This month): 4') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="hr-panel">
                        <div class="hr-panel-title"><i class="bi bi-bar-chart me-2"></i>{{ __('Overtime (Last 12 Months)') }}</div>
                        <div class="hr-chart-placeholder"></div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hr-panel">
                        <div class="hr-panel-title"><i class="bi bi-activity me-2"></i>{{ __('Recent HR Activity') }}</div>
                        <ul class="hr-list">
                            <li>{{ __('Salary adjustment processed for 3 employees.') }}</li>
                            <li>{{ __('2 leave requests pending approval.') }}</li>
                            <li>{{ __('New policy: Remote work guideline updated.') }}</li>
                            <li>{{ __('Next payroll run scheduled for Friday.') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>