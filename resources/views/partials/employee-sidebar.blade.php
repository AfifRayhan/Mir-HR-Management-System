        <aside class="emp-sidebar">
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