<x-app-layout>
    <div class="hr-layout">
        @include('partials.team-lead-sidebar')

        <main class="hr-main">
            <div class="row mb-4 align-items-center">
                <div class="col-md-6">
                    <h4 class="fw-bold mb-1">{{ __('Send New Remark') }}</h4>
                    <p class="text-muted mb-0">{{ __('Message your direct reports') }}</p>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('team-lead.remarks.index') }}" class="btn btn-light rounded-pill px-4 border shadow-sm small">
                        <i class="bi bi-arrow-left me-2"></i>{{ __('Back to List') }}
                    </a>
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

            <form action="{{ route('team-lead.remarks.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-lg-8">
                        <div class="hr-panel mb-4">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted uppercase tracking-wider">{{ __('Message Title') }} <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control rounded-3" 
                                       placeholder="{{ __('e.g., Performance Review, Urgent Update...') }}" required 
                                       value="{{ old('title') }}">
                                <div class="form-text small">{{ __('This title will be displayed directly on the employee dashboard.') }}</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted uppercase tracking-wider">{{ __('Message Content') }} <span class="text-danger">*</span></label>
                                <textarea name="message" class="form-control rounded-3" rows="6" 
                                          placeholder="{{ __('Write the full message details here...') }}" required>{{ old('message') }}</textarea>
                                <div class="form-text small">{{ __('Employees will see this full message in a popup when they click the title.') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="hr-panel mb-4">
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted uppercase tracking-wider mb-2 d-block">{{ __('Select Recipients') }} <span class="text-danger">*</span></label>
                                <div class="bg-light p-3 rounded-3 border mb-2" style="max-height: 250px; overflow-y: auto;">
                                    @forelse($directReports as $employee)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="employee_ids[]" 
                                               value="{{ $employee->id }}" id="emp_{{ $employee->id }}"
                                               {{ is_array(old('employee_ids')) && in_array($employee->id, old('employee_ids')) ? 'checked' : '' }}>
                                        <label class="form-check-label small d-flex flex-column" for="emp_{{ $employee->id }}">
                                            <span class="fw-bold">{{ $employee->name }}</span>
                                            <span class="text-muted" style="font-size: 0.7rem;">ID: {{ $employee->employee_code }}</span>
                                        </label>
                                    </div>
                                    @empty
                                    <div class="text-center py-3">
                                        <p class="text-muted small mb-0">{{ __('No direct reports found.') }}</p>
                                    </div>
                                    @endforelse
                                </div>
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1" id="selectAll" style="font-size: 0.75rem;">
                                        <i class="bi bi-check-all me-1"></i>{{ __('Select All') }}
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1" id="deselectAll" style="font-size: 0.75rem;">
                                        <i class="bi bi-x-circle me-1"></i>{{ __('Deselect All') }}
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted uppercase tracking-wider">{{ __('Expiry Date (Optional)') }}</label>
                                <input type="datetime-local" name="expires_at" class="form-control rounded-3" 
                                       value="{{ old('expires_at', $defaultExpiry) }}">
                                <div class="form-text small">{{ __('Message will disappear from the dashboard after this date (Default: 1 month).') }}</div>
                            </div>

                            <button type="submit" class="btn btn-success w-100 py-3 rounded-pill shadow-sm fw-bold">
                                <i class="bi bi-send-fill me-2 rotate-45"></i>{{ __('Send Remark') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAll');
            const deselectAll = document.getElementById('deselectAll');
            const checkboxes = document.querySelectorAll('input[name="employee_ids[]"]');

            if (selectAll) {
                selectAll.addEventListener('click', () => {
                    checkboxes.forEach(cb => cb.checked = true);
                });
            }

            if (deselectAll) {
                deselectAll.addEventListener('click', () => {
                    checkboxes.forEach(cb => cb.checked = false);
                });
            }
        });
    </script>
    @endpush

    @push('styles')
    <style>
        .btn:focus, .btn:active, .form-check-input:focus, .form-control:focus, textarea:focus, input:focus {
            box-shadow: none !important;
            outline: none !important;
            border-color: #dee2e6 !important;
        }
        .form-check-input:checked {
            background-color: #275e26 !important;
            border-color: #275e26 !important;
        }
        .rotate-45 { transform: rotate(45deg); display: inline-block; }
    </style>
    @endpush
</x-app-layout>
