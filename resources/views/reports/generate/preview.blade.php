<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    <!-- Include CKEditor 5 from CDN -->
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
    <style>
        .ck-editor__editable_inline {
            min-height: 400px;
        }
        .preview-panel {
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
                        <h5 class="mb-1 text-2xl font-bold">{{ __('Preview Report:') }} {{ $reportName }}</h5>
                        <p class="mb-0 text-gray-500">{{ __('Make any final adjustments before generating the PDF.') }}</p>
                    </div>
                    <div>
                        <a href="{{ route('personnel.reports.generate') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i> {{ __('Back') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="preview-panel">
                        <form action="{{ route('personnel.reports.generate.pdf') }}" method="POST">
                            @csrf
                            <input type="hidden" name="report_name" value="{{ $reportName }}">
                            
                            <div class="mb-4">
                                <label for="editor" class="form-label font-bold text-gray-700">{{ __('Document Content') }}</label>
                                <!-- The textarea where CKEditor will be initialized -->
                                <textarea name="final_content" id="editor" class="form-control">{!! $content !!}</textarea>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary px-4 py-2 font-bold">
                                    <i class="bi bi-file-earmark-pdf me-2"></i> {{ __('Generate PDF') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            ClassicEditor
                .create(document.querySelector('#editor'), {
                    toolbar: [
                        'heading', '|',
                        'bold', 'italic', 'underline', 'strikethrough', '|',
                        'bulletedList', 'numberedList', 'alignment', '|',
                        'outdent', 'indent', '|',
                        'link', 'insertTable', 'blockQuote', '|',
                        'undo', 'redo'
                    ],
                    table: {
                        contentToolbar: [
                            'tableColumn',
                            'tableRow',
                            'mergeTableCells'
                        ]
                    }
                })
                .then(editor => {
                    // Update the underlying textarea before form submission
                    editor.model.document.on('change:data', () => {
                        document.querySelector('#editor').value = editor.getData();
                    });
                })
                .catch(error => {
                    console.error(error);
                });
        });
    </script>
    @endpush
</x-app-layout>
