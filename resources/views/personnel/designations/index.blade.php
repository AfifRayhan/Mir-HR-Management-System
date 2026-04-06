<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ __('Designation Management') }}</h5>
                        <p class="mb-0 small text-muted">
                            {{ __('Welcome,') }}
                            {{ $employee ? $employee->name : ($user->name ?? __('HR Administrator')) }}
                            • {{ $roleName }}
                        </p>
                    </div>
                    <div class="text-end text-sm text-gray-500">
                        <i class="bi bi-calendar-event me-2 text-success"></i>{{ now()->format('l, d M Y') }}
                    </div>
                </div>
            </div>


            <div class="row g-4">
                <!-- New Designation Form -->
                <div class="col-lg-4">
                    <div class="hr-panel">
                        <div class="hr-panel-title">
                            <i class="bi bi-plus-circle me-2 text-success"></i>{{ __('Add New Designation') }}
                        </div>

                        <form action="{{ route('personnel.designations.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="insert_mode" id="insert_mode" value="0">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Designation Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control rounded-3" placeholder="{{ __('e.g. Senior Software Engineer') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Short Name') }}</label>
                                <input type="text" name="short_name" class="form-control rounded-3" placeholder="{{ __('e.g. Sr. SE') }}">
                            </div>

                            <div class="mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="form-label small fw-bold text-muted mb-0">{{ __('Priority Order') }}</label>
                                    <button type="button" id="toggleModeBtn" class="mode-toggle-btn">
                                        Switch to: Insert Mode
                                    </button>
                                </div>
                                <input type="number" name="priority" class="form-control rounded-3" value="0" min="1">
                                <div id="normal_hint" class="form-text small">{{ __('Lower number (e.g. 1) = Highest priority.') }}</div>
                                <div id="insert_hint" class="mt-2 d-none form-text small text-success" style="background:var(--hr-success-soft); padding:8px; border-radius:6px; border-left:3px solid var(--hr-success);">
                                    <strong>Insert at Priority mode:</strong><br> Existing designations at this and lower ranks will be shifted down by +1 to make room.
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success w-100 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-plus-circle me-2"></i>{{ __('Save Designation') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Designation List -->
                <div class="col-lg-8">
                    <div class="hr-panel">
                        <div class="hr-panel-title mb-4">
                            <i class="bi bi-list-task me-2 text-success"></i>{{ __('Designation List') }}
                        </div>

                        <div class="table-responsive">
                            <table class="hr-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th>{{ __('Designation Info') }}</th>
                                        <th>{{ __('Short Name') }}</th>
                                        <th>{{ __('Priority') }}</th>
                                        <th class="text-end pe-4">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($designations as $designation)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-success">{{ $designation->name }}</div>
                                        </td>
                                        <td><span class="hr-badge hr-badge-global">{{ $designation->short_name ?: '---' }}</span></td>
                                        <td>
                                            <span class="badge bg-secondary rounded-pill px-3">{{ $designation->priority }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            @php $confirmMsg = __('Are you sure?'); @endphp
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-success border-0" title="{{ __('Edit') }}" data-bs-toggle="modal" data-bs-target="#editModal{{ $designation->id }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <form action="{{ route('personnel.designations.destroy', $designation) }}" method="POST" data-confirm data-confirm-message="{{ $confirmMsg }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="{{ __('Delete') }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal{{ $designation->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-success">{{ __('Edit Designation') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('personnel.designations.update', $designation) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body py-4">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Designation Name') }}</label>
                                                                <input type="text" name="name" class="form-control rounded-3" value="{{ $designation->name }}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Short Name') }}</label>
                                                                <input type="text" name="short_name" class="form-control rounded-3" value="{{ $designation->short_name }}">
                                                            </div>
                                                            <div class="col-md-12">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Priority Order') }}</label>
                                                                <input type="number" name="priority" class="form-control rounded-3" value="{{ $designation->priority }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                        <button type="submit" class="btn btn-success rounded-pill px-4">{{ __('Update Designation') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-award d-block mb-3 fs-1 opacity-50"></i>
                                                {{ __('No designations found.') }}
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

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn        = document.getElementById('toggleModeBtn');
            const modeInput  = document.getElementById('insert_mode');
            const insertHint = document.getElementById('insert_hint');
            const normalHint = document.getElementById('normal_hint');

            if (btn) {
                btn.addEventListener('click', function () {
                    const isInsert = modeInput.value === '1';

                    if (isInsert) {
                        modeInput.value = '0';
                        btn.textContent = 'Switch to: Insert Mode';
                        btn.classList.remove('active-insert');
                        insertHint.classList.add('d-none');
                        normalHint.classList.remove('d-none');
                    } else {
                        modeInput.value = '1';
                        btn.textContent = 'Switch to: Priority Order';
                        btn.classList.add('active-insert');
                        insertHint.classList.remove('d-none');
                        normalHint.classList.add('d-none');
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>