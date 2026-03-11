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
                    <a href="{{ route('employee.attendance.index') }}" class="emp-sidebar-link {{ request()->routeIs('employee.attendance.*') ? 'active' : '' }}">
                        <i class="bi bi-clock"></i>
                        <span>{{ __('Attendances') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('employee.leave.index') }}" class="emp-sidebar-link {{ request()->routeIs('employee.leave.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar2-minus"></i>
                        <span>{{ __('Leave Requests') }}</span>
                    </a>
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