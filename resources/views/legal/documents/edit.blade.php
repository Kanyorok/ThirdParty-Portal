@extends('layouts.app')
@section('title', 'Edit Legal Document')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-9">
            <form method="POST" action="{{ route('legal.documents.update', $doc->Id) }}"
                  enctype="multipart/form-data" id="documentForm">
                @csrf
                @method('PUT')
                
        <div class="card shadow rounded-4 border-0">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-info">
                    <i class="fas fa-edit me-2"></i> Edit Legal Document
                </h5>
                <a href="{{ route('legal.documents.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card-body px-4 py-4">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="DocumentTitle" class="form-label">Title</label>
                            <input type="text" name="DocumentTitle" id="DocumentTitle"
                                   class="form-control @error('DocumentTitle') is-invalid @enderror"
                                   value="{{ old('DocumentTitle', $doc->DocumentTitle) }}" required>
                            @error('DocumentTitle')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="DocumentType" class="form-label">Document Type</label>
                            <select name="DocumentType" id="DocumentType"
                                    class="form-select @error('DocumentType') is-invalid @enderror" required>
                                <option value="">-- Select type --</option>
                                @foreach ($docTypes as $type)
                                    <option
                                        value="{{ $type->Description }}" @selected(old('DocumentType',$doc->DocumentType) === $type->Description)>
                                        {{ $type->Description }}
                                    </option>
                                @endforeach
                            </select>
                            @error('DocumentType')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="SourceModule" class="form-label">Source Module</label>
                            <select name="SourceModule" id="SourceModule"
                                    class="form-select @error('SourceModule') is-invalid @enderror">
                                <option value="">-- Select source module --</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module->ModuleID }}" @selected(old('SourceModule',$doc->SourceID) == $module->ModuleID)>{{ $module->Name }}</option>
                                @endforeach
                            </select>
                            @error('SourceModule')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Mode Selector for Adding New Documents -->
                    <hr class="my-4">
                    <h6 class="text-info mb-3"><i class="fas fa-plus-circle me-2"></i>Add New Document Version</h6>
                    
                    <div class="mode-selector" style="background: #f8f9fa; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
                        <label class="form-label fw-semibold mb-3"><i class="fas fa-magic me-2"></i>How to add?</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="mode-option active" id="uploadModeLabel" style="cursor: pointer; padding: 0.75rem 1rem; border: 2px solid #dee2e6; border-radius: 0.375rem; transition: all 0.2s; display: block;">
                                    <input type="radio" name="creation_mode" value="upload" checked id="uploadMode">
                                    <i class="fas fa-upload me-2"></i>Upload File
                                    <div class="small text-muted mt-1">Upload new document</div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="mode-option" id="templateModeLabel" style="cursor: pointer; padding: 0.75rem 1rem; border: 2px solid #dee2e6; border-radius: 0.375rem; transition: all 0.2s; display: block;">
                                    <input type="radio" name="creation_mode" value="template" id="templateMode">
                                    <i class="fas fa-file-contract me-2"></i>Create from Template
                                    <div class="small text-muted mt-1">Edit template content</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <!-- Upload Section (Default) -->
                        <div class="col-md-12" id="fileUploadSection">
                            <label for="LinkedDMSDocID" class="form-label">Upload New File</label>
                            <input type="file" name="LinkedDMSDocID" id="LinkedDMSDocID"
                                   class="form-control @error('LinkedDMSDocID') is-invalid @enderror"
                                   accept=".pdf,.jpeg,.png,.docx,.xlsx">
                            <small class="text-muted">
                                Upload a new file to add as an additional document version. Previous documents will remain available.
                            </small>
                            @error('LinkedDMSDocID')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Template Selection (Hidden by default) -->
                        <div class="col-md-12" id="templateSelectSection" style="display: none;">
                            <label for="template_id" class="form-label">Template <span class="text-danger">*</span></label>
                            <select name="template_id" id="template_id" class="form-control" style="width: 100%;">
                                <option value="">-- Select template --</option>
                            </select>
                            <small class="text-muted" id="templateHint">Choose document type to see templates</small>
                        </div>
                    </div>

                    <!-- Editor Section for Template Mode -->
                    <div id="templateEditorSection" style="display: none;">
                        <hr>
                        <h6 class="text-info mb-3"><i class="fa fa-edit me-2"></i>Edit Content</h6>
                        <div class="mb-3">
                            <textarea name="document_body" id="editor" class="form-control" rows="12"></textarea>
                        </div>
                        <input type="hidden" name="selected_clause_ids" id="selected_clause_ids" value="[]">
                    </div>



                    <div class="mb-3">
                        <label for="Remarks" class="form-label">Remarks</label>
                        <textarea name="Remarks" id="Remarks"
                                  class="form-control @error('Remarks') is-invalid @enderror"
                                  rows="3">{{ old('Remarks', $doc->Remarks) }}</textarea>
                        @error('Remarks')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Optional: let admins update statuses --}}
                    <div class="row mb-3">
                        {{--                        <div class="col-md-6">--}}
                        {{--                            <label for="ReviewStatus" class="form-label">Review Status</label>--}}
                        {{--                            <select name="ReviewStatus" id="ReviewStatus" class="form-select">--}}
                        {{--                                @foreach (['Draft','In Review','Approved','Rejected'] as $r)--}}
                        {{--                                    <option value="{{ $r }}" @selected(old('ReviewStatus',$doc->ReviewStatus) === $r)>{{ $r }}</option>--}}
                        {{--                                @endforeach--}}
                        {{--                            </select>--}}
                        {{--                        </div>--}}
                        @if($doc->ExecutionStatus==='Pending')
                            <div class="col-md-12">
                                <label for="ExecutionStatus" class="form-label">Execution Status</label>
                                <select name="ExecutionStatus" id="ExecutionStatus" class="form-select">
                                    @foreach ($execStatuses as $e)
                                        <option value="{{ $e->Description }}">{{ $e->Description }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>

                    <div class="text-end">
                        <button class="btn btn-success" id="postBtn" type="submit"
                                onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Updating...';
                                    this.form.submit();}"><i class="fas fa-save me-1"></i> Update Document
                        </button>
                    </div>
                </div>
            </div>
            </form>
        </div>
        
        <!-- Clause Sidebar (Top Right) -->
        <div class="col-lg-3" id="clauseSidebar" style="display: none;">
            <div class="card shadow" style="position: sticky; top: 1rem;">
                <div class="card-header bg-light py-2">
                    <h6 class="mb-0 text-info"><i class="fa fa-book"></i> Clauses</h6>
                </div>
                <div class="card-body">
                    <input type="text" id="clause-search" class="form-control form-control-sm mb-2" placeholder="Search...">
                    <div id="clause-results" style="max-height: 400px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const CLAUSES = @json($clauses ?? []);
