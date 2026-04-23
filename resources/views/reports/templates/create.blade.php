<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    <style>
        .ck-editor__editable_inline {
            min-height: 500px;
        }
        .tag-panel {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
        }
        .tag-btn {
            font-size: 0.75rem;
            margin: 0.2rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .tag-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(255, 255, 255, 1);
        }
    </style>
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ route('personnel.report-templates.index') }}" class="text-secondary text-decoration-none mb-2 d-inline-block small hover-opacity">
                            <i class="bi bi-arrow-left"></i> {{ __('Back to Templates') }}
                        </a>
                        <h5 class="mb-1 text-2xl font-bold">{{ __('Add New Report Template') }}</h5>
                        <p class="mb-0 text-gray-500">{{ __('Create a new dynamic document template with rich text and placeholders.') }}</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('personnel.report-templates.store') }}" method="POST">
                @csrf
                
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="hr-panel shadow-sm border-0 rounded-4">
                            <div class="mb-4">
                                <label for="name" class="form-label font-bold text-gray-700">{{ __('Template Name') }}</label>
                                <input type="text" name="name" id="name" class="form-control rounded-3 py-2" placeholder="{{ __('Enter template name (e.g. Appointment Letter)') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="content" class="form-label font-bold text-gray-700">{{ __('Template Content') }}</label>
                                <textarea name="content" id="editor" class="form-control">{{ old('content') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="hr-panel mb-4 shadow-sm border-0 rounded-4">
                            <h6 class="font-bold text-gray-800 mb-3"><i class="bi bi-info-circle me-2 text-success"></i>{{ __('Template Settings') }}</h6>

                            <div class="mb-3">
                                <label for="format" class="form-label small font-bold text-muted">{{ __('Format/Language') }}</label>
                                <select name="format" id="format" class="form-select rounded-3">
                                    @foreach($formats as $format)
                                        <option value="{{ $format }}">{{ $format }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                                    <label class="form-check-label font-bold" for="is_active">{{ __('Active Template') }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="tag-panel shadow-sm border-0 rounded-4">
                            <h6 class="font-bold text-gray-800 mb-2"><i class="bi bi-tags me-2 text-success"></i>{{ __('Dynamic Tags') }}</h6>
                            <p class="text-xs text-muted mb-3">{{ __('Select a report type to see available tags.') }}</p>
                            
                            <div id="tag-container" class="d-flex flex-wrap gap-1">
                                <!-- Tags will be populated via JS -->
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary w-100 font-bold py-2 shadow-sm">
                                <i class="bi bi-plus-lg me-2"></i> {{ __('Create Template') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>

    @push('scripts')
    <!-- Load CKEditor 5 Classic from CDN -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let activeEditor;

            ClassicEditor
                .create(document.querySelector('#editor'))
                .then(editor => {
                    activeEditor = editor;
                })
                .catch(error => {
                    console.error(error);
                });



            function insertTag(tag) {
                if (activeEditor) {
                    const model = activeEditor.model;
                    model.change(writer => {
                        writer.insertText(tag, model.document.selection.getFirstPosition());
                    });
                    activeEditor.editing.view.focus();
                }
            }
        });
    </script>
    @endpush
</x-app-layout>
