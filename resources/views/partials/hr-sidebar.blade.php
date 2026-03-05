@php
$currentUser = auth()->user();
$currentRoute = request()->route()->getName() ?? '';
@endphp

<aside class="hr-sidebar">
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