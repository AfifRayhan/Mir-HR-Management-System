{{-- Mobile Top Bar --}}
<div class="emp-mobile-topbar" id="empMobileTopbar">
    <button class="mobile-hamburger" id="empSidebarToggle" aria-label="Toggle Sidebar">
        <i class="bi bi-list"></i>
    </button>
    <span class="topbar-brand"><span>Employee</span>&nbsp;<span>Portal</span></span>
</div>

{{-- Sidebar Overlay --}}
<div class="emp-sidebar-overlay" id="empSidebarOverlay"></div>

        <aside class="emp-sidebar" id="empSidebar">
            <div class="emp-logo">
                <span>Employee</span>
                <span>Portal</span>
            </div>

            <ul class="emp-sidebar-nav">
                @foreach($sidebarItems as $item)
                    @if($item->slug === 'employee-dashboard' && $item->filtered_children->isNotEmpty())
                        @foreach($item->filtered_children as $child)
                            <li>
                                <a href="{{ $child->route_name ? route($child->route_name) : '#' }}" 
                                   class="emp-sidebar-link {{ $child->is_active ? 'active' : '' }}">
                                    <i class="bi {{ $child->icon }}"></i>
                                    <span>{{ __($child->name) }}</span>
                                </a>
                            </li>
                        @endforeach
                    @else
                        <li>
                            <a href="{{ $item->route_name ? route($item->route_name) : '#' }}" 
                               class="emp-sidebar-link {{ $item->is_active ? 'active' : '' }}">
                                <i class="bi {{ $item->icon }}"></i>
                                <span>{{ __($item->name) }}</span>
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>

            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button type="submit" class="emp-sidebar-link w-100 border-0 bg-transparent text-start">
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
    sidebar.querySelectorAll('a.emp-sidebar-link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth < 992) closeSidebar();
        });
    });
});
</script>
@endPushOnce