<x-app-layout>
    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('personnel.grades.index') }}">Grades</a></li>
                                <li class="breadcrumb-item active">{{ isset($grade) ? 'Edit' : 'Create' }} Grade</li>
                            </ol>
                        </nav>
                        <h1 class="h3 mb-0 text-gray-800">{{ isset($grade) ? 'Edit' : 'Create' }} Grade</h1>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 col-lg-6">
                        <div class="hr-panel">
                            <form action="{{ isset($grade) ? route('personnel.grades.update', $grade) : route('personnel.grades.store') }}" method="POST">
                                @csrf
                                @if(isset($grade))
                                @method('PUT')
                                @endif

                                <div class="mb-3">
                                    <label for="name" class="form-label">Grade Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $grade->name ?? '') }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Save Grade</button>
                                    <a href="{{ route('personnel.grades.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>