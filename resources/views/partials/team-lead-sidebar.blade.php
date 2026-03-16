{{-- Mobile Top Bar --}}
<div class="mobile-topbar" id="tlMobileTopbar">
    <button class="mobile-hamburger" id="tlSidebarToggle" aria-label="Toggle Sidebar">
        <i class="bi bi-list"></i>
    </button>
    <span class="topbar-brand"><span>Team</span>&nbsp;<span>Lead</span></span>
</div>

{{-- Sidebar Overlay --}}
<div class="sidebar-overlay" id="tlSidebarOverlay"></div>

        <aside class="hr-sidebar" id="tlSidebar">
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
                    <a href="{{ route('employee.attendance.index') }}" class="hr-sidebar-link {{ request()->routeIs('employee.attendance.*') ? 'active' : '' }}">
                        <i class="bi bi-clock"></i>
                        <span>{{ __('Attendances') }}</span>
                    </a>
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
                                class="hr-sidebar-link {{ request()->routeIs('team-lead.leave-applications.index') ? 'active' : '' }}">
                                <i class="bi bi-file-earmark-text"></i>
                                <span>{{ __('Applications') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('team-lead.leave-applications.history') }}"
                                class="hr-sidebar-link {{ request()->routeIs('team-lead.leave-applications.history') ? 'active' : '' }}">
                                <i class="bi bi-clock-history"></i>
                                <span>{{ __('History') }}</span>
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

@pushOnce('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('tlSidebarToggle');
    var overlay = document.getElementById('tlSidebarOverlay');
    var sidebar = document.getElementById('tlSidebar');
    if (!toggle || !overlay || !sidebar) return;

    function openSidebar() {
        sidebar.classList.add('sidebar-open');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar.classList.remove('sidebar-open');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    toggle.addEventListener('click', openSidebar);
    overlay.addEventListener('click', closeSidebar);
    sidebar.querySelectorAll('a.hr-sidebar-link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth < 992) closeSidebar();
        });
    });
});
</script>
@endPushOnce