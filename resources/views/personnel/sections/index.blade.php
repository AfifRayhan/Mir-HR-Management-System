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
                        <h5 class="mb-1">{{ __('Section Management') }}</h5>
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
                <!-- New Section Form -->
                <div class="col-lg-4">
                    <div class="hr-panel">
                        <div class="hr-panel-title">
                            <i class="bi bi-plus-circle me-2 text-success"></i>{{ __('Add New Section') }}
                        </div>

                        <form action="{{ route('personnel.sections.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Department') }} <span class="text-danger">*</span></label>
                                <select name="department_id" class="form-select rounded-3" required>
                                    <option value="">{{ __('Select Department') }}</option>
                                    @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Section Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control rounded-3" placeholder="{{ __('e.g. Software Development') }}" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">{{ __('Description') }}</label>
                                <textarea name="description" class="form-control rounded-3" rows="3" placeholder="{{ __('Brief description...') }}"></textarea>
                            </div>

                            <button type="submit" class="btn btn-success w-100 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-plus-circle me-2"></i>{{ __('Save Section') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Section List -->
                <div class="col-lg-8">
                    <div class="hr-panel">
                        <div class="hr-panel-title mb-4">
                            <i class="bi bi-list-task me-2 text-success"></i>{{ __('Section List') }}
                        </div>

                        <!-- Filter & Search Form -->
                        <form action="{{ route('personnel.sections.index') }}" method="GET" class="mb-4">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control rounded-pill" placeholder="{{ __('Search sections...') }}" value="{{ request('search') }}">
                                </div>
                                <div class="col-md-4">
                                    <select name="department_id" class="form-select rounded-pill">
                                        <option value="">{{ __('All Departments') }}</option>
                                        @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-hr-search flex-grow-1">{{ __('Search') }}</button>
                                    <a href="{{ route('personnel.sections.index') }}" class="btn btn-hr-clear flex-grow-1">{{ __('Clear') }}</a>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="hr-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4" style="width: 50px;">#</th>
                                        <th>
                                            <a href="{{ route('personnel.sections.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark d-inline-flex align-items-center">
                                                {{ __('Section Name') }}
                                                @if(request('sort') == 'name')
                                                    <i class="bi bi-sort-alpha-{{ request('direction') == 'asc' ? 'down' : 'up' }} ms-1"></i>
                                                @else
                                                    <i class="bi bi-arrow-down-up ms-1 text-muted" style="font-size: 0.8rem; opacity: 0.5;"></i>
                                                @endif
                                            </a>
                                        </th>
                                        <th>{{ __('Department') }}</th>
                                        <th class="text-end pe-4">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sections as $section)
                                    <tr>
                                        <td class="ps-4 text-muted fw-bold">{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="fw-bold text-success">{{ $section->name }}</div>
                                            @if($section->description)
                                            <div class="small text-muted text-truncate" style="max-width: 250px;" title="{{ $section->description }}">
                                                {{ $section->description }}
                                            </div>
                                            @endif
                                        </td>
                                        <td><span class="hr-badge hr-badge-global">{{ $section->department->name }}</span></td>
                                        <td class="text-end pe-4">
                                            @php $confirmMsg = __('Are you sure?'); @endphp
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-success border-0" title="{{ __('Edit') }}" data-bs-toggle="modal" data-bs-target="#editModal{{ $section->id }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <form action="{{ route('personnel.sections.destroy', $section) }}" method="POST" data-confirm data-confirm-message="{{ $confirmMsg }}">
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
                                    <div class="modal fade" id="editModal{{ $section->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-success">{{ __('Edit Section') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('personnel.sections.update', $section) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body py-4">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Department') }}</label>
                                                                <select name="department_id" class="form-select rounded-3" required>
                                                                    @foreach($departments as $dept)
                                                                    <option value="{{ $dept->id }}" {{ $section->department_id == $dept->id ? 'selected' : '' }}>
                                                                        {{ $dept->name }}
                                                                    </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Section Name') }}</label>
                                                                <input type="text" name="name" class="form-control rounded-3" value="{{ $section->name }}" required>
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Description') }}</label>
                                                                <textarea name="description" class="form-control rounded-3" rows="3">{{ $section->description }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                        <button type="submit" class="btn btn-success rounded-pill px-4">{{ __('Update Section') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-layers d-block mb-3 fs-1 opacity-50"></i>
                                                {{ __('No sections found.') }}
                                                @if(request('search') || request('department_id'))
                                                    <div class="mt-2 text-sm">
                                                        <a href="{{ route('personnel.sections.index') }}" class="text-decoration-none">{{ __('Clear Filters') }}</a>
                                                    </div>
                                                @endif
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
</x-app-layout>