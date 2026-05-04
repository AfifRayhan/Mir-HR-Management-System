<x-app-layout>
    @push('styles')
    @vite(['resources/css/role-permissions.css'])
    <style>
        /* UI refinement to match other pages */
        .rp-tree-toggle {
            transition: all 0.2s ease;
            background: var(--ui-bg);
            border: 1px solid var(--ui-border);
            color: var(--ui-primary);
        }
        .rp-tree-toggle:hover {
            background: var(--ui-primary-soft);
            border-color: var(--ui-primary);
        }
        .rp-tree-item {
            padding: 8px 12px;
            border-radius: var(--ui-radius-sm);
            transition: background 0.15s;
        }
        .rp-tree-item:hover {
            background: var(--ui-bg);
        }
        .rp-subtree {
            border-left: 2px dashed var(--ui-border);
            margin-left: 10px !important;
            padding-left: 24px !important;
        }
        .btn-check:checked + .btn-outline-primary {
            background-color: var(--ui-primary);
            border-color: var(--ui-primary);
            color: #fff;
        }
        .rp-header-card {
            background: #fff;
            border: 1px solid var(--ui-border);
            border-radius: var(--ui-radius-md);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--ui-shadow);
        }
        .rp-tree-card {
            background: #fff;
            border: 1px solid var(--ui-border);
            border-radius: var(--ui-radius-md);
            padding: 1.5rem;
            box-shadow: var(--ui-shadow);
        }
    </style>
    @endpush

    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            <!-- Page Header -->
            <div class="row mb-4 align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1 text-2xl font-bold">{{ __('Security & Permissions') }}</h5>
                    <p class="mb-0 text-gray-500 text-sm">
                        <i class="bi bi-shield-lock me-1"></i>
                        {{ __('Manage access levels for roles and individual employees') }}
                    </p>
                </div>
                <div class="col-md-4 text-md-end text-sm text-gray-500">
                    <i class="bi bi-calendar-event me-2 text-success"></i>{{ now()->format('l, d M Y') }}
                </div>
            </div>

            <form method="POST" action="{{ route('security.role-permissions.update') }}" id="rpForm">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    {{-- Left Side: Selectors --}}
                    <div class="col-lg-4">
                        <div class="rp-header-card h-100">
                            <div class="ui-panel-title mb-4">
                                <i class="bi bi-person-gear me-2 text-success"></i>{{ __('Selection Context') }}
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted mb-2">{{ __('Manage Permissions By') }}</label>
                                <div class="btn-group w-100 p-1 bg-light rounded-pill" role="group">
                                    <input type="radio" class="btn-check" name="manage_by" id="manage_by_role" value="role" {{ $selectedRole ? 'checked' : '' }}>
                                    <label class="btn btn-sm rounded-pill px-4 border-0" for="manage_by_role">{{ __('Role') }}</label>

                                    <input type="radio" class="btn-check" name="manage_by" id="manage_by_user" value="user" {{ $selectedUser ? 'checked' : '' }}>
                                    <label class="btn btn-sm rounded-pill px-4 border-0" for="manage_by_user">{{ __('Employee') }}</label>
                                </div>
                            </div>

                            <div id="role_selector_group" class="{{ $selectedUser ? 'd-none' : '' }}">
                                <div class="mb-3">
                                    <label for="role_id" class="form-label small fw-bold text-muted">{{ __('System Role') }}</label>
                                    <select name="role_id" id="role_id" class="form-select rounded-3 border-light shadow-sm" data-index-url="{{ route('security.role-permissions.index') }}">
                                        @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ $selectedRole && $selectedRole->id === $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text text-xs mt-2 text-muted">
                                        <i class="bi bi-info-circle me-1"></i>{{ __('Updating a role affects all users assigned to it.') }}
                                    </div>
                                </div>
                            </div>

                            <div id="user_selector_group" class="{{ $selectedRole ? 'd-none' : '' }}">
                                <div class="mb-3">
                                    <label for="user_id" class="form-label small fw-bold text-muted">{{ __('Target Employee') }}</label>
                                    <select name="user_id" id="user_id" class="form-select rounded-3 border-light shadow-sm" data-index-url="{{ route('security.role-permissions.index') }}">
                                        <option value="">{{ __('--- Select Employee ---') }}</option>
                                        @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ $selectedUser && $selectedUser->id === $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->role->name ?? 'No Role' }})
                                        </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text text-xs mt-2 text-muted text-success">
                                        <i class="bi bi-lightning-charge me-1"></i>{{ __('Individual permissions override role defaults.') }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto pt-4 border-top">
                                <button type="submit" class="btn btn-success w-100 py-2 rounded-pill shadow-sm d-flex align-items-center justify-content-center">
                                    <i class="bi bi-check-circle-fill me-2"></i> {{ __('Save Permissions') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Right Side: Tree --}}
                    <div class="col-lg-8">
                        <div class="rp-tree-card">
                            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                                <div class="ui-panel-title m-0 border-0 p-0">
                                    <i class="bi bi-list-check me-2 text-success"></i>{{ __('Navigation Access Tree') }}
                                </div>
                                <div class="form-check mb-0 bg-light px-3 py-1 rounded-pill">
                                    <input class="form-check-input" type="checkbox" id="checkAll">
                                    <label class="form-check-label small fw-bold text-dark" for="checkAll">{{ __('Select All') }}</label>
                                </div>
                            </div>

                            <ul class="rp-tree">
                                @php
                                    // List of redundant or system-only slugs to hide from the UI tree
                                    $redundantSlugs = [
                                        'employee-leave-req',
                                        'team-lead-leave-req',
                                        'team-lead-leave-apps',
                                        'team-lead-leave-history'
                                    ];
                                @endphp
                                @foreach($menuItems as $item)
                                @if(!in_array($item->slug, $redundantSlugs))
                                <li class="mb-2 border-0">
                                    <div class="rp-tree-item d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center flex-grow-1">
                                            @php
                                                $visibleChildren = $item->children->filter(fn($c) => !in_array($c->slug, $redundantSlugs));
                                            @endphp
                                            <span class="rp-tree-toggle me-3 {{ $visibleChildren->isEmpty() ? 'no-children' : '' }}"
                                                data-target="sub-{{ $item->slug }}"
                                                onclick="toggleSub(this)">
                                                <i class="bi bi-plus-square"></i>
                                            </span>
                                            <div class="form-check mb-0">
                                                <input class="form-check-input menu-check parent-check"
                                                    type="checkbox"
                                                    name="menu_items[]"
                                                    value="{{ $item->id }}"
                                                    id="menu_{{ $item->id }}"
                                                    data-slug="{{ $item->slug }}"
                                                    {{ in_array($item->slug, $assignedSlugs) ? 'checked' : '' }}>
                                                <label class="form-check-label rp-tree-label fw-bold text-gray-800" for="menu_{{ $item->id }}">
                                                    <i class="{{ $item->icon }} me-2 text-muted"></i>{{ __($item->name) }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    @if($visibleChildren->isNotEmpty())
                                    <ul class="rp-subtree mt-2" id="sub-{{ $item->slug }}">
                                        @foreach($visibleChildren as $child)
                                        <li class="py-1 border-0">
                                            <div class="rp-tree-item py-1">
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input menu-check child-check"
                                                        type="checkbox"
                                                        name="menu_items[]"
                                                        value="{{ $child->id }}"
                                                        id="menu_{{ $child->id }}"
                                                        data-parent="{{ $item->id }}"
                                                        data-slug="{{ $child->slug }}"
                                                        {{ in_array($child->slug, $assignedSlugs) ? 'checked' : '' }}>
                                                    <label class="form-check-label text-gray-700" for="menu_{{ $child->id }}">
                                                        <i class="{{ $child->icon }} me-2 text-muted small"></i>{{ __($child->name) }}
                                                    </label>
                                                </div>
                                            </div>
                                        </li>
                                        @endforeach
                                    </ul>
                                    @endif
                                </li>
                                @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleIdSelect = document.getElementById('role_id');
            const userIdSelect = document.getElementById('user_id');
            const roleGroup = document.getElementById('role_selector_group');
            const userGroup = document.getElementById('user_selector_group');
            const manageByRole = document.getElementById('manage_by_role');
            const manageByUser = document.getElementById('manage_by_user');
            const baseUrl = roleIdSelect.getAttribute('data-index-url');

            function updateVisibility() {
                if (manageByRole.checked) {
                    roleGroup.classList.remove('d-none');
                    userGroup.classList.add('d-none');
                } else {
                    roleGroup.classList.add('d-none');
                    userGroup.classList.remove('d-none');
                }
            }

            if (manageByRole && manageByUser) {
                manageByRole.addEventListener('change', updateVisibility);
                manageByUser.addEventListener('change', updateVisibility);
                updateVisibility();
            }

            // Handle role change redirect
            if (roleIdSelect) {
                roleIdSelect.addEventListener('change', function() {
                    const roleId = this.value;
                    if (roleId && baseUrl) {
                        const url = new URL(baseUrl, window.location.origin);
                        url.searchParams.set('role_id', roleId);
                        window.location.href = url.toString();
                    }
                });
            }

            // Handle user change redirect
            if (userIdSelect) {
                userIdSelect.addEventListener('change', function() {
                    const userId = this.value;
                    if (userId && baseUrl) {
                        const url = new URL(baseUrl, window.location.origin);
                        url.searchParams.set('user_id', userId);
                        window.location.href = url.toString();
                    }
                });
            }

            // "All" checkbox logic
            const checkAll = document.getElementById('checkAll');
            const allChecks = document.querySelectorAll('.menu-check');

            function updateCheckAll() {
                if (allChecks.length === 0) return;
                const checkedCount = [...allChecks].filter(c => c.checked).length;
                const allChecked = checkedCount === allChecks.length;
                const someChecked = checkedCount > 0 && !allChecked;

                checkAll.checked = allChecked;
                checkAll.indeterminate = someChecked;
            }

            if (checkAll) {
                checkAll.addEventListener('change', function() {
                    const isChecked = this.checked;
                    allChecks.forEach(c => {
                        if (c.checked !== isChecked) {
                            c.checked = isChecked;
                        }
                    });
                    updateCheckAll();
                });
            }

            // Parent ↔ child cascade
            document.querySelectorAll('.parent-check').forEach(parent => {
                parent.addEventListener('change', function() {
                    const children = document.querySelectorAll('.child-check[data-parent="' + this.value + '"]');
                    children.forEach(c => c.checked = this.checked);
                    updateCheckAll();
                });
            });

            document.querySelectorAll('.child-check').forEach(child => {
                child.addEventListener('change', function() {
                    const parentId = this.getAttribute('data-parent');
                    const siblings = document.querySelectorAll('.child-check[data-parent="' + parentId + '"]');
                    const parentCheck = document.getElementById('menu_' + parentId);
                    if (parentCheck) {
                        const anyChecked = [...siblings].some(c => c.checked);
                        parentCheck.checked = anyChecked;
                    }
                    updateCheckAll();
                });
            });

            allChecks.forEach(c => c.addEventListener('change', updateCheckAll));

            // Set initial "All" state
            updateCheckAll();

            // Auto-expand subtrees that have checked children
            document.querySelectorAll('.rp-subtree').forEach(sub => {
                const hasChecked = sub.querySelector('.menu-check:checked');
                if (hasChecked) {
                    sub.classList.add('open');
                    const toggle = document.querySelector('[data-target="' + sub.id + '"]');
                    if (toggle) {
                        const icon = toggle.querySelector('i');
                        if (icon) icon.className = 'bi bi-dash-square';
                    }
                }
            });
        });

        // Toggle function needs to be global for onclick
        function toggleSub(el) {
            const targetId = el.getAttribute('data-target');
            const sub = document.getElementById(targetId);
            if (!sub) return;
            const icon = el.querySelector('i');
            if (sub.classList.contains('open')) {
                sub.classList.remove('open');
                icon.className = 'bi bi-plus-square';
            } else {
                sub.classList.add('open');
                icon.className = 'bi bi-dash-square';
            }
        }
    </script>
    @endpush
</x-app-layout>
