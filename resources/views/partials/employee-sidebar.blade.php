{{-- Mobile Top Bar --}}
<div class="ui-ui-mobile-topbar" id="empMobileTopbar">
    <button class="ui-ui-mobile-hamburger" id="empSidebarToggle" aria-label="Toggle Sidebar">
        <i class="bi bi-list"></i>
    </button>
    <a href="{{ route('employee-dashboard') }}" class="ui-ui-topbar-brand" style="text-decoration: none;"><span>Employee</span>&nbsp;<span>Portal</span></a>

    {{-- Notification Mobile Bell --}}
    @auth
    <a href="{{ route('notifications.index') }}" class="ms-auto position-relative text-dark me-3" style="font-size: 1.15rem; display: flex; align-items: center; text-decoration: none;">
        <i class="bi bi-bell-fill"></i>
        @if(($unreadNotificationCount ?? 0) > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.55rem; padding: 0.25em 0.4em;">
                {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
            </span>
        @endif
    </a>
    @endauth
</div>

{{-- Sidebar Overlay --}}
<div class="ui-overlay" id="empSidebarOverlay"></div>

<aside class="ui-sidebar" id="empSidebar">
    <a href="{{ route('employee-dashboard') }}" class="ui-logo" style="text-decoration: none;">
        <span>Employee</span>
        <span>Portal</span>
    </a>

    <ul class="ui-sidebar-nav">
        @foreach($sidebarItems as $item)
            @if($item->slug === 'employee-dashboard' && $item->filtered_children->isNotEmpty())
                @foreach($item->filtered_children as $child)
                    <li>
                        <a href="{{ $child->route_name ? route($child->route_name) : '#' }}" 
                           class="ui-sidebar-link {{ $child->is_active ? 'active' : '' }}">
                            <i class="bi {{ $child->icon }}"></i>
                            <span>{{ __($child->name) }}</span>
                        </a>
                    </li>
                @endforeach
            @else
                <li>
                    <a href="{{ $item->route_name ? route($item->route_name) : '#' }}" 
                       class="ui-sidebar-link {{ $item->is_active ? 'active' : '' }}">
                        <i class="bi {{ $item->icon }}"></i>
                        <span>{{ __($item->name) }}</span>
                    </a>
                </li>
            @endif
        @endforeach

        {{-- Notifications Link --}}
        <li>
            <a href="{{ route('notifications.index') }}" 
               class="ui-sidebar-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                <i class="bi bi-bell"></i>
                <span class="d-flex w-100 align-items-center justify-content-between">
                    {{ __('Notifications') }}
                    @if(($unreadNotificationCount ?? 0) > 0)
                        <span class="badge bg-danger rounded-pill">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                    @endif
                </span>
            </a>
        </li>
    </ul>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="ui-sidebar-link w-100 border-0 bg-transparent text-start">
            <i class="bi bi-box-arrow-right"></i>
            <span>{{ __('Log Out') }}</span>
        </button>
    </form>
</aside>

@pushOnce('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('empSidebarToggle');
    var overlay = document.getElementById('empSidebarOverlay');
    var sidebar = document.getElementById('empSidebar');
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
    sidebar.querySelectorAll('a.ui-sidebar-link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth < 992) closeSidebar();
        });
    });
});
</script>
@endPushOnce