let editor = null;
let selectedClauses = [];

$(document).ready(function() {
    // Initialize Select2
    $('#DocumentType').select2({ theme: 'bootstrap-5', placeholder: '-- Select document type --', allowClear: false });
    $('#SourceModule').select2({ theme: 'bootstrap-5', placeholder: '-- Select source module --', allowClear: false });
    $('#template_id').select2({ theme: 'bootstrap-5', placeholder: '-- Select template --', allowClear: true });

    // Mode toggle
    $('input[name="creation_mode"]').on('change', function() {
        const mode = $(this).val();
        $('.mode-option').removeClass('active').css({'border-color': '#dee2e6', 'background': 'transparent', 'color': 'inherit'});
        $(this).closest('.mode-option').addClass('active').css({'border-color': '#0d6efd', 'background': '#0d6efd', 'color': 'white'});
        
        if (mode === 'upload') {
            $('#fileUploadSection').show();
            $('#templateSelectSection, #templateEditorSection, #clauseSidebar').hide();
            $('#template_id').attr('required', false);
        } else {
            $('#fileUploadSection').hide();
            $('#templateSelectSection, #clauseSidebar').show();
            $('#template_id').attr('required', true);
            renderClauseLibrary();
            
            // Auto-fetch templates if document type is already selected
            const currentDocType = $('#DocumentType').val();
            if (currentDocType) {
                loadTemplatesByType(currentDocType);
            }
        }
    });

    $('#DocumentType').on('change', function() {
        if ($('input[name="creation_mode"]:checked').val() === 'template') {
            loadTemplatesByType($(this).val());
        }
    });

    $('#template_id').on('change', function() {
        const templateId = $(this).val();
        if (templateId) {
            loadTemplateContent(templateId);
        } else {
            $('#templateEditorSection').hide();
            if (editor) editor.setData('');
        }
    });

    function loadTemplatesByType(docType) {
        const $select = $('#template_id');
        $select.prop('disabled', true).html('<option value="">Loading...</option>');
        $.ajax({
            url: '{{ route("legal.templates.by-type") }}',
            data: { document_type: docType },
            success: function(response) {
                $select.prop('disabled', false);
                if (response.templates && response.templates.length) {
                    let options = '<option value="">-- Select template --</option>';
                    response.templates.forEach(t => options += `<option value=\"${t.id}\">${t.title} (v${t.version})</option>`);
                    $select.html(options);
                    $('#templateHint').text(`${response.templates.length} template(s) available`);
                } else {
                    $select.html('<option value="">No templates</option>');
                    $('#templateHint').html('<span class="text-warning">No templates. <a href="/legal/templates/create" target="_blank">Create?</a></span>');
                }
            },
            error: () => $select.prop('disabled', false).html('<option value="">Error</option>')
        });
    }

    function loadTemplateContent(templateId) {
        $.ajax({
            url: `/legal/templates/${templateId}/content`,
            success: function(response) {
                if (!editor) {
                    ClassicEditor.create(document.querySelector('#editor'))
                        .then(ed => { editor = ed; editor.setData(response.template_body || ''); })
                        .catch(console.error);
                } else {
                    editor.setData(response.template_body || '');
                }
                $('#templateEditorSection').slideDown();
                selectedClauses = response.clause_ids || [];
                $('#selected_clause_ids').val(JSON.stringify(selectedClauses));
            },
            error: () => alert('Failed to load template')
        });
    }

    function renderClauseLibrary() {
        const $results = $('#clause-results');
        $results.html('');
        if (!CLAUSES || !CLAUSES.length) {
            $results.html('<div class=\"text-muted small text-center py-3\">No clauses</div>');
            return;
        }
        CLAUSES.forEach(clause => {
            const item = $(`
                <div class="clause-item border rounded p-2 mb-2" style="cursor: pointer; transition: background-color .2s;">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fw-semibold small">${escapeHtml(clause.Title ||'Untitled')}</div>
                            <div class="text-muted" style="font-size:11px;">${escapeHtml(clause.ClauseType || '—')}</div>
                        </div>
                        <button class="btn btn-sm btn-outline-primary insert-btn" type="button" title="Insert">
                            <i class="fa fa-download"></i>
                        </button>
                    </div>
                </div>
            `);
            item.find('.insert-btn').on('click', function() {
                if (editor) {
                    editor.model.change(writer => {
                        const pos = editor.model.document.selection.getFirstPosition();
                        writer.insertText('\\n\\n' + (clause.Content || '') + '\\n\\n', pos);
                    });
                    if (!selectedClauses.includes(clause.Id)) {
                        selectedClauses.push(clause.Id);
                        $('#selected_clause_ids').val(JSON.stringify(selectedClauses));
                    }
                }
            });
            $results.append(item);
        });
    }

    $('#clause-search').on('input', function() {
        const q = $(this).val().toLowerCase();
        $('.clause-item').each(function() {
            $(this).toggle($(this).text().toLowerCase().includes(q));
        });
    });

    $('form').on('submit', function() {
        if (editor && $('input[name="creation_mode"]:checked').val() === 'template') {
            $('#editor').val(editor.getData());
        }
        return true;
    });

    function escapeHtml(str) {
        return (str ?? '').toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    $('.mode-option').hover(
        function() { if(!$(this).hasClass('active')) $(this).css({'border-color': '#0d6efd', 'background': '#e7f3ff'}); },
        function() { if(!$(this).hasClass('active')) $(this).css({'border-color': '#dee2e6', 'background': 'transparent'}); }
    );
});
</script>
@endsection
