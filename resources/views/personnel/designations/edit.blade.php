<x-app-layout>
    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('personnel.designations.index') }}">Designations</a></li>
                                <li class="breadcrumb-item active">{{ isset($designation) ? 'Edit' : 'Create' }} Designation</li>
                            </ol>
                        </nav>
                        <h1 class="h3 mb-0 text-gray-800">{{ isset($designation) ? 'Edit' : 'Create' }} Designation</h1>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 col-lg-6">
                        <div class="ui-panel">
                            <form action="{{ isset($designation) ? route('personnel.designations.update', $designation) : route('personnel.designations.store') }}" method="POST">
                                @csrf
                                @if(isset($designation))
                                @method('PUT')
                                @endif

                                <div class="mb-3">
                                    <label for="name" class="form-label">Designation Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $designation->name ?? '') }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="short_name" class="form-label">Short Name</label>
                                    <input type="text" class="form-control @error('short_name') is-invalid @enderror" id="short_name" name="short_name" value="{{ old('short_name', $designation->short_name ?? '') }}">
                                    @error('short_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority</label>
                                    <input type="number" class="form-control @error('priority') is-invalid @enderror" id="priority" name="priority" value="{{ old('priority', $designation->priority ?? 0) }}">
                                    @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success">Save Designation</button>
                                    <a href="{{ route('personnel.designations.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>



