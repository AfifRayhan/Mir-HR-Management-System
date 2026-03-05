<x-app-layout>
    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('personnel.office-times.index') }}">Office Times</a></li>
                                <li class="breadcrumb-item active">{{ isset($officeTime) ? 'Edit' : 'Create' }} Shift</li>
                            </ol>
                        </nav>
                        <h1 class="h3 mb-0 text-gray-800">{{ isset($officeTime) ? 'Edit' : 'Create' }} Shift</h1>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-10">
                        <div class="hr-panel">
                            <form action="{{ isset($officeTime) ? route('personnel.office-times.update', $officeTime) : route('personnel.office-times.store') }}" method="POST">
                                @csrf
                                @if(isset($officeTime))
                                @method('PUT')
                                @endif

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="shift_name" class="form-label">Shift Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('shift_name') is-invalid @enderror" id="shift_name" name="shift_name" value="{{ old('shift_name', $officeTime->shift_name ?? '') }}" required>
                                        @error('shift_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control @error('start_time') is-invalid @enderror" id="start_time" name="start_time" value="{{ old('start_time', $officeTime->start_time ?? '') }}" required>
                                        @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="end_time" class="form-label">End Time <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control @error('end_time') is-invalid @enderror" id="end_time" name="end_time" value="{{ old('end_time', $officeTime->end_time ?? '') }}" required>
                                        @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="late_after" class="form-label">Late After</label>
                                        <input type="time" class="form-control @error('late_after') is-invalid @enderror" id="late_after" name="late_after" value="{{ old('late_after', $officeTime->late_after ?? '') }}">
                                        @error('late_after')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="absent_after" class="form-label">Absent After</label>
                                        <input type="time" class="form-control @error('absent_after') is-invalid @enderror" id="absent_after" name="absent_after" value="{{ old('absent_after', $officeTime->absent_after ?? '') }}">
                                        @error('absent_after')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="lunch_start" class="form-label">Lunch Start</label>
                                        <input type="time" class="form-control @error('lunch_start') is-invalid @enderror" id="lunch_start" name="lunch_start" value="{{ old('lunch_start', $officeTime->lunch_start ?? '') }}">
                                        @error('lunch_start')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="lunch_end" class="form-label">Lunch End</label>
                                        <input type="time" class="form-control @error('lunch_end') is-invalid @enderror" id="lunch_end" name="lunch_end" value="{{ old('lunch_end', $officeTime->lunch_end ?? '') }}">
                                        @error('lunch_end')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Save Shift</button>
                                    <a href="{{ route('personnel.office-times.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>