<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Notice/Event') }}
        </h2>
    </x-slot>

    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <div class="row mb-4">
                <div class="col-12">
                    <a href="{{ route('settings.notices.index') }}" class="btn btn-link text-muted p-0 mb-2 text-decoration-none small">
                        <i class="bi bi-arrow-left me-1"></i>{{ __('Back to List') }}
                    </a>
                    <h4 class="fw-bold mb-1">{{ __('Edit Notice/Event') }}</h4>
                    <p class="text-muted mb-0 small">{{ __('Update announcement or event details') }}</p>
                </div>
            </div>

            <div class="hr-panel shadow-sm">
                <form action="{{ route('settings.notices.update', $notice) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-4">
                        <div class="col-md-8">
                            <div class="mb-4">
                                <label class="form-label font-bold text-gray-700">{{ __('Title') }}</label>
                                <input type="text" name="title" class="form-control rounded-3 @error('title') is-invalid @enderror" value="{{ old('title', $notice->title) }}" placeholder="Enter title" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label font-bold text-gray-700">{{ __('Content') }}</label>
                                <textarea name="content" rows="6" class="form-control rounded-3 @error('content') is-invalid @enderror" placeholder="Write notice content here..." required>{{ old('content', $notice->content) }}</textarea>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-4">
                                <label class="form-label font-bold text-gray-700">{{ __('Type') }}</label>
                                <select name="type" class="form-select rounded-3 @error('type') is-invalid @enderror" required>
                                    <option value="notice" {{ old('type', $notice->type) === 'notice' ? 'selected' : '' }}>{{ __('Notice') }}</option>
                                    <option value="event" {{ old('type', $notice->type) === 'event' ? 'selected' : '' }}>{{ __('Event') }}</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label font-bold text-gray-700">{{ __('Expiry Date') }}</label>
                                <input type="text" id="expires_at" name="expires_at" class="form-control rounded-3 @error('expires_at') is-invalid @enderror" value="{{ old('expires_at', $notice->expires_at ? $notice->expires_at->format('Y-m-d') : '') }}" placeholder="Select expiry date (optional)">
                                <div class="form-text small">{{ __('Leave blank for no expiry.') }}</div>
                                @error('expires_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <div class="form-check form-switch p-3 bg-light rounded-3">
                                    <input class="form-check-input ms-0 me-3" type="checkbox" name="is_active" role="switch" id="isActiveSwitch" {{ old('is_active', $notice->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label font-bold text-gray-700" for="isActiveSwitch">{{ __('Active') }}</label>
                                </div>
                                <div class="form-text small">{{ __('Inactive notices will not be visible to employees.') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top">
                        <a href="{{ route('settings.notices.index') }}" class="btn btn-light rounded-pill px-4">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">{{ __('Update Notice') }}</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('#expires_at', {
                dateFormat: 'Y-m-d',
                allowInput: false,
            });
        });
    </script>
    @endpush
</x-app-layout>
