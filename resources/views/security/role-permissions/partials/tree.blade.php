<div class="rp-tree-card">
    @php
        $redundantSlugs = [
            'employee-leave-req',
            'team-lead-leave-req',
            'team-lead-leave-apps',
            'team-lead-leave-history'
        ];

        $visibleMenuItems = $menuItems->filter(fn($item) => !in_array($item->slug, $redundantSlugs));
    @endphp

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="ui-panel-title m-0 border-0 p-0">
                <i class="bi bi-diagram-3 me-2 text-success"></i>{{ __('Navigation Access Tree') }}
            </div>
            <p class="rp-tree-subtitle mb-0">
                @if($manageBy === 'user' && !$selectedUser)
                    {{ __('Select an employee to load individual permission access.') }}
                @elseif($manageBy === 'role' && !$selectedRole)
                    {{ __('Select a role to load role-based navigation access.') }}
                @else
                    {{ __('Enable full modules or open a module to fine-tune individual links.') }}
                @endif
            </p>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="rp-stat-pill">
                <i class="bi bi-grid me-2"></i>{{ $visibleMenuItems->count() }} {{ __('Modules') }}
            </span>
            <span class="rp-stat-pill">
                <i class="bi bi-check2-circle me-2"></i><span id="selectedPermissionCount">0</span> {{ __('Selected') }}
            </span>
            <div class="form-check form-switch rp-switch rp-select-all mb-0">
                <input class="form-check-input" type="checkbox" role="switch" id="checkAll">
                <label class="form-check-label small fw-bold text-dark" for="checkAll">{{ __('Select All') }}</label>
            </div>
            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" id="expandAllModules">
                <i class="bi bi-arrows-expand me-1"></i>{{ __('Expand All') }}
            </button>
        </div>
    </div>

    @if(($manageBy === 'role' && !$selectedRole) || ($manageBy === 'user' && !$selectedUser))
        <div class="rp-empty-state">
            <div class="rp-empty-icon">
                <i class="bi {{ $manageBy === 'user' ? 'bi-person-check' : 'bi-shield-lock' }}"></i>
            </div>
            <h6 class="rp-empty-title mb-2">
                {{ $manageBy === 'user' ? __('Select an employee first') : __('Select a role first') }}
            </h6>
            <p class="rp-empty-copy mb-0">
                {{ $manageBy === 'user'
                    ? __('Choose an employee from the Selection Context panel to load personal navigation permissions.')
                    : __('Choose a role from the Selection Context panel to load navigation permissions for that role.') }}
            </p>
        </div>
    @else
        <div class="rp-module-list">
            @foreach($visibleMenuItems as $item)
                @php
                    $visibleChildren = $item->children->filter(fn($c) => !in_array($c->slug, $redundantSlugs));
                @endphp

                <section class="rp-module-card" data-parent-card="{{ $item->id }}">
                    <div class="rp-module-head">
                        <div class="rp-module-main">
                            <div class="rp-module-icon">
                                <i class="{{ $item->icon }}"></i>
                            </div>
                            <div class="rp-module-copy">
                                <div class="rp-module-title-row">
                                    <h6 class="rp-module-title mb-0">{{ __($item->name) }}</h6>
                                    <span class="rp-module-badge">
                                        {{ $visibleChildren->isNotEmpty() ? trans_choice(':count link|:count links', $visibleChildren->count(), ['count' => $visibleChildren->count()]) : __('Single Link') }}
                                    </span>
                                </div>
                                <p class="rp-module-meta mb-0">
                                    @if($visibleChildren->isNotEmpty())
                                        <span class="rp-module-state" data-module-state="{{ $item->id }}">{{ __('0 selected') }}</span>
                                    @else
                                        {{ __('Standalone navigation item') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="rp-module-actions">
                            <div class="form-check form-switch rp-switch mb-0">
                                <input class="form-check-input menu-check parent-check"
                                    type="checkbox"
                                    role="switch"
                                    name="menu_items[]"
                                    value="{{ $item->id }}"
                                    id="menu_{{ $item->id }}"
                                    data-slug="{{ $item->slug }}"
                                    {{ in_array($item->slug, $assignedSlugs) ? 'checked' : '' }}>
                                <label class="form-check-label rp-tree-label fw-bold text-gray-800" for="menu_{{ $item->id }}">{{ __('Module') }}</label>
                            </div>
                            @if($visibleChildren->isNotEmpty())
                                <button type="button"
                                    class="rp-tree-toggle"
                                    data-target="sub-{{ $item->slug }}"
                                    aria-label="{{ __('Toggle :item children', ['item' => $item->name]) }}"
                                    aria-expanded="false"
                                    onclick="toggleSub(this)">
                                    <span>{{ __('Details') }}</span>
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    @if($visibleChildren->isNotEmpty())
                        <div class="rp-subtree" id="sub-{{ $item->slug }}">
                            <div class="rp-child-grid">
                                @foreach($visibleChildren as $child)
                                    <label class="rp-child-row" for="menu_{{ $child->id }}">
                                        <div class="rp-child-copy">
                                            <span class="rp-child-icon">
                                                <i class="{{ $child->icon }}"></i>
                                            </span>
                                            <span class="rp-child-name">{{ __($child->name) }}</span>
                                        </div>
                                        <div class="form-check form-switch rp-switch mb-0">
                                            <input class="form-check-input menu-check child-check"
                                                type="checkbox"
                                                role="switch"
                                                name="menu_items[]"
                                                value="{{ $child->id }}"
                                                id="menu_{{ $child->id }}"
                                                data-parent="{{ $item->id }}"
                                                data-slug="{{ $child->slug }}"
                                                {{ in_array($child->slug, $assignedSlugs) ? 'checked' : '' }}>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>
            @endforeach
        </div>
    @endif
</div>
