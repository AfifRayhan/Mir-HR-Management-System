@php
$currentUser = auth()->user();
$currentRoute = request()->route()->getName() ?? '';
@endphp

{{-- Mobile Top Bar --}}
<div class="mobile-topbar" id="hrMobileTopbar">
    <button class="mobile-hamburger" id="hrSidebarToggle" aria-label="Toggle Sidebar">
        <i class="bi bi-list"></i>
    </button>
    <span class="topbar-brand"><span>HRM</span>&nbsp;<span>System</span></span>
</div>

{{-- Sidebar Overlay --}}
<div class="sidebar-overlay" id="hrSidebarOverlay"></div>

<aside class="hr-sidebar" id="hrSidebar">
    <div class="hr-logo">
        <span>HRM</span>
        <span>System</span>
    </div>

    <ul class="hr-sidebar-nav">
        @foreach($sidebarItems as $item)
        @if($item->filtered_children->isNotEmpty())
        {{-- Parent with submenu --}}
        <li class="hr-sidebar-parent {{ $item->is_open ? 'open' : '' }}">
            <span class="hr-sidebar-link hr-sidebar-toggle" onclick="this.closest('.hr-sidebar-parent').classList.toggle('open')">
                <i class="{{ $item->icon }}"></i>
                <span>{{ __($item->name) }}</span>
                <i class="bi bi-chevron-down hr-chevron ms-auto"></i>
            </span>
            <ul class="hr-sidebar-submenu">
                @foreach($item->filtered_children as $child)
                <li>
                    <a href="{{ $child->route_name ? route($child->route_name) : '#' }}"
                        class="hr-sidebar-link {{ $child->is_active ? 'active' : '' }}">
                        <i class="{{ $child->icon }}"></i>
                        <span>{{ __($child->name) }}</span>
                    </a>
                </li>
                @endforeach
            </ul>
        </li>
        @else
        {{-- Simple link --}}
        <li>
            <a href="{{ $item->route_name ? route($item->route_name) : '#' }}"
                class="hr-sidebar-link {{ $item->is_active ? 'active' : '' }}">
                <i class="{{ $item->icon }}"></i>
                <span>{{ __($item->name) }}</span>
            </a>
        </li>
        @endif
        @endforeach
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
    var toggle = document.getElementById('hrSidebarToggle');
    var overlay = document.getElementById('hrSidebarOverlay');
    var sidebar = document.getElementById('hrSidebar');
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