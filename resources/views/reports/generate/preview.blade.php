<x-app-layout>
    @push('styles')
    
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
        .download-btn-group {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            flex-wrap: wrap;
        }
        .download-btn-group .btn {
            min-width: 170px;
        }
    </style>
    @endpush

    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 text-2xl font-bold">{{ __('Preview Letter:') }} {{ $reportName }}</h5>
                        <p class="mb-0 text-gray-500">{{ __('Make any final adjustments before downloading.') }}</p>
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
                        <div class="mb-4">
                            <label for="editor" class="form-label font-bold text-gray-700">{{ __('Document Content') }}</label>
                            <!-- The textarea where CKEditor will be initialized -->
                            <textarea name="final_content" id="editor" class="form-control">{!! $content !!}</textarea>
                        </div>

                        <div class="download-btn-group">
                            <!-- PDF Download Form -->
                            <form action="{{ route('personnel.reports.generate.pdf') }}" method="POST" id="pdf-form">
                                @csrf
                                <input type="hidden" name="report_name" value="{{ $reportName }}">
                                <input type="hidden" name="final_content" id="pdf-content" value="">
                                <button type="submit" class="btn btn-primary px-4 py-2 font-bold">
                                    <i class="bi bi-file-earmark-pdf me-2"></i> {{ __('Download PDF') }}
                                </button>
                            </form>

                            <!-- PDF Print Form -->
                            <form action="{{ route('personnel.reports.generate.pdf') }}" method="POST" id="print-pdf-form" target="_blank">
                                @csrf
                                <input type="hidden" name="action" value="print">
                                <input type="hidden" name="report_name" value="{{ $reportName }}">
                                <input type="hidden" name="final_content" id="print-pdf-content" value="">
                                <button type="submit" class="btn btn-info px-4 py-2 font-bold text-white">
                                    <i class="bi bi-printer me-2"></i> {{ __('Print PDF') }}
                                </button>
                            </form>

                            <!-- DOCX Download Form -->
                            <form action="{{ route('personnel.reports.generate.docx') }}" method="POST" id="docx-form">
                                @csrf
                                <input type="hidden" name="report_name" value="{{ $reportName }}">
                                <input type="hidden" name="final_content" id="docx-content" value="">
                                <button type="submit" class="btn btn-success px-4 py-2 font-bold">
                                    <i class="bi bi-file-earmark-word me-2"></i> {{ __('Download DOCX') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let editorInstance = null;

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
                    editorInstance = editor;

                    // Keep the underlying textarea in sync
                    editor.model.document.on('change:data', () => {
                        document.querySelector('#editor').value = editor.getData();
                    });
                })
                .catch(error => {
                    console.error(error);
                });

            // Sync CKEditor content to hidden fields before form submission
            function syncEditorContent(formId, hiddenFieldId) {
                const form = document.getElementById(formId);
                form.addEventListener('submit', function(e) {
                    if (editorInstance) {
                        document.getElementById(hiddenFieldId).value = editorInstance.getData();
                    }
                });
            }

            syncEditorContent('pdf-form', 'pdf-content');
            syncEditorContent('print-pdf-form', 'print-pdf-content');
            syncEditorContent('docx-form', 'docx-content');
        });
    </script>
    @endpush
</x-app-layout>



