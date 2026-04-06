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
                        <div class="px-4 py-3 border-bottom">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-list-task me-2 text-success"></i>{{ __('Initialized Accounts') }}
                                <span class="badge bg-secondary ms-2" style="font-size:0.7rem;">{{ $currentYear }}</span>
                            </h5>
                        </div>

                        <div class="table-responsive">
                            <table class="table hr-table mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">{{ __('Employee') }}</th>
                                        <th>{{ __('Department / Role') }}</th>
                                        <th>{{ __('Leave Types Allocated') }}</th>
                                        <th class="pe-4">{{ __('Total Allocation') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($balances as $employeeId => $employeeBalances)
                                    @php
                                        $emp       = $employeeBalances->first()->employee;
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
                                                @foreach($employeeBalances as $bal)
                                                <span class="badge rounded-pill px-2 py-1"
                                                      style="font-size:0.65rem; background:#e0f2fe; color:#0369a1;"
                                                      title="{{ $bal->remaining_days }} / {{ $bal->opening_balance }} remaining">
                                                    {{ $bal->leaveType->name ?? '—' }}
                                                    <span class="opacity-75">({{ $bal->remaining_days }}/{{ $bal->opening_balance }})</span>
                                                </span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="pe-4">
                                            <span class="badge bg-secondary rounded-pill">{{ $totalDays }} days</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <i class="bi bi-wallet2 d-block mb-3 fs-1 opacity-25"></i>
                                            <span class="text-muted small">{{ __('No leave accounts initialized for') }} {{ $currentYear }}.</span>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
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

                checkboxContainer.querySelectorAll('.leave-type-item').forEach(function (item) {
                    const id        = parseInt(item.dataset.id);
                    const checkbox  = item.querySelector('.leave-type-checkbox');
                    const alreadyBadge = item.querySelector('.lt-already-set');
                    const isInit    = initialized.includes(id);

                    if (isInit) {
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