<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Employee Dashboard') }}
        </h2>
    </x-slot>

    <!-- Specific styles for this dashboard -->
    @push('styles')
    @vite(['resources/css/custom-employee-dashboard.css'])
    @endpush

    <div class="emp-layout">
        <aside class="emp-sidebar">
            <div class="emp-logo">
                <span>Employee</span>
                <span>Portal</span>
            </div>

            <ul class="emp-sidebar-nav">
                <li>
                    <a href="{{ route('employee-dashboard') }}" class="emp-sidebar-link {{ request()->routeIs('employee-dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>{{ __('Dashboard') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('employee-profile') }}" class="emp-sidebar-link {{ request()->routeIs('employee-profile') ? 'active' : '' }}">
                        <i class="bi bi-person-vcard"></i>
                        <span>{{ __('My Profile') }}</span>
                    </a>
                </li>
                <li>
                    <span class="emp-sidebar-link">
                        <i class="bi bi-clock"></i>
                        <span>{{ __('Attendances') }}</span>
                    </span>
                </li>
                <li>
                    <span class="emp-sidebar-link">
                        <i class="bi bi-calendar2-minus"></i>
                        <span>{{ __('Leave Requests') }}</span>
                    </span>
                </li>
                <li>
                    <span class="emp-sidebar-link">
                        <i class="bi bi-envelope-paper"></i>
                        <span>{{ __('Payslips') }}</span>
                    </span>
                </li>
                <li>
                    <span class="emp-sidebar-link">
                        <i class="bi bi-bell"></i>
                        <span>{{ __('Notifications') }}</span>
                    </span>
                </li>
            </ul>

            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button type="submit" class="emp-sidebar-link w-100 border-0 bg-transparent text-start">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>{{ __('Log Out') }}</span>
                </button>
            </form>
        </aside>

        <main class="emp-main">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 text-2xl font-bold">{{ __('Employee Dashboard') }}</h5>
                        <p class="mb-0 text-gray-500">
                            {{ __('Welcome back,') }}
                            {{ $employee ? $employee->first_name . ' ' . $employee->last_name : $user->name }}
                        </p>
                    </div>
                    <div class="text-end text-sm text-gray-500">
                        <i class="bi bi-calendar-event me-2"></i>{{ now()->format('l, d M Y') }}
                    </div>
                </div>
            </div>

            <!-- Secondary Metrics -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="emp-metric-card">
                        <div class="metric-icon bg-info-soft text-info">
                            <i class="bi bi-calendar-check text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Present Days') }}</div>
                            <div class="metric-value">22</div>
                            <div class="metric-sub">{{ __('This month') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="emp-metric-card">
                        <div class="metric-icon bg-warning-soft text-warning">
                            <i class="bi bi-cup-hot text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Leave Balance') }}</div>
                            <div class="metric-value">12</div>
                            <div class="metric-sub">{{ __('Annual leaves remaining') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="emp-metric-card">
                        <div class="metric-icon bg-success-soft text-success">
                            <i class="bi bi-cash-stack text-2xl"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">{{ __('Next Payroll') }}</div>
                            <div class="metric-value">{{ now()->endOfMonth()->format('d M') }}</div>
                            <div class="metric-sub">{{ __('Status: Processing') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="emp-panel">
                        <div class="emp-panel-title">
                            <i class="bi bi-graph-up me-2"></i>{{ __('Recent Attendances') }}
                        </div>
                        <div class="table-responsive">
                            <table class="table emp-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Check In') }}</th>
                                        <th>{{ __('Check Out') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ now()->format('d M Y') }}</td>
                                        <td>09:02 AM</td>
                                        <td>--:-- PM</td>
                                        <td><span class="badge bg-success-soft text-success">{{ __('Present') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td>{{ now()->subDays(1)->format('d M Y') }}</td>
                                        <td>08:55 AM</td>
                                        <td>06:10 PM</td>
                                        <td><span class="badge bg-success-soft text-success">{{ __('Present') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td>{{ now()->subDays(2)->format('d M Y') }}</td>
                                        <td>09:15 AM</td>
                                        <td>06:05 PM</td>
                                        <td><span class="badge bg-warning-soft text-warning">{{ __('Late') }}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="emp-panel">
                        <div class="emp-panel-title">
                            <i class="bi bi-megaphone me-2"></i>{{ __('Company Announcements') }}
                        </div>
                        <ul class="emp-announcement-list">
                            <li>
                                <div class="date">{{ now()->subDays(2)->format('d M') }}</div>
                                <div class="content">
                                    <h6>{{ __('Townhall Meeting') }}</h6>
                                    <p>{{ __('Monthly townhall meeting scheduled for coming Friday.') }}</p>
                                </div>
                            </li>
                            <li>
                                <div class="date">{{ now()->subDays(5)->format('d M') }}</div>
                                <div class="content">
                                    <h6>{{ __('Public Holiday') }}</h6>
                                    <p>{{ __('Office will remain closed on Monday for National Holiday.') }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>