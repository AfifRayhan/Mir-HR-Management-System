<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Notices & Events') }}
        </h2>
    </x-slot>

    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1">{{ __('Notices & Events') }}</h4>
                        <p class="text-muted mb-0 small">{{ __('Manage announcements and upcoming events') }}</p>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Create Form -->
                <div class="col-lg-4">
                    <div class="hr-panel">
                        <div class="hr-panel-title mb-4">
                            <i class="bi bi-plus-circle me-2 text-primary"></i>{{ __('Add Notice/Event') }}
                        </div>

                        <form action="{{ route('settings.notices.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Title') }} <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control rounded-3 @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Enter title" required>
                                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Content') }} <span class="text-danger">*</span></label>
                                <textarea name="content" rows="4" class="form-control rounded-3 @error('content') is-invalid @enderror" placeholder="Write notice content here..." required>{{ old('content') }}</textarea>
                                @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Type') }} <span class="text-danger">*</span></label>
                                <select name="type" class="form-select rounded-3 @error('type') is-invalid @enderror" required>
                                    <option value="notice" {{ old('type') === 'notice' ? 'selected' : '' }}>{{ __('Notice') }}</option>
                                    <option value="event" {{ old('type') === 'event' ? 'selected' : '' }}>{{ __('Event') }}</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Expiry Date') }}</label>
                                <input type="text" name="expires_at" class="form-control rounded-3 datepicker @error('expires_at') is-invalid @enderror" value="{{ old('expires_at') }}" placeholder="Select expiry date (optional)">
                                <div class="form-text small text-muted">{{ __('Leave blank for no expiry.') }}</div>
                            </div>

                            <div class="mb-4">
                                <div class="form-check form-switch p-2 bg-light rounded-3 px-3">
                                    <input class="form-check-input ms-0 me-3" type="checkbox" name="is_active" role="switch" id="isActiveSwitchDefault" checked>
                                    <label class="form-check-label small fw-bold text-muted" for="isActiveSwitchDefault">{{ __('Active Status') }}</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-megaphone me-2"></i>{{ __('Publish Notice') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Table List -->
                <div class="col-lg-8">
                    <div class="hr-panel p-0 overflow-hidden">
                        <div class="hr-panel-title p-4 border-bottom">
                            <i class="bi bi-list-task me-2 text-primary"></i>{{ __('Notice List') }}
                        </div>
                        <div class="table-responsive">
                            <table class="table hr-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">{{ __('Title') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Expiry') }}</th>
                                        <th class="pe-4 text-end">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($notices as $notice)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold text-gray-800">{{ $notice->title }}</div>
                                                <div class="text-muted small text-truncate" style="max-width: 200px;">{{ Str::limit($notice->content, 50) }}</div>
                                            </td>
                                            <td>
                                                @if($notice->type === 'notice')
                                                    <span class="badge bg-info-soft text-info">{{ __('Notice') }}</span>
                                                @else
                                                    <span class="badge bg-primary-soft text-primary" style="background-color: #e0e7ff; color: #4338ca;">{{ __('Event') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($notice->is_active)
                                                    <span class="badge bg-success-soft text-success">{{ __('Active') }}</span>
                                                @else
                                                    <span class="badge bg-secondary text-white">{{ __('Inactive') }}</span>
                                                @endif
                                            </td>
                                            <td class="small">
                                                {{ $notice->expires_at ? $notice->expires_at->format('d M Y') : __('No Expiry') }}
                                            </td>
                                            <td class="pe-4 text-end">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary border-0" data-bs-toggle="modal" data-bs-target="#editNoticeModal{{ $notice->id }}" title="{{ __('Edit') }}">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <form action="{{ route('settings.notices.destroy', $notice) }}" method="POST" data-confirm data-confirm-message="{{ __('Are you sure you want to delete this notice?') }}">
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
                                        <div class="modal fade" id="editNoticeModal{{ $notice->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content rounded-4 border-0 shadow">
                                                    <div class="modal-header border-0 pb-0 px-4 pt-4">
                                                        <h5 class="modal-title fw-bold text-primary">{{ __('Edit Notice/Event') }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('settings.notices.update', $notice) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body p-4">
                                                            <div class="row g-3">
                                                                <div class="col-md-8">
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-bold text-muted">{{ __('Title') }}</label>
                                                                        <input type="text" name="title" class="form-control rounded-3" value="{{ old('title', $notice->title) }}" required>
                                                                    </div>
                                                                    <div class="mb-0">
                                                                        <label class="form-label small fw-bold text-muted">{{ __('Content') }}</label>
                                                                        <textarea name="content" rows="6" class="form-control rounded-3" required>{{ old('content', $notice->content) }}</textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-bold text-muted">{{ __('Type') }}</label>
                                                                        <select name="type" class="form-select rounded-3" required>
                                                                            <option value="notice" {{ old('type', $notice->type) === 'notice' ? 'selected' : '' }}>{{ __('Notice') }}</option>
                                                                            <option value="event" {{ old('type', $notice->type) === 'event' ? 'selected' : '' }}>{{ __('Event') }}</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-bold text-muted">{{ __('Expiry Date') }}</label>
                                                                        <input type="text" name="expires_at" class="form-control rounded-3 datepicker" value="{{ old('expires_at', $notice->expires_at ? $notice->expires_at->format('Y-m-d') : '') }}">
                                                                    </div>
                                                                    <div class="form-check form-switch p-3 bg-light rounded-3 ms-2">
                                                                        <input class="form-check-input ms-0 me-3" type="checkbox" name="is_active" role="switch" id="isActiveSwitch{{ $notice->id }}" {{ $notice->is_active ? 'checked' : '' }}>
                                                                        <label class="form-check-label small fw-bold text-muted" for="isActiveSwitch{{ $notice->id }}">{{ __('Active') }}</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-0 p-4 pt-0">
                                                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                            <button type="submit" class="btn btn-primary rounded-pill px-4">{{ __('Update Notice') }}</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="bi bi-megaphone fs-1 d-block mb-2 opacity-25"></i>
                                                {{ __('No notices or events found.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($notices->hasPages())
                            <div class="px-4 py-3 border-top bg-light">
                                {{ $notices->links('pagination::bootstrap-5') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('.datepicker', {
                dateFormat: 'Y-m-d',
                allowInput: false,
                allowClear: true,
            });
        });
    </script>
    @endpush
</x-app-layout>
