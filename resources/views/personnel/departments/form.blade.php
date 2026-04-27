<x-app-layout>
    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('personnel.departments.index') }}">Departments</a></li>
                                <li class="breadcrumb-item active">{{ isset($department) ? 'Edit' : 'Create' }} Department</li>
                            </ol>
                        </nav>
                        <h1 class="h3 mb-0 text-gray-800">{{ isset($department) ? 'Edit' : 'Create' }} Department</h1>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 col-lg-6">
                        <div class="ui-panel">
                            <form action="{{ isset($department) ? route('personnel.departments.update', $department) : route('personnel.departments.store') }}" method="POST">
                                @csrf
                                @if(isset($department))
                                @method('PUT')
                                @endif

                                <div class="mb-3">
                                    <label for="name" class="form-label">Department Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $department->name ?? '') }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="short_name" class="form-label">Short Name</label>
                                    <input type="text" class="form-control @error('short_name') is-invalid @enderror" id="short_name" name="short_name" value="{{ old('short_name', $department->short_name ?? '') }}">
                                    @error('short_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="incharge_id" class="form-label">In-charge</label>
                                    <select class="form-select @error('incharge_id') is-invalid @enderror" id="incharge_id" name="incharge_id">
                                        <option value="">Select Employee</option>
                                        @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ old('incharge_id', $department->incharge_id ?? '') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('incharge_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $department->description ?? '') }}</textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success">Save Department</button>
                                    <a href="{{ route('personnel.departments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>



