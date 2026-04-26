<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .generate-panel {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            padding: 2rem;
            margin-bottom: 2rem;
        }
    </style>
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 text-2xl font-bold">{{ __('Generate Letter') }}</h5>
                        <p class="mb-0 text-gray-500">{{ __('Select a template and fill in the dynamic tags to generate a PDF letter.') }}</p>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="generate-panel">
                        <form action="{{ route('personnel.reports.generate.preview') }}" method="POST" id="generate-form">
                            @csrf
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="report_template_type_id" class="form-label font-bold text-gray-700">{{ __('Letter Name') }}</label>
                                    <select name="report_template_type_id" id="report_template_type_id" class="form-select rounded-3" required>
                                        <option value="" disabled selected>{{ __('Select Letter Name') }}</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="format" class="form-label font-bold text-gray-700">{{ __('Format') }}</label>
                                    <select name="format" id="format" class="form-select rounded-3" required>
                                        <option value="" disabled selected>{{ __('Select Format') }}</option>
                                        @foreach($formats as $format)
                                            <option value="{{ $format }}">{{ $format }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <hr class="mb-4">

                            <div id="loading-spinner" class="text-center d-none mb-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted small mt-2">Fetching dynamic fields...</p>
                            </div>

                            <div id="error-message" class="alert alert-danger d-none mb-4"></div>

                            <div id="dynamic-fields-container" class="mb-4">
                                <!-- Dynamic fields will be injected here via JS -->
                                <p class="text-muted text-center" id="empty-state">
                                    <i class="bi bi-file-earmark-text display-4 d-block mb-2 text-light"></i>
                                    {{ __('Please select a Letter Name and Format to load the required fields.') }}
                                </p>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-success px-4 py-2 font-bold" id="generate-btn" disabled>
                                    <i class="bi bi-eye me-2"></i> {{ __('Preview Letter') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <div id="employees-data" class="d-none" data-employees="{{ json_encode($employees->map(function($emp) {
        return [
            'id' => $emp->id,
            'name' => $emp->name,
            'employee_id' => $emp->employee_code,
            'designation' => $emp->designation ? $emp->designation->name : '',
            'department' => $emp->department ? $emp->department->name : '',
            'office_name' => $emp->office ? $emp->office->name : '',
            'joining_date' => $emp->joining_date ? \Carbon\Carbon::parse($emp->joining_date)->format('Y-m-d') : '',
        ];
    })) }}"></div>

    <script>
        const employeesData = JSON.parse(document.getElementById('employees-data').dataset.employees);

        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('report_template_type_id');
            const formatSelect = document.getElementById('format');
            const fieldsContainer = document.getElementById('dynamic-fields-container');
            const emptyState = document.getElementById('empty-state');
            const spinner = document.getElementById('loading-spinner');
            const errorMessage = document.getElementById('error-message');
            const generateBtn = document.getElementById('generate-btn');

            function fetchFields() {
                const typeId = typeSelect.value;
                const format = formatSelect.value;

                if (!typeId || !format) {
                    return;
                }

                // Show loading
                emptyState.classList.add('d-none');
                fieldsContainer.innerHTML = '';
                errorMessage.classList.add('d-none');
                spinner.classList.remove('d-none');
                generateBtn.disabled = true;

                fetch(`{{ route('personnel.reports.generate.fields') }}?report_template_type_id=${typeId}&format=${format}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Template not found or not active.');
                        }
                        return response.json();
                    })
                    .then(data => {
                        spinner.classList.add('d-none');
                        generateBtn.disabled = false;
                        
                        if (data.tags && data.tags.length > 0) {
                            let html = '<h6 class="font-bold mb-3"><i class="bi bi-input-cursor-text me-2 text-success"></i>Required Information</h6>';
                            html += '<div class="row g-3">';
                            
                            data.tags.forEach(tag => {
                                // Remove # for the label
                                const labelName = tag.replace('#', '');
                                // Input name should include # or not, let's keep it without # to be standard, controller handles it
                                const inputName = `tags[${tag}]`;
                                
                                let extraClass = '';
                                if (tag === '#current_date' || tag === '#leave_start' || tag === '#leave_end' || tag === '#resignation_date' || tag === '#resignation_receive_date') {
                                    extraClass = 'date-picker-input';
                                }
                                
                                html += `
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">${labelName}</label>
                                `;
                                
                                if (tag === '#employee_name') {
                                    html += `<select name="${inputName}" class="form-select rounded-3 employee-select" data-tag="${tag}" required>
                                        <option value="" disabled selected>Select Employee</option>`;
                                    employeesData.forEach(emp => {
                                        html += `<option value="${emp.name}" data-id="${emp.id}">${emp.name} (${emp.employee_id})</option>`;
                                    });
                                    html += `</select></div>`;
                                } else {
                                    html += `<input type="text" name="${inputName}" class="form-control rounded-3 ${extraClass}" data-tag="${tag}" placeholder="Enter ${labelName}" required>
                                    </div>`;
                                }
                            });
                            
                            html += '</div>';
                            fieldsContainer.innerHTML = html;

                            // Initialize select2 for employee name
                            if ($('.employee-select').length > 0) {
                                $('.employee-select').select2({
                                    placeholder: 'Select Employee',
                                    width: '100%'
                                }).on('change', function() {
                                    const selectedId = $(this).find(':selected').data('id');
                                    const employee = employeesData.find(e => e.id === selectedId);
                                    
                                    if (employee) {
                                        // Auto-fill tags if they exist
                                        const tagsToFill = {
                                            '#employee_id': employee.employee_id,
                                            '#designation': employee.designation,
                                            '#department': employee.department,
                                            '#office_name': employee.office_name,
                                            '#joining_date': employee.joining_date,
                                        };

                                        for (const [tag, value] of Object.entries(tagsToFill)) {
                                            const input = fieldsContainer.querySelector(`[name="tags[${tag}]"]`);
                                            if (input) {
                                                input.value = value;
                                                // If it's a flatpickr, it might need to update its visual date
                                                if (input._flatpickr) {
                                                    input._flatpickr.setDate(value);
                                                }
                                            }
                                        }
                                    }
                                });
                            }

                            // Initialize date pickers
                            const dateInputs = fieldsContainer.querySelectorAll('.date-picker-input');
                            if (dateInputs.length > 0) {
                                dateInputs.forEach(input => {
                                    const tag = input.getAttribute('data-tag');
                                    let options = {
                                        dateFormat: 'Y-m-d',
                                        allowInput: false,
                                    };
                                    if (tag === '#current_date') {
                                        options.defaultDate = "today";
                                    }
                                    flatpickr(input, options);
                                });
                            }
                        } else {
                            fieldsContainer.innerHTML = `
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i> This template does not require any dynamic tags.
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        spinner.classList.add('d-none');
                        errorMessage.textContent = error.message;
                        errorMessage.classList.remove('d-none');
                        generateBtn.disabled = true;
                    });
            }

            typeSelect.addEventListener('change', fetchFields);
            formatSelect.addEventListener('change', fetchFields);
        });
    </script>
    @endpush
</x-app-layout>
