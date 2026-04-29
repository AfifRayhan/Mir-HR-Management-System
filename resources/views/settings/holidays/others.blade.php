<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css', 'resources/css/custom-holidays.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @endpush

    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ __('National & Other Holiday Management') }}</h5>
                        <p class="mb-0 small text-muted">
                            {{ __('Welcome,') }}
                            {{ $employee ? $employee->name : ($user->name ?? __('HR Administrator')) }}
                            • {{ $roleName }}
                        </p>
                    </div>
                    <div class="text-end text-sm text-gray-500">
                        <i class="bi bi-calendar-event me-2 text-primary"></i>{{ now()->format('l, d M Y') }}
                    </div>
                </div>
            </div>


            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4 px-4 py-3 small shadow-sm mb-4" role="alert">
                <div class="fw-bold mb-2"><i class="bi bi-exclamation-octagon-fill me-2"></i>{{ __('Submission Errors') }}</div>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="row g-4">
                <!-- New Holiday Form -->
                <div class="col-lg-4">
                    <div class="ui-panel">
                        <div class="ui-panel-title">
                            <i class="bi bi-calendar-plus me-2 text-primary"></i>{{ __('Add New Holiday') }}
                        </div>

                        <form action="{{ route('settings.holidays.others.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Holiday Type') }} <span class="text-danger">*</span></label>
                                <select name="type" id="holiday_type" class="form-select form-select-sm rounded-3" required>
                                    <option value="National">{{ __('National') }}</option>
                                    <option value="Other">{{ __('Other') }}</option>
                                    <option value="Eid Day">{{ __('Eid Day') }}</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Year') }} <span class="text-danger">*</span></label>
                                <select name="year" class="form-select form-select-sm rounded-3" required>
                                    @for($i = date('Y'); $i <= date('Y')+5; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Holiday Title') }} <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control form-control-sm rounded-3" placeholder="{{ __('e.g. Eid-ul-Fitr') }}" required>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch mb-2 custom-switch">
                                    <input type="checkbox" name="all_office" id="all_office" class="form-check-input" value="1" checked>
                                    <label class="form-check-label small fw-bold text-muted ms-2" for="all_office">{{ __('Apply to All Offices') }}</label>
                                </div>
                            </div>

                            <div class="mb-3" id="office_select_row" style="display: none;">
                                <label class="form-label small fw-bold text-muted">{{ __('Specific Office') }} <span class="text-danger">*</span></label>
                                <select name="office_id" class="form-select form-select-sm rounded-3">
                                    <option value="">{{ __('Select Office') }}</option>
                                    @foreach($offices as $office)
                                    <option value="{{ $office->id }}">{{ $office->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6" id="from_date_col">
                                    <label class="form-label small fw-bold text-muted" id="from_date_label">{{ __('From Date') }} <span class="text-danger">*</span></label>
                                    <input type="text" id="add_from_date" name="from_date" class="form-control form-control-sm rounded-3" placeholder="Select date" readonly required>
                                </div>
                                <div class="col-6" id="to_date_col">
                                    <label class="form-label small fw-bold text-muted">{{ __('To Date') }} <span class="text-danger">*</span></label>
                                    <input type="text" id="add_to_date" name="to_date" class="form-control form-control-sm rounded-3" placeholder="Select date" readonly required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Remarks') }}</label>
                                <textarea name="remarks" class="form-control form-control-sm rounded-3" rows="2" placeholder="{{ __('Optional notes...') }}"></textarea>
                            </div>

                             <div class="mb-4">
                                <div class="form-check form-switch p-2 bg-light rounded-3 px-3">
                                    <input class="form-check-input ms-0 me-3" type="checkbox" name="is_active" role="switch" id="isActiveSwitchDefault" checked>
                                    <label class="form-check-label small fw-bold text-muted" for="isActiveSwitchDefault">{{ __('Active Status') }}</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-plus-circle me-2"></i>{{ __('Save Holiday') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Holiday List -->
                <div class="col-lg-8">
                    <!-- Filter Bar -->
                    <div class="ui-filter-bar mb-4">
                        <form action="{{ route('settings.holidays.others.index') }}" method="GET" class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Search') }}</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0 ps-0 rounded-end-3" placeholder="Holiday title..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Office') }}</label>
                                <select name="office_id" class="form-select form-select-sm rounded-3">
                                    <option value="">{{ __('All Offices') }}</option>
                                    @foreach($offices as $office)
                                    <option value="{{ $office->id }}" {{ request('office_id') == $office->id ? 'selected' : '' }}>{{ $office->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted">{{ __('Year') }}</label>
                                <select name="year" class="form-select form-select-sm rounded-3">
                                    <option value="">{{ __('All Years') }}</option>
                                    @for($i = date('Y') - 1; $i <= date('Y') + 5; $i++)
                                    <option value="{{ $i }}" {{ request('year') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted">{{ __('Type') }}</label>
                                <select name="type" class="form-select form-select-sm rounded-3">
                                    <option value="">{{ __('All Types') }}</option>
                                    <option value="National" {{ request('type') == 'National' ? 'selected' : '' }}>{{ __('National') }}</option>
                                    <option value="Other" {{ request('type') == 'Other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                                    <option value="Eid Day" {{ request('type') == 'Eid Day' ? 'selected' : '' }}>{{ __('Eid Day') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end gap-1">
                                <button type="submit" class="btn btn-primary btn-sm flex-grow-1 rounded-3">{{ __('Filter') }}</button>
                                <a href="{{ route('settings.holidays.others.index') }}" class="btn btn-light btn-sm flex-grow-1 rounded-3" title="{{ __('Clear') }}">{{ __('Clear') }}</a>
                            </div>
                        </form>
                    </div>

                    <div class="ui-panel">
                        <div class="ui-panel-title mb-4">
                            <i class="bi bi-calendar-event me-2 text-primary"></i>{{ __('Holiday Calendar') }}
                        </div>

                        <div class="table-responsive">
                            <table class="ui-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th>{{ __('Year') }}</th>
                                        <th>{{ __('Office Scope') }}</th>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('Timeline') }}</th>
                                        <th>{{ __('Days') }}</th>
                                        <th class="text-center">{{ __('Status') }}</th>
                                        <th class="text-end pe-4">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($holidays as $holiday)
                                    <tr>
                                        <td class="fw-bold">{{ $holiday->year }}</td>
                                        <td>
                                            @if($holiday->all_office)
                                            <span class="hr-badge ui-badge-global">{{ __('Global') }}</span>
                                            @else
                                            <span class="small text-truncate d-inline-block" style="max-width: 120px;">{{ $holiday->office->name }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold text-primary">{{ $holiday->title }}</div>
                                            <div class="small text-muted">{{ $holiday->type }}</div>
                                        </td>
                                        <td>
                                            <div class="small fw-500">{{ $holiday->from_date->format('d M') }} - {{ $holiday->to_date->format('d M Y') }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary rounded-pill">{{ $holiday->total_days }} {{ Str::plural('day', $holiday->total_days) }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($holiday->is_active)
                                            <span class="hr-status-badge hr-status-active">{{ __('Active') }}</span>
                                            @else
                                            <span class="hr-status-badge hr-status-inactive">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary border-0" data-bs-toggle="modal" data-bs-target="#editHolidayModal{{ $holiday->id }}" title="{{ __('Edit') }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                @php $statement = 'Are you sure you want to delete this holiday?'; @endphp
                                                <form action="{{ route('settings.holidays.others.destroy', $holiday->id) }}" method="POST" data-confirm data-confirm-message="{{ $statement }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="{{ __('Delete') }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-calendar-x d-block mb-3 fs-1 opacity-50"></i>
                                                {{ __('No holidays found for the selected criteria.') }}
                                            </div>
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

    <!-- Edit Holiday Modals -->
    @foreach($holidays as $holiday)
    <div class="modal fade" id="editHolidayModal{{ $holiday->id }}" tabindex="-1" aria-labelledby="editHolidayModalLabel{{ $holiday->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="editHolidayModalLabel{{ $holiday->id }}"><i class="bi bi-pencil-square me-2 text-primary"></i>{{ __('Edit Holiday') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('settings.holidays.others.update', $holiday->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body pb-0">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">{{ __('Holiday Type') }} <span class="text-danger">*</span></label>
                            <select name="type" class="form-select form-select-sm rounded-3 edit-holiday-type" data-id="{{ $holiday->id }}" required>
                                <option value="National" {{ $holiday->type == 'National' ? 'selected' : '' }}>{{ __('National') }}</option>
                                <option value="Other" {{ $holiday->type == 'Other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                                <option value="Eid Day" {{ $holiday->type == 'Eid Day' ? 'selected' : '' }}>{{ __('Eid Day') }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">{{ __('Year') }} <span class="text-danger">*</span></label>
                            <select name="year" class="form-select form-select-sm rounded-3" required>
                                @for($i = date('Y') - 1; $i <= date('Y')+5; $i++)
                                    <option value="{{ $i }}" {{ $holiday->year == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">{{ __('Holiday Title') }} <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-sm rounded-3" value="{{ $holiday->title }}" required>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch mb-2 custom-switch">
                                <input type="checkbox" name="all_office" id="edit_all_office_{{ $holiday->id }}" class="form-check-input edit-all-office" data-target="edit_office_select_row_{{ $holiday->id }}" value="1" {{ $holiday->all_office ? 'checked' : '' }}>
                                <label class="form-check-label small fw-bold text-muted ms-2" for="edit_all_office_{{ $holiday->id }}">{{ __('Apply to All Offices') }}</label>
                            </div>
                        </div>

                        <div class="mb-3" id="edit_office_select_row_{{ $holiday->id }}" @if($holiday->all_office) style="display: none;" @else style="display: block;" @endif>
                            <label class="form-label small fw-bold text-muted">{{ __('Specific Office') }} <span class="text-danger">*</span></label>
                            <select name="office_id" class="form-select form-select-sm rounded-3" {{ !$holiday->all_office ? 'required' : '' }}>
                                <option value="">{{ __('Select Office') }}</option>
                                @foreach($offices as $office)
                                <option value="{{ $office->id }}" {{ $holiday->office_id == $office->id ? 'selected' : '' }}>{{ $office->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6" id="edit_from_date_col_{{ $holiday->id }}">
                                <label class="form-label small fw-bold text-muted" id="edit_from_date_label_{{ $holiday->id }}">{{ $holiday->type == 'Eid Day' ? __('Date') : __('From Date') }} <span class="text-danger">*</span></label>
                                <input type="text" name="from_date" id="edit_from_date_{{ $holiday->id }}" class="form-control form-control-sm rounded-3" value="{{ $holiday->from_date->format('Y-m-d') }}" placeholder="Select date" readonly required>
                            </div>
                            <div class="col-6" id="edit_to_date_col_{{ $holiday->id }}" @if($holiday->type == 'Eid Day') style="display: none;" @endif>
                                <label class="form-label small fw-bold text-muted">{{ __('To Date') }} <span class="text-danger">*</span></label>
                                <input type="text" name="to_date" id="edit_to_date_{{ $holiday->id }}" class="form-control form-control-sm rounded-3" value="{{ $holiday->to_date->format('Y-m-d') }}" placeholder="Select date" readonly required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">{{ __('Remarks') }}</label>
                            <textarea name="remarks" class="form-control form-control-sm rounded-3" rows="2">{{ $holiday->remarks }}</textarea>
                        </div>

                        <div class="mb-4">
                             <div class="form-check form-switch p-2 bg-light rounded-3 px-3">
                                 <input class="form-check-input ms-0 me-3" type="checkbox" name="is_active" role="switch" id="edit_is_active_{{ $holiday->id }}" {{ $holiday->is_active ? 'checked' : '' }}>
                                 <label class="form-check-label small fw-bold text-muted" for="edit_is_active_{{ $holiday->id }}">{{ __('Active Status') }}</label>
                             </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 pb-3">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="bi bi-save me-2"></i>{{ __('Update Holiday') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Init Flatpickr for the Add Holiday form
        flatpickr('#add_from_date', { dateFormat: 'Y-m-d', allowInput: false });
        flatpickr('#add_to_date',   { dateFormat: 'Y-m-d', allowInput: false });

        // Logic for "Eid Day" in Add Form
        const holidayType = document.getElementById('holiday_type');
        const fromDateCol = document.getElementById('from_date_col');
        const toDateCol = document.getElementById('to_date_col');
        const fromDateLabel = document.getElementById('from_date_label');
        const addFromDateInput = document.getElementById('add_from_date');
        const addToDateInput = document.getElementById('add_to_date');

        holidayType.addEventListener('change', function() {
            if (this.value === 'Eid Day') {
                toDateCol.style.display = 'none';
                fromDateCol.classList.remove('col-6');
                fromDateCol.classList.add('col-12');
                fromDateLabel.innerHTML = "{{ __('Date') }} <span class=\"text-danger\">*</span>";
                addToDateInput.value = addFromDateInput.value;
            } else {
                toDateCol.style.display = 'block';
                fromDateCol.classList.remove('col-12');
                fromDateCol.classList.add('col-6');
                fromDateLabel.innerHTML = "{{ __('From Date') }} <span class=\"text-danger\">*</span>";
            }
        });

        addFromDateInput.addEventListener('change', function() {
            if (holidayType.value === 'Eid Day') {
                addToDateInput.value = this.value;
            }
        });

        // Init Flatpickr inside each edit modal when it's shown
        document.querySelectorAll('[id^="editHolidayModal"]').forEach(function(modal) {
            modal.addEventListener('shown.bs.modal', function() {
                const id = modal.id.replace('editHolidayModal', '');
                flatpickr('#edit_from_date_' + id, { dateFormat: 'Y-m-d', allowInput: false });
                flatpickr('#edit_to_date_'   + id, { dateFormat: 'Y-m-d', allowInput: false });

                // Logic for "Eid Day" in Edit Modal
                const editType = modal.querySelector('.edit-holiday-type');
                const editFromDate = document.getElementById('edit_from_date_' + id);
                const editToDate = document.getElementById('edit_to_date_' + id);
                const editFromCol = document.getElementById('edit_from_date_col_' + id);
                const editToCol = document.getElementById('edit_to_date_col_' + id);
                const editFromLabel = document.getElementById('edit_from_date_label_' + id);

                const handleEditTypeChange = (val) => {
                    if (val === 'Eid Day') {
                        editToCol.style.display = 'none';
                        editFromCol.classList.remove('col-6');
                        editFromCol.classList.add('col-12');
                        editFromLabel.innerHTML = "{{ __('Date') }} <span class=\"text-danger\">*</span>";
                        editToDate.value = editFromDate.value;
                    } else {
                        editToCol.style.display = 'block';
                        editFromCol.classList.remove('col-12');
                        editFromCol.classList.add('col-6');
                        editFromLabel.innerHTML = "{{ __('From Date') }} <span class=\"text-danger\">*</span>";
                    }
                };

                editType.addEventListener('change', function() {
                    handleEditTypeChange(this.value);
                });

                editFromDate.addEventListener('change', function() {
                    if (editType.value === 'Eid Day') {
                        editToDate.value = this.value;
                    }
                });

                // Initial call for existing "Eid Day" records being edited
                handleEditTypeChange(editType.value);
            });
        });

        document.getElementById('all_office').addEventListener('change', function() {
            const row = document.getElementById('office_select_row');
            const select = row.querySelector('select');
            if (this.checked) {
                row.style.display = 'none';
                select.removeAttribute('required');
            } else {
                row.style.display = 'block';
                select.setAttribute('required', 'required');
            }
        });

        document.querySelectorAll('.edit-all-office').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const targetId = this.getAttribute('data-target');
                const row = document.getElementById(targetId);
                const select = row.querySelector('select');
                if (this.checked) {
                    row.style.display = 'none';
                    select.removeAttribute('required');
                } else {
                    row.style.display = 'block';
                    select.setAttribute('required', 'required');
                }
            });
        });
    </script>
    @endpush
</x-app-layout>



