@php
$currentUser = auth()->user();
$currentRoute = request()->route()->getName() ?? '';

// Load menu items from DB, grouped by top-level with children
$sidebarItems = \App\Models\MenuItem::whereNull('parent_id')
->with('children')
->orderBy('sort_order')
->get();
@endphp

<aside class="hr-sidebar">
    <div class="hr-logo">
        <span>HRM</span>
        <span>System</span>
    </div>

    <ul class="hr-sidebar-nav">
        @foreach($sidebarItems as $item)
        @php
        // Check if user has access to this parent menu item or any of its children
        $hasParentAccess = $currentUser && $currentUser->hasMenuAccess($item->slug);
        $hasChildAccess = false;
        if ($currentUser && $item->children->isNotEmpty()) {
        foreach ($item->children as $child) {
        if ($currentUser->hasMenuAccess($child->slug)) {
        $hasChildAccess = true;
        break;
        }
        }
        }
        $showItem = $hasParentAccess || $hasChildAccess;
        @endphp

        @if($showItem)
        @if($item->children->isNotEmpty())
        {{-- Parent with submenu --}}
        <li class="hr-sidebar-parent {{ $item->route_name && str_starts_with($currentRoute, explode('.', $item->route_name)[0] ?? '') ? 'open' : (collect($item->children)->pluck('route_name')->filter()->contains(fn($r) => str_starts_with($currentRoute, explode('.', $r)[0] . '.')) ? 'open' : '') }}">
            <span class="hr-sidebar-link hr-sidebar-toggle" onclick="this.closest('.hr-sidebar-parent').classList.toggle('open')">
                <i class="{{ $item->icon }}"></i>
                <span>{{ __($item->name) }}</span>
                <i class="bi bi-chevron-down hr-chevron ms-auto"></i>
            </span>
            <ul class="hr-sidebar-submenu">
                @foreach($item->children as $child)
                @if($currentUser && $currentUser->hasMenuAccess($child->slug))
                <li>
                    <a href="{{ $child->route_name ? route($child->route_name) : '#' }}"
                        class="hr-sidebar-link {{ $child->route_name && str_starts_with($currentRoute, str_replace('.index', '', $child->route_name)) ? 'active' : '' }}">
                        <i class="{{ $child->icon }}"></i>
                        <span>{{ __($child->name) }}</span>
                    </a>
                </li>
                @endif
                @endforeach
            </ul>
        </li>
        @else
        {{-- Simple link --}}
        <li>
            <a href="{{ $item->route_name ? route($item->route_name) : '#' }}"
                class="hr-sidebar-link {{ $item->route_name && $currentRoute === $item->route_name ? 'active' : '' }}">
                <i class="{{ $item->icon }}"></i>
                <span>{{ __($item->name) }}</span>
            </a>
        </li>
        @endif
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