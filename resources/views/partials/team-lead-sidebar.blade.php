        <aside class="hr-sidebar">
            <div class="hr-logo">
                <span>Team</span>
                <span>Lead</span>
            </div>

            <ul class="hr-sidebar-nav">
                <li>
                    <a href="{{ route('employee-dashboard') }}" class="hr-sidebar-link {{ request()->routeIs('employee-dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>{{ __('Dashboard') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('employee-profile') }}" class="hr-sidebar-link {{ request()->routeIs('employee-profile') ? 'active' : '' }}">
                        <i class="bi bi-person-vcard"></i>
                        <span>{{ __('My Profile') }}</span>
                    </a>
                </li>
                <li>
                    <span class="hr-sidebar-link">
                        <i class="bi bi-clock"></i>
                        <span>{{ __('Attendances') }}</span>
                    </span>
                </li>

                {{-- Leave dropdown --}}
                @php $leaveActive = request()->routeIs('team-lead.leave.*') || request()->routeIs('team-lead.leave-applications.*'); @endphp
                <li class="hr-sidebar-parent {{ $leaveActive ? 'open' : '' }}">
                    <span class="hr-sidebar-link hr-sidebar-toggle" onclick="this.closest('.hr-sidebar-parent').classList.toggle('open')">
                        <i class="bi bi-calendar2-minus"></i>
                        <span>{{ __('Leave') }}</span>
                        <i class="bi bi-chevron-down hr-chevron ms-auto"></i>
                    </span>
                    <ul class="hr-sidebar-submenu">
                        <li>
                            <a href="{{ route('team-lead.leave.index') }}"
                                class="hr-sidebar-link {{ request()->routeIs('team-lead.leave.index') ? 'active' : '' }}">
                                <i class="bi bi-journal-plus"></i>
                                <span>{{ __('Requests') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('team-lead.leave-applications.index') }}"
                                class="hr-sidebar-link {{ request()->routeIs('team-lead.leave-applications.*') ? 'active' : '' }}">
                                <i class="bi bi-file-earmark-text"></i>
                                <span>{{ __('Applications') }}</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li>
                    <span class="hr-sidebar-link">
                        <i class="bi bi-envelope-paper"></i>
                        <span>{{ __('Payslips') }}</span>
                    </span>
                </li>
                <li>
                    <span class="hr-sidebar-link">
                        <i class="bi bi-bell"></i>
                        <span>{{ __('Notifications') }}</span>
                    </span>
                </li>
            </ul>

            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button type="submit" class="hr-sidebar-link w-100 border-0 bg-transparent text-start">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>{{ __('Log Out') }}</span>
                </button>
            </form>
        </aside>