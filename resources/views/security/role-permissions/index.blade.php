<x-app-layout>
    @push('styles')
    @vite(['resources/css/role-permissions.css'])
    <style>
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

        /* Autocomplete Styles */
        .autocomplete-container {
            position: relative;
        }
        .autocomplete-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid var(--ui-border);
            border-radius: 0 0 var(--ui-radius-md) var(--ui-radius-md);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 1050;
            max-height: 250px;
            overflow-y: auto;
            display: none;
        }
        .autocomplete-results.show {
            display: block;
        }
        .autocomplete-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid var(--ui-border-faint);
            transition: all 0.2s;
            font-size: 0.875rem;
        }
        .autocomplete-item:last-child {
            border-bottom: none;
        }
        .autocomplete-item:hover, .autocomplete-item.active {
            background: var(--ui-bg-faint);
            color: var(--ui-primary);
        }
        .autocomplete-item .id-badge {
            font-family: monospace;
            font-weight: bold;
            color: var(--ui-secondary);
            margin-right: 0.5rem;
        }
    </style>
    @endpush

    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
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
                <input type="hidden" name="manage_by" id="manage_by_input" value="{{ $manageBy }}">

                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="rp-header-card h-100">
                            <div class="ui-panel-title mb-4">
                                <i class="bi bi-person-gear me-2 text-success"></i>{{ __('Selection Context') }}
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted mb-2">{{ __('Manage Permissions By') }}</label>
                                <div class="rp-manage-toggle" role="group" aria-label="{{ __('Manage Permissions By') }}">
                                    <input type="radio" class="btn-check" name="manage_by_switch" id="manage_by_role" value="role" {{ $manageBy === 'role' ? 'checked' : '' }}>
                                    <label class="rp-manage-option" for="manage_by_role">{{ __('Role') }}</label>

                                    <input type="radio" class="btn-check" name="manage_by_switch" id="manage_by_user" value="user" {{ $manageBy === 'user' ? 'checked' : '' }}>
                                    <label class="rp-manage-option" for="manage_by_user">{{ __('Employee') }}</label>
                                </div>
                            </div>

                            <div id="role_selector_group" class="{{ $manageBy === 'user' ? 'd-none' : '' }}">
                                <div class="mb-3">
                                    <label for="role_id" class="form-label small fw-bold text-muted">{{ __('System Role') }}</label>
                                    <select name="role_id" id="role_id" class="form-select rounded-3 border-light shadow-sm" data-index-url="{{ route('security.role-permissions.index') }}">
                                        <option value="">{{ __('--- Select Role ---') }}</option>
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

                            <div id="user_selector_group" class="{{ $manageBy === 'role' ? 'd-none' : '' }}">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">{{ __('Search & Target Employee') }}</label>
                                    
                                    {{-- Enhanced Search Box with Autocomplete --}}
                                    <div class="autocomplete-container">
                                        <div class="input-group mb-2 shadow-sm">
                                            <span class="input-group-text bg-white border-end-0 text-muted">
                                                <i class="bi bi-search"></i>
                                            </span>
                                            <input type="text" id="employee_search_input" class="form-control border-start-0 ps-0" placeholder="{{ __('Search Name or ID...') }}" autocomplete="off">
                                            <button class="btn btn-outline-secondary border-start-0 d-none" type="button" id="clear_employee_search" title="{{ __('Clear Search') }}">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                        <div id="autocomplete-results" class="autocomplete-results"></div>
                                    </div>

                                    {{-- Hidden Select for Form Submission --}}
                                    <select name="user_id" id="user_id" class="d-none" data-index-url="{{ route('security.role-permissions.index') }}">
                                        <option value="">{{ __('--- Select Employee ---') }}</option>
                                        @foreach($users as $user)
                                        <option value="{{ $user->id }}" 
                                            {{ $selectedUser && $selectedUser->id === $user->id ? 'selected' : '' }}
                                            data-name="{{ $user->name }}"
                                            data-code="{{ $user->employee->employee_code ?? 'N/A' }}">
                                            {{ $user->employee->employee_code ?? 'N/A' }} - {{ $user->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    
                                    <div id="selected_employee_badge" class="mt-2 {{ $selectedUser ? '' : 'd-none' }}">
                                        <div class="alert alert-success py-2 px-3 rounded-3 d-flex align-items-center mb-0 border-0 shadow-sm">
                                            <i class="bi bi-person-check-fill me-2"></i>
                                            <span class="small fw-bold" id="active_employee_name">
                                                {{ $selectedUser ? ($selectedUser->employee->employee_code ?? 'N/A') . ' - ' . $selectedUser->name : '' }}
                                            </span>
                                        </div>
                                    </div>

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

                    <div class="col-lg-8">
                        <div id="rolePermissionTree" data-tree-url="{{ route('security.role-permissions.tree') }}">
                            @include('security.role-permissions.partials.tree', [
                                'menuItems' => $menuItems,
                                'manageBy' => $manageBy,
                                'selectedRole' => $selectedRole,
                                'selectedUser' => $selectedUser,
                                'assignedSlugs' => $assignedSlugs,
                            ])
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
            const treeContainer = document.getElementById('rolePermissionTree');
            const treeUrl = treeContainer ? treeContainer.getAttribute('data-tree-url') : '';
            const manageByInput = document.getElementById('manage_by_input');
            const submitButton = document.querySelector('#rpForm button[type="submit"]');

            function updateVisibility() {
                if (manageByRole.checked) {
                    roleGroup.classList.remove('d-none');
                    userGroup.classList.add('d-none');
                } else {
                    roleGroup.classList.add('d-none');
                    userGroup.classList.remove('d-none');
                }
            }

            function getCurrentManageBy() {
                return manageByRole.checked ? 'role' : 'user';
            }

            function buildContextUrl(base, manageBy, selectedId) {
                const url = new URL(base, window.location.origin);
                url.searchParams.set('manage_by', manageBy);

                if (manageBy === 'role') {
                    if (selectedId) {
                        url.searchParams.set('role_id', selectedId);
                    }
                } else if (selectedId) {
                    url.searchParams.set('user_id', selectedId);
                }

                return url.toString();
            }

            function updateSubmitState() {
                if (!submitButton) return;

                const hasSelection = getCurrentManageBy() === 'role'
                    ? Boolean(roleIdSelect && roleIdSelect.value)
                    : Boolean(userIdSelect && userIdSelect.value);

                submitButton.disabled = !hasSelection;
            }

            async function loadTree(manageBy, selectedId) {
                if (!treeContainer || !treeUrl) return;

                treeContainer.classList.add('rp-tree-loading');

                try {
                    const response = await fetch(buildContextUrl(treeUrl, manageBy, selectedId), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Failed to load permission tree.');
                    }

                    treeContainer.innerHTML = await response.text();
                    if (manageByInput) {
                        manageByInput.value = manageBy;
                    }
                    window.history.replaceState({}, '', buildContextUrl(baseUrl, manageBy, selectedId));
                    initTreePanel();
                } catch (error) {
                    console.error(error);
                } finally {
                    treeContainer.classList.remove('rp-tree-loading');
                }
            }

            function initTreePanel() {
                const selectedPermissionCount = document.getElementById('selectedPermissionCount');
                const expandAllModulesBtn = document.getElementById('expandAllModules');
                const checkAll = document.getElementById('checkAll');
                const allChecks = treeContainer ? treeContainer.querySelectorAll('.menu-check') : [];

                function setModuleExpanded(toggle, isExpanded) {
                    if (!toggle) return;
                    const targetId = toggle.getAttribute('data-target');
                    const sub = document.getElementById(targetId);
                    if (!sub) return;

                    sub.classList.toggle('open', isExpanded);
                    toggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');

                    const icon = toggle.querySelector('i');
                    if (icon) {
                        icon.className = isExpanded ? 'bi bi-chevron-up' : 'bi bi-chevron-down';
                    }
                }

                function refreshExpandAllLabel() {
                    if (!expandAllModulesBtn) return;
                    const toggles = [...treeContainer.querySelectorAll('.rp-tree-toggle[data-target]')];
                    const allExpanded = toggles.length > 0 && toggles.every(toggle => toggle.getAttribute('aria-expanded') === 'true');

                    expandAllModulesBtn.innerHTML = allExpanded
                        ? "<i class=\"bi bi-arrows-collapse me-1\"></i>{{ __('Collapse All') }}"
                        : "<i class=\"bi bi-arrows-expand me-1\"></i>{{ __('Expand All') }}";
                }

                function syncModuleState(parentId) {
                    const card = treeContainer.querySelector('[data-parent-card="' + parentId + '"]');
                    const stateLabel = treeContainer.querySelector('[data-module-state="' + parentId + '"]');
                    const parentCheck = document.getElementById('menu_' + parentId);
                    const children = treeContainer.querySelectorAll('.child-check[data-parent="' + parentId + '"]');
                    if (!card || !parentCheck) return;

                    if (!stateLabel || children.length === 0) {
                        card.classList.toggle('is-active', parentCheck.checked);
                        card.classList.toggle('is-full', parentCheck.checked);
                        return;
                    }

                    const checkedChildren = [...children].filter(c => c.checked).length;
                    card.classList.toggle('is-active', checkedChildren > 0);
                    card.classList.toggle('is-full', checkedChildren === children.length);
                    stateLabel.textContent = checkedChildren + ' / ' + children.length + ' selected';
                }

                function updateCheckAll() {
                    if (allChecks.length === 0) {
                        if (selectedPermissionCount) {
                            selectedPermissionCount.textContent = '0';
                        }
                        if (checkAll) {
                            checkAll.checked = false;
                            checkAll.indeterminate = false;
                        }
                        return;
                    }

                    const checkedCount = [...allChecks].filter(c => c.checked).length;
                    const allChecked = checkedCount === allChecks.length;
                    const someChecked = checkedCount > 0 && !allChecked;

                    if (checkAll) {
                        checkAll.checked = allChecked;
                        checkAll.indeterminate = someChecked;
                    }

                    if (selectedPermissionCount) {
                        selectedPermissionCount.textContent = checkedCount;
                    }

                    treeContainer.querySelectorAll('.parent-check').forEach(parent => {
                        syncModuleState(parent.value);
                    });
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

                treeContainer.querySelectorAll('.parent-check').forEach(parent => {
                    parent.addEventListener('change', function() {
                        const children = treeContainer.querySelectorAll('.child-check[data-parent="' + this.value + '"]');
                        children.forEach(c => c.checked = this.checked);
                        syncModuleState(this.value);
                        updateCheckAll();
                    });
                });

                treeContainer.querySelectorAll('.child-check').forEach(child => {
                    child.addEventListener('change', function() {
                        const parentId = this.getAttribute('data-parent');
                        const siblings = treeContainer.querySelectorAll('.child-check[data-parent="' + parentId + '"]');
                        const parentCheck = document.getElementById('menu_' + parentId);
                        if (parentCheck) {
                            const anyChecked = [...siblings].some(c => c.checked);
                            parentCheck.checked = anyChecked;
                        }
                        syncModuleState(parentId);
                        updateCheckAll();
                    });
                });

                allChecks.forEach(c => c.addEventListener('change', updateCheckAll));
                updateCheckAll();

                if (expandAllModulesBtn) {
                    expandAllModulesBtn.addEventListener('click', function() {
                        const toggles = [...treeContainer.querySelectorAll('.rp-tree-toggle[data-target]')];
                        const shouldExpand = toggles.some(toggle => toggle.getAttribute('aria-expanded') !== 'true');

                        toggles.forEach(toggle => setModuleExpanded(toggle, shouldExpand));
                        refreshExpandAllLabel();
                    });
                }

                treeContainer.querySelectorAll('.rp-subtree').forEach(sub => {
                    const toggle = treeContainer.querySelector('[data-target="' + sub.id + '"]');
                    const hasChecked = sub.querySelector('.menu-check:checked');
                    setModuleExpanded(toggle, Boolean(hasChecked));
                });

                refreshExpandAllLabel();
            }

            if (manageByRole && manageByUser) {
                manageByRole.addEventListener('change', function() {
                    updateVisibility();
                    if (manageByInput) manageByInput.value = 'role';
                    updateSubmitState();
                    loadTree('role', roleIdSelect ? roleIdSelect.value : '');
                });
                manageByUser.addEventListener('change', function() {
                    updateVisibility();
                    if (manageByInput) manageByInput.value = 'user';
                    updateSubmitState();
                    loadTree('user', userIdSelect ? userIdSelect.value : '');
                });
                updateVisibility();
            }

            if (roleIdSelect) {
                roleIdSelect.addEventListener('change', function() {
                    updateSubmitState();
                    loadTree('role', this.value);
                });
            }

            if (userIdSelect) {
                userIdSelect.addEventListener('change', function() {
                    updateSubmitState();
                    loadTree('user', this.value);
                });
            }

            // Floating Autocomplete Employee Search Logic
            const searchInput = document.getElementById('employee_search_input');
            const clearSearchBtn = document.getElementById('clear_employee_search');
            const resultsDiv = document.getElementById('autocomplete-results');
            const originalOptions = userIdSelect ? Array.from(userIdSelect.options).slice(1) : []; // Skip placeholder
            const selectedBadge = document.getElementById('selected_employee_badge');
            const activeEmployeeName = document.getElementById('active_employee_name');
            let activeIndex = -1;

            function renderSuggestions() {
                if (!searchInput || !resultsDiv) return;
                
                const searchTerm = searchInput.value.toLowerCase().trim();
                
                // Show/hide clear button
                if (clearSearchBtn) {
                    clearSearchBtn.classList.toggle('d-none', searchTerm === '');
                }

                if (searchTerm.length < 1) {
                    resultsDiv.classList.remove('show');
                    resultsDiv.innerHTML = '';
                    return;
                }

                const filtered = originalOptions.filter(opt => {
                    return opt.text.toLowerCase().includes(searchTerm);
                }).slice(0, 10); // Show top 10 matches

                if (filtered.length === 0) {
                    resultsDiv.innerHTML = '<div class="autocomplete-item text-muted text-center py-3">{{ __('No matching employees') }}</div>';
                    resultsDiv.classList.add('show');
                    return;
                }

                resultsDiv.innerHTML = '';
                filtered.forEach((opt, index) => {
                    const item = document.createElement('div');
                    item.className = 'autocomplete-item';
                    if (index === activeIndex) item.classList.add('active');
                    
                    const code = opt.getAttribute('data-code') || 'N/A';
                    const name = opt.getAttribute('data-name');
                    
                    item.innerHTML = `<span class="id-badge">${code}</span> ${name}`;
                    item.addEventListener('mousedown', (e) => {
                        e.preventDefault(); // Prevent blur
                        selectUser(opt.value, `${code} - ${name}`);
                    });
                    resultsDiv.appendChild(item);
                });
                
                resultsDiv.classList.add('show');
            }

            function selectUser(userId, displayText) {
                userIdSelect.value = userId;
                searchInput.value = '';
                resultsDiv.classList.remove('show');
                
                if (selectedBadge) {
                    selectedBadge.classList.remove('d-none');
                    activeEmployeeName.textContent = displayText;
                }
                
                // Trigger original logic
                updateSubmitState();
                loadTree('user', userId);
            }

            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    activeIndex = -1;
                    renderSuggestions();
                });

                searchInput.addEventListener('focus', renderSuggestions);

                searchInput.addEventListener('keydown', (e) => {
                    const items = resultsDiv.querySelectorAll('.autocomplete-item');
                    if (!resultsDiv.classList.contains('show') || items.length === 0) return;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        activeIndex = Math.min(activeIndex + 1, items.length - 1);
                        renderSuggestions();
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        activeIndex = Math.max(activeIndex - 1, 0);
                        renderSuggestions();
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (activeIndex > -1) {
                            const activeItem = items[activeIndex];
                            activeItem.dispatchEvent(new MouseEvent('mousedown'));
                        }
                    } else if (e.key === 'Escape') {
                        resultsDiv.classList.remove('show');
                    }
                });

                searchInput.addEventListener('blur', () => {
                    setTimeout(() => resultsDiv.classList.remove('show'), 200);
                });
            }

            if (clearSearchBtn) {
                clearSearchBtn.addEventListener('click', function() {
                    searchInput.value = '';
                    renderSuggestions();
                    searchInput.focus();
                });
            }

            updateSubmitState();
            initTreePanel();
        });

        function toggleSub(el) {
            const targetId = el.getAttribute('data-target');
            const sub = document.getElementById(targetId);
            if (!sub) return;

            const isExpanded = !sub.classList.contains('open');
            sub.classList.toggle('open', isExpanded);
            el.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');

            const icon = el.querySelector('i');
            if (icon) {
                icon.className = isExpanded ? 'bi bi-chevron-up' : 'bi bi-chevron-down';
            }

            const expandAllModulesBtn = document.getElementById('expandAllModules');
            const treeContainer = document.getElementById('rolePermissionTree');
            if (expandAllModulesBtn && treeContainer) {
                const toggles = [...treeContainer.querySelectorAll('.rp-tree-toggle[data-target]')];
                const allExpanded = toggles.length > 0 && toggles.every(toggle => toggle.getAttribute('aria-expanded') === 'true');

                expandAllModulesBtn.innerHTML = allExpanded
                    ? "<i class=\"bi bi-arrows-collapse me-1\"></i>{{ __('Collapse All') }}"
                    : "<i class=\"bi bi-arrows-expand me-1\"></i>{{ __('Expand All') }}";
            }
        }
    </script>
    @endpush
</x-app-layout>
