<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css', 'resources/css/role-permissions.css'])
    @endpush

    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">

            <div class="rp-container">
                <div class="rp-header">
                    <i class="bi bi-shield-check me-2"></i>{{ __('Role Permission') }}
                </div>
                <div class="rp-body">
                    <form method="POST" action="{{ route('security.role-permissions.update') }}" id="rpForm">
                        @csrf
                        @method('PUT')

                        <div class="rp-split">
                            {{-- Left: role selector --}}
                            <div class="rp-left">
                                <div class="rp-form-row">
                                    <label for="role_id">{{ __('Role Name') }}&nbsp;:</label>
                                    <select name="role_id" id="role_id" class="form-select" data-index-url="{{ route('security.role-permissions.index') }}">
                                        @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ $selectedRole && $selectedRole->id === $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Right: menu tree --}}
                            <div class="rp-right">
                                <div class="rp-menu-title">{{ __('Menu List') }}</div>

                                <ul class="rp-tree">
                                    {{-- All toggle --}}
                                    <li class="rp-all-row">
                                        <div class="rp-tree-item">
                                            <span class="rp-tree-toggle no-children"></span>
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="checkAll">
                                                <label class="form-check-label rp-tree-label" for="checkAll">{{ __('All') }}</label>
                                            </div>
                                        </div>
                                    </li>

                                    @foreach($menuItems as $item)
                                    <li>
                                        <div class="rp-tree-item">
                                            <span class="rp-tree-toggle {{ $item->children->isEmpty() ? 'no-children' : '' }}"
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
                                                <label class="form-check-label rp-tree-label" for="menu_{{ $item->id }}">
                                                    {{ $item->name }}
                                                </label>
                                            </div>
                                        </div>

                                        @if($item->children->isNotEmpty())
                                        <ul class="rp-subtree" id="sub-{{ $item->slug }}">
                                            @foreach($item->children as $child)
                                            <li>
                                                <div class="rp-tree-item">
                                                    <span class="rp-tree-toggle no-children"></span>
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input menu-check child-check"
                                                            type="checkbox"
                                                            name="menu_items[]"
                                                            value="{{ $child->id }}"
                                                            id="menu_{{ $child->id }}"
                                                            data-parent="{{ $item->id }}"
                                                            data-slug="{{ $child->slug }}"
                                                            {{ in_array($child->slug, $assignedSlugs) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="menu_{{ $child->id }}">
                                                            {{ $child->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </li>
                                            @endforeach
                                        </ul>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4 gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> {{ __('Save Permissions') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle role change redirect
            const roleIdSelect = document.getElementById('role_id');
            if (roleIdSelect) {
                roleIdSelect.addEventListener('change', function() {
                    const roleId = this.value;
                    const baseUrl = this.getAttribute('data-index-url');

                    if (roleId && baseUrl) {
                        const url = new URL(baseUrl, window.location.origin);
                        url.searchParams.set('role_id', roleId);
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

            checkAll.addEventListener('change', function() {
                const isChecked = this.checked;
                allChecks.forEach(c => {
                    if (c.checked !== isChecked) {
                        c.checked = isChecked;
                        // For parent checks, we need to handle their siblings/children if needed
                        // but since we're setting EVERY checkbox, we don't need the individual triggers
                    }
                });
                updateCheckAll();
            });

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



