<x-app-layout>
    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ __('Overtime (OT) Settings') }}</h5>
                        <p class="mb-0 small text-muted">{{ __('Configure grade-based rates, designation overrides, and eligibility.') }}</p>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 1rem;">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('overtimes.settings.save') }}" method="POST">
                @csrf
                <div class="row g-4">
                    {{-- Grade Rates Section --}}
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
                            <div class="card-header bg-white border-0 py-3" style="border-radius: 1rem 1rem 0 0;">
                                <h6 class="mb-0 fw-bold"><i class="bi bi-layers me-2 text-primary"></i>{{ __('Grade-based Rates') }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-xs">{{ __('Grade') }}</th>
                                                <th class="text-xs" style="width: 120px;">{{ __('Rate (BDT)') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($grades as $grade)
                                                <tr>
                                                    <td class="small">{{ $grade->name }}</td>
                                                    <td>
                                                        <input type="number" step="0.01" name="grade_rates[{{ $grade->id }}]" 
                                                               class="form-control form-control-sm" value="{{ $gradeRates[$grade->id] ?? '' }}" 
                                                               placeholder="0.00">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Designation Overrides Section --}}
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
                            <div class="card-header bg-white border-0 py-3" style="border-radius: 1rem 1rem 0 0;">
                                <h6 class="mb-0 fw-bold"><i class="bi bi-person-gear me-2 text-warning"></i>{{ __('Designation Overrides') }}</h6>
                                <small class="text-muted text-xs">{{ __('Specific rates for these designations.') }}</small>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive" style="max-height: 400px;">
                                    <table class="table table-sm table-hover align-middle">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th class="text-xs">{{ __('Designation') }}</th>
                                                <th class="text-xs" style="width: 120px;">{{ __('Rate (BDT)') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($designations->where('is_ot_eligible', true) as $designation)
                                                <tr>
                                                    <td class="small">{{ $designation->name }}</td>
                                                    <td>
                                                        <input type="number" step="0.01" name="designation_rates[{{ $designation->id }}]" 
                                                               class="form-control form-control-sm" value="{{ $designationRates[$designation->id] ?? '' }}" 
                                                               placeholder="Inherit">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Eligibility Section --}}
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
                            <div class="card-header bg-white border-0 py-3" style="border-radius: 1rem 1rem 0 0;">
                                <h6 class="mb-0 fw-bold"><i class="bi bi-person-check me-2 text-success"></i>{{ __('OT Eligibility') }}</h6>
                                <small class="text-muted text-xs">{{ __('Toggle who can receive overtime.') }}</small>
                            </div>
                            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                @foreach($designations as $designation)
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" name="designations[]" 
                                               value="{{ $designation->id }}" id="desig_{{ $designation->id }}"
                                               {{ $designation->is_ot_eligible ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="desig_{{ $designation->id }}">
                                            {{ $designation->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Special Eid Rates Section --}}
                    <div class="col-lg-12">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
                            <div class="card-header bg-white border-0 py-3" style="border-radius: 1rem 1rem 0 0;">
                                <h6 class="mb-0 fw-bold"><i class="bi bi-star-fill me-2 text-danger"></i>{{ __('Special Eid Rates (Roster Groups)') }}</h6>
                                <small class="text-muted text-xs">{{ __('Specific hourly fallback rates for NOC/Roster groups during Eid-adjacent days.') }}</small>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    @foreach($rosterGroups as $group)
                                        <div class="col-md-3">
                                            <label class="form-label small mb-1">{{ $group }}</label>
                                            <input type="number" step="0.01" name="special_rates[{{ $group }}]" 
                                                   class="form-control form-control-sm" value="{{ $specialRates[$group] ?? '' }}" 
                                                   placeholder="Inherit">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary px-5 py-2 shadow-sm" style="border-radius: 0.75rem; font-weight: 600;">
                        <i class="bi bi-save me-2"></i>{{ __('Save All Settings') }}
                    </button>
                </div>
            </form>
        </main>
    </div>

    <style>
        .text-xs { font-size: 0.7rem; }
        .hover-shadow-sm:hover { box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
    </style>
</x-app-layout>
