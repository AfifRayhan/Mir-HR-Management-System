<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css', 'resources/css/custom-holidays.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .badge-a { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .badge-b { background-color: #cfe2ff; color: #084298; border: 1px solid #b6d4fe; }
        .badge-c { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        .badge-g { background-color: #fff3cd; color: #664d03; border: 1px solid #ffecb5; }
        .badge-off { background-color: #e2e3e5; color: #41464b; border: 1px solid #d3d6d8; }
        
        .group-pill {
            transition: all 0.2s ease;
            color: #64748b;
            background: #fff;
            border: 1px solid #e2e8f0;
        }
        .group-pill:hover {
            background: #f8fafc;
            color: #007a10;
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }
        .group-pill.active {
            background: #007a10;
            color: #fff;
            border-color: #007a10;
            box-shadow: 0 4px 6px -1px rgba(0, 122, 16, 0.2);
        }
    </style>
    @endpush

    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">{{ __('Roster Shift Management') }}</h5>
                        <p class="mb-0 small text-muted">
                            {{ __('Define and manage dynamic shift timings for different roster groups.') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Add New Roster Shift Panel -->
                <div class="col-lg-4">
                    <div class="ui-panel">
                        <div class="ui-panel-title mb-4">
                            <i class="bi bi-plus-circle me-2 text-primary"></i>{{ __('Add New Roster Shift') }}
                        </div>

                        <form action="{{ route('roster.times.store') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted">{{ __('Roster Group') }} <span class="text-danger">*</span></label>
                                    <select name="group_slug" class="form-select rounded-3" required>
                                        <option value="">Select Group</option>
                                        @foreach($groups as $slug => $label)
                                            <option value="{{ $slug }}" {{ old('group_slug', $selectedGroup) == $slug ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted">{{ __('Shift Identifier (Internal Key)') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="shift_key" class="form-control rounded-3" placeholder="e.g. Technician A, X, or A" value="{{ old('shift_key') }}" required>
                                    <div class="form-text small">This is the code used in the system backend.</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted">{{ __('Display Label') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="display_label" class="form-control rounded-3" placeholder="e.g. Shift A" value="{{ old('display_label') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('Start Time') }}</label>
                                    <input type="text" name="start_time" class="form-control rounded-3 time-picker" value="{{ old('start_time') }}" placeholder="09:00">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('End Time') }}</label>
                                    <input type="text" name="end_time" class="form-control rounded-3 time-picker" value="{{ old('end_time') }}" placeholder="17:00">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('Badge Style') }} <span class="text-danger">*</span></label>
                                    <select name="badge_class" class="form-select rounded-3" required>
                                        <option value="badge-a" class="badge-a">Style A (Green)</option>
                                        <option value="badge-b" class="badge-b">Style B (Blue)</option>
                                        <option value="badge-c" class="badge-c">Style C (Red)</option>
                                        <option value="badge-g" class="badge-g">Style G (Yellow)</option>
                                        <option value="badge-off" class="badge-off">Style Off (Grey)</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('Is Off Day?') }}</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_off_day" value="1" id="isOffDay" {{ old('is_off_day') ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="isOffDay">Yes</label>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill shadow-sm mt-4">
                                <i class="bi bi-save me-2"></i> {{ __('Save Roster Shift') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Roster Shift List -->
                <div class="col-lg-8">
                    <div class="ui-panel p-0 overflow-hidden">
                        <div class="ui-panel-title p-4 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-clock-history me-2 text-primary"></i>Configured Shifts: <span class="text-primary">{{ $groups[$selectedGroup] ?? 'Unknown' }}</span>
                            </div>
                        </div>

                        <!-- Group Selector Tabs -->
                        <div class="p-3 bg-light border-bottom overflow-auto">
                            <div class="d-flex gap-2 flex-nowrap">
                                @foreach($groups as $slug => $label)
                                    <a href="{{ route('roster.times.index', ['group' => $slug]) }}" 
                                       class="btn btn-sm rounded-pill px-3 shadow-sm group-pill {{ $selectedGroup === $slug ? 'active' : '' }}">
                                        {{ $label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table ui-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">{{ __('Shift / Key') }}</th>
                                        <th>{{ __('Display Info') }}</th>
                                        <th class="text-end pe-4">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rosterTimes as $rt)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-primary">{{ $rt->display_label }}</div>
                                            <div class="small text-muted">Key: {{ $rt->shift_key }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="badge {{ $rt->badge_class }} rounded-pill px-2 py-1 small">
                                                    {{ $rt->start_time ? \Carbon\Carbon::parse($rt->start_time)->format('h:i A') : '--' }}
                                                    - 
                                                    {{ $rt->end_time ? \Carbon\Carbon::parse($rt->end_time)->format('h:i A') : '--' }}
                                                </span>
                                            </div>
                                            @if($rt->is_off_day)
                                                <span class="badge bg-danger-soft text-danger rounded-pill x-small" style="font-size: 0.65rem;">OFF DAY</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-primary border-0" title="{{ __('Edit') }}" data-bs-toggle="modal" data-bs-target="#editRtModal{{ $rt->id }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <form action="{{ route('roster.times.destroy', $rt) }}" method="POST" data-confirm="true" data-confirm-message="Are you sure you want to delete this shift?">
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
                                    <div class="modal fade" id="editRtModal{{ $rt->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-md">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-0 pb-0 px-4 pt-4">
                                                    <h5 class="modal-title fw-bold text-primary">{{ __('Edit Roster Shift') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('roster.times.update', $rt) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body p-4">
                                                        <div class="row g-3">
                                                            <div class="col-12">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Roster Group') }} <span class="text-danger">*</span></label>
                                                                <select name="group_slug" class="form-select rounded-3" required>
                                                                    @foreach($groups as $slug => $label)
                                                                        <option value="{{ $slug }}" {{ $rt->group_slug == $slug ? 'selected' : '' }}>{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Shift Identifier (Internal Key)') }} <span class="text-danger">*</span></label>
                                                                <input type="text" name="shift_key" class="form-control rounded-3" value="{{ $rt->shift_key }}" required>
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Display Label') }} <span class="text-danger">*</span></label>
                                                                <input type="text" name="display_label" class="form-control rounded-3" value="{{ $rt->display_label }}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Start Time') }}</label>
                                                                <input type="text" name="start_time" class="form-control rounded-3 time-picker" value="{{ $rt->start_time ? \Carbon\Carbon::parse($rt->start_time)->format('H:i') : '' }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('End Time') }}</label>
                                                                <input type="text" name="end_time" class="form-control rounded-3 time-picker" value="{{ $rt->end_time ? \Carbon\Carbon::parse($rt->end_time)->format('H:i') : '' }}">
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Badge Style') }} <span class="text-danger">*</span></label>
                                                                <select name="badge_class" class="form-select rounded-3" required>
                                                                    <option value="badge-a" class="badge-a" {{ $rt->badge_class == 'badge-a' ? 'selected' : '' }}>Style A (Green)</option>
                                                                    <option value="badge-b" class="badge-b" {{ $rt->badge_class == 'badge-b' ? 'selected' : '' }}>Style B (Blue)</option>
                                                                    <option value="badge-c" class="badge-c" {{ $rt->badge_class == 'badge-c' ? 'selected' : '' }}>Style C (Red)</option>
                                                                    <option value="badge-g" class="badge-g" {{ $rt->badge_class == 'badge-g' ? 'selected' : '' }}>Style G (Yellow)</option>
                                                                    <option value="badge-off" class="badge-off" {{ $rt->badge_class == 'badge-off' ? 'selected' : '' }}>Style Off (Grey)</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Is Off Day?') }}</label>
                                                                <div class="form-check form-switch mt-2">
                                                                    <input type="hidden" name="is_off_day" value="0">
                                                                    <input class="form-check-input" type="checkbox" name="is_off_day" value="1" id="isOffDay{{ $rt->id }}" {{ $rt->is_off_day ? 'checked' : '' }}>
                                                                    <label class="form-check-label small" for="isOffDay{{ $rt->id }}">Yes</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 p-4 pt-0">
                                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                        <button type="submit" class="btn btn-primary rounded-pill px-4">{{ __('Update Shift') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-calendar-x d-block mb-3 fs-1 opacity-50"></i>
                                                {{ __('No roster shifts found.') }}
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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('.time-picker', {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                altInput: true,
                altFormat: 'h:i K',
            });
        });
    </script>
    @endpush
</x-app-layout>




