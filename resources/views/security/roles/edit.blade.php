<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <div class="row mb-3">
                <div class="col-12">
                    <h5 class="mb-1">{{ __('Edit Role') }}</h5>
                    <p class="mb-0 small text-muted">{{ __('Update role') }} <strong>{{ $role->name }}</strong></p>
                </div>
            </div>

            <div class="hr-panel hr-form-container">
                <form action="{{ route('security.roles.update', $role) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">{{ __('Role Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $role->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">{{ __('Description') }}</label>
                        <textarea name="description" id="description" rows="2"
                            class="form-control @error('description') is-invalid @enderror">{{ old('description', $role->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> {{ __('Update Role') }}
                        </button>
                        <a href="{{ route('security.roles.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</x-app-layout>