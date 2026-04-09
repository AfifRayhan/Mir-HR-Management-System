<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 text-2xl font-bold">{{ __('Leave Accounts Management') }}</h5>
                        <p class="mb-0 text-gray-500">{{ __('Initialize and monitor employee leave balances for') }} {{ $currentYear }}</p>
                    </div>
                </div>
            </div>


            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4 px-4 py-3 small shadow-sm mb-4" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="row g-4">
                <!-- Initialize Account Form -->
                <div class="col-lg-4">
                    <div class="hr-panel">
                        <div class="hr-panel-title">
                            <i class="bi bi-person-plus me-2 text-success"></i>{{ __('Initialize Employee Account') }}
                        </div>

                        <p class="small text-muted mb-4">
                            {{ __('Select an employee, choose which leave types to enable, then initialize.') }}
                        </p>

                        <form action="{{ route('personnel.leave-balances.store') }}" method="POST" id="initForm">
                            @csrf

                            {{-- Employee --}}
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted" for="employee_id">
                                    {{ __('Select Employee') }} <span class="text-danger">*</span>
                                </label>
                                <select name="employee_id" id="employee_id" class="form-select rounded-3" required>
                                    <option value="">{{ __('Choose employee…') }}</option>
                                    @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }} ({{ $emp->employee_code }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Year --}}
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted" for="year">
                                    {{ __('Target Year') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="year" id="year" class="form-control rounded-3"
                                       value="{{ old('year', $currentYear) }}" required min="2020" max="2050">
                            </div>

                            {{-- Leave Type Checkboxes (dynamic) --}}
                            <div class="mb-4" id="leaveTypesSection" style="display:none;">
                                <label class="form-label small fw-bold text-muted d-flex justify-content-between align-items-center">
                                    <span>{{ __('Leave Types') }} <span class="text-danger">*</span></span>
                                </label>

                                <div id="leaveTypesLoading" class="text-center py-3 d-none">
                                    <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                                    <span class="ms-2 small text-muted">{{ __('Loading…') }}</span>
                                </div>

                                <div id="leaveTypesCheckboxes" class="border rounded-3 p-3" style="max-height:280px; overflow-y:auto; background:#f9fafb;">
                                    @foreach($leaveTypes as $type)
                                    <div class="form-check mb-2 leave-type-item" data-id="{{ $type->id }}">
                                        <input class="form-check-input leave-type-checkbox"
                                               type="checkbox"
                                               name="leave_type_ids[]"
                                               value="{{ $type->id }}"
                                               id="lt_{{ $type->id }}"
                                               {{ collect(old('leave_type_ids', []))->contains($type->id) ? 'checked' : '' }}>
                                        <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="lt_{{ $type->id }}">
                                            <span class="small fw-semibold">{{ $type->name }}</span>
                                            <span class="badge bg-secondary ms-2" style="font-size:0.65rem;">
                                                {{ $type->total_days_per_year }} days/yr
                                            </span>
                                        </label>
                                        <div class="lt-already-set d-none ms-4 mt-1">
                                            <span class="badge bg-success-soft text-success" style="font-size:0.65rem;">
                                                <i class="bi bi-check-circle me-1"></i>{{ __('Already initialized') }}
                                            </span>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Placeholder when no employee chosen --}}
                            <div id="leaveTypesPlaceholder" class="mb-4">
                                <div class="border rounded-3 p-3 text-center text-muted small" style="background:#f9fafb;">
                                    <i class="bi bi-person-circle d-block mb-2 fs-4 opacity-50"></i>
                                    {{ __('Select an employee and year to see available leave types') }}
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success w-100 py-2 rounded-pill shadow-sm" id="initBtn" disabled>
                                <i class="bi bi-wallet2 me-2"></i>{{ __('Initialize Selected Types') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Existing Balances List -->
                <div class="col-lg-8">
                    <div class="hr-panel p-0 overflow-hidden">
                        <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-list-task me-2 text-success"></i>{{ __('Initialized Accounts') }}
                                <span class="badge bg-secondary ms-2" style="font-size:0.7rem;">{{ $currentYear }}</span>
                            </h5>
                            
                            <!-- Search Form -->
                            <form action="{{ route('personnel.leave-balances.index') }}" method="GET" class="d-flex gap-2">
                                <div class="input-group input-group-sm" style="width: 250px;">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                           placeholder="{{ __('Search employee…') }}" value="{{ $search }}">
                                </div>
                                <button type="submit" class="btn btn-success btn-sm px-3 rounded-pill">{{ __('Search') }}</button>
                                @if($search)
                                    <a href="{{ route('personnel.leave-balances.index') }}" class="btn btn-light btn-sm px-3 rounded-pill">{{ __('Clear') }}</a>
                                @endif
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table hr-table mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">
                                            <a href="{{ route('personnel.leave-balances.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => $sort === 'name' && $direction === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark d-flex align-items-center">
                                                {{ __('Employee') }}
                                                @if($sort === 'name')
                                                    <i class="bi bi-sort-alpha-{{ $direction === 'asc' ? 'down' : 'up' }} ms-1 text-success"></i>
                                                @else
                                                    <i class="bi bi-arrow-down-up ms-1 text-muted small" style="font-size: 0.7rem;"></i>
                                                @endif
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ route('personnel.leave-balances.index', array_merge(request()->query(), ['sort' => 'department_id', 'direction' => $sort === 'department_id' && $direction === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark d-flex align-items-center">
                                                {{ __('Department / Role') }}
                                                @if($sort === 'department_id')
                                                    <i class="bi bi-sort-numeric-{{ $direction === 'asc' ? 'down' : 'up' }} ms-1 text-success"></i>
                                                @else
                                                    <i class="bi bi-arrow-down-up ms-1 text-muted small" style="font-size: 0.7rem;"></i>
                                                @endif
                                            </a>
                                        </th>
                                        <th>{{ __('Leave Types Allocated') }}</th>
                                        <th class="pe-4">{{ __('Total Allocation') }}</th>
                                        <th class="pe-4 text-end">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($paginatedEmployees as $emp)
                                    @php
                                        $employeeBalances = $balances->get($emp->id, collect());
                                        $totalDays = $employeeBalances->sum('opening_balance');
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="emp-avatar-sm me-3">
                                                    {{ strtoupper(substr($emp->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="font-bold text-gray-800">{{ $emp->name }}</div>
                                                    <div class="small text-muted">{{ $emp->employee_code }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small fw-bold">{{ $emp->department->name ?? 'N/A' }}</div>
                                            <div class="small text-muted">{{ $emp->designation->name ?? 'N/A' }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @if($employeeBalances->isEmpty())
                                                    <span class="text-muted small italic">{{ __('Not initialized') }}</span>
                                                @else
                                                    @foreach($employeeBalances as $bal)
                                                    <span class="badge rounded-pill px-2 py-1"
                                                          style="font-size:0.65rem; background:#e0f2fe; color:#0369a1;"
                                                          title="{{ $bal->remaining_days }} / {{ $bal->opening_balance }} remaining">
                                                        {{ $bal->leaveType->name ?? '—' }}
                                                        <span class="opacity-75">({{ $bal->remaining_days }}/{{ $bal->opening_balance }})</span>
                                                    </span>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </td>
                                        <td class="pe-4">
                                            <span class="badge @if($totalDays > 0) bg-secondary @else bg-light text-muted @endif rounded-pill">{{ $totalDays }} days</span>
                                        </td>
                                        <td class="pe-4 text-end">
                                            @if($employeeBalances->isNotEmpty())
                                            <button type="button" class="btn btn-sm btn-outline-success border-0" title="{{ __('Edit Balances') }}" data-bs-toggle="modal" data-bs-target="#editBalanceModal{{ $emp->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary border-0" disabled title="{{ __('Not initialized') }}">
                                                <i class="bi bi-pencil-square opacity-50"></i>
                                            </button>
                                            @endif
                                        </td>
                                    </tr>

 
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <i class="bi bi-wallet2 d-block mb-3 fs-1 opacity-25"></i>
                                            <span class="text-muted small">{{ __('No employees found.') }}</span>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        @if($paginatedEmployees->hasPages())
                        <div class="px-4 py-3 border-top bg-light">
                            {{ $paginatedEmployees->links() }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Edit Balance Modals -->
            @foreach($paginatedEmployees as $emp)
                @php
                    $employeeBalances = $balances->get($emp->id, collect());
                @endphp
                @if($employeeBalances->isNotEmpty())
                <div class="modal fade" id="editBalanceModal{{ $emp->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content rounded-4 border-0 shadow">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold text-success">{{ __('Edit Leave Balances for') }} {{ $emp->name }} ({{ $currentYear }})</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('personnel.leave-balances.update-bulk') }}" method="POST">
                                @csrf
                                <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                                <input type="hidden" name="year" value="{{ $currentYear }}">
                                <div class="modal-body py-4">
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle hr-table mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>{{ __('Leave Type') }}</th>
                                                    <th style="width: 120px;">{{ __('Opening') }}</th>
                                                    <th style="width: 120px;">{{ __('Used') }}</th>
                                                    <th style="width: 120px;">{{ __('Remaining') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($employeeBalances as $bal)
                                                <tr>
                                                    <td class="fw-bold">{{ $bal->leaveType->name ?? 'Unknown' }}</td>
                                                    <td>
                                                        <input type="number" step="0.5" class="form-control form-control-sm rounded-3" name="balances[{{ $bal->id }}][opening_balance]" value="{{ $bal->opening_balance }}" required min="0">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.5" class="form-control form-control-sm rounded-3" name="balances[{{ $bal->id }}][used_days]" value="{{ $bal->used_days }}" required min="0">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.5" class="form-control form-control-sm rounded-3" name="balances[{{ $bal->id }}][remaining_days]" value="{{ $bal->remaining_days }}" required min="0">
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="alert alert-info py-2 small mb-0 mt-3 rounded-3 border-0 bg-info-soft text-info">
                                        <i class="bi bi-info-circle me-1"></i> {{ __('Make sure Remaining = Opening - Used for consistency.') }}
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                    <button type="submit" class="btn btn-success rounded-pill px-4">{{ __('Save Changes') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
        </main>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const employeeSelect    = document.getElementById('employee_id');
        const yearInput         = document.getElementById('year');
        const section           = document.getElementById('leaveTypesSection');
        const placeholder       = document.getElementById('leaveTypesPlaceholder');
        const loading           = document.getElementById('leaveTypesLoading');
        const checkboxContainer = document.getElementById('leaveTypesCheckboxes');
        const initBtn           = document.getElementById('initBtn');
        const selectAllBtn      = document.getElementById('selectAllBtn');
        const clearAllBtn       = document.getElementById('clearAllBtn');

        const existingUrl = "{{ route('personnel.leave-balances.existing') }}";

        function updateInitBtn() {
            const anyChecked = checkboxContainer.querySelectorAll('.leave-type-checkbox:checked').length > 0;
            initBtn.disabled = !anyChecked;
        }

        function loadExisting() {
            const empId = employeeSelect.value;
            const year  = yearInput.value;

            if (!empId || !year) {
                section.style.display = 'none';
                placeholder.style.display = 'block';
                initBtn.disabled = true;
                return;
            }

            section.style.display = 'block';
            placeholder.style.display = 'none';
            loading.classList.remove('d-none');
            checkboxContainer.style.opacity = '0.4';

            fetch(`${existingUrl}?employee_id=${empId}&year=${year}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                const initialized = data.initialized || [];
                const allocations = data.allocations || {};

                checkboxContainer.querySelectorAll('.leave-type-item').forEach(function (item) {
                    const id        = parseInt(item.dataset.id);
                    const checkbox  = item.querySelector('.leave-type-checkbox');
                    const alreadyBadge = item.querySelector('.lt-already-set');
                    const badgeSpan = item.querySelector('.badge.bg-secondary');
                    
                    const isInit    = initialized.includes(id);
                    const allowedDays = allocations[id] !== undefined ? allocations[id] : 'N/A';

                    if (badgeSpan) {
                        badgeSpan.textContent = allowedDays + ' days/yr';
                    }

                    if (allowedDays === 0) {
                        checkbox.checked = false;
                        checkbox.disabled = true;
                        item.style.opacity = '0.55';
                        alreadyBadge.classList.add('d-none');
                    } else if (isInit) {
                        checkbox.checked  = false;
                        checkbox.disabled = true;
                        item.style.opacity = '0.55';
                        alreadyBadge.classList.remove('d-none');
                    } else {
                        checkbox.disabled = false;
                        checkbox.checked  = true;   // default: select available types
                        item.style.opacity = '1';
                        alreadyBadge.classList.add('d-none');
                    }
                });

                updateInitBtn();
            })
            .catch(() => {
                // On error just enable all checkboxes unchecked
                checkboxContainer.querySelectorAll('.leave-type-checkbox').forEach(cb => {
                    cb.disabled = false;
                    cb.checked  = false;
                });
                updateInitBtn();
            })
            .finally(() => {
                loading.classList.add('d-none');
                checkboxContainer.style.opacity = '1';
            });
        }

        employeeSelect.addEventListener('change', loadExisting);
        yearInput.addEventListener('change', loadExisting);

        selectAllBtn.addEventListener('click', function () {
            checkboxContainer.querySelectorAll('.leave-type-checkbox:not(:disabled)').forEach(cb => cb.checked = true);
            updateInitBtn();
        });

        clearAllBtn.addEventListener('click', function () {
            checkboxContainer.querySelectorAll('.leave-type-checkbox:not(:disabled)').forEach(cb => cb.checked = false);
            updateInitBtn();
        });

        checkboxContainer.addEventListener('change', updateInitBtn);

        // If old input exists (validation failed), reload state
        if (employeeSelect.value) {
            loadExisting();
        }
    });
    </script>
    @endpush
</x-app-layout>