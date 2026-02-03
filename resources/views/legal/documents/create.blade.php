@extends('layouts.app')
@section('title', 'Add Legal Document')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .mode-selector { background: #f8f9fa; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; }
    .mode-option { cursor: pointer; padding: 0.75rem 1rem; border: 2px solid #dee2e6; border-radius: 0.375rem; transition: all 0.2s; }
    .mode-option:hover { border-color: #0d6efd; background: #e7f3ff; }
    .mode-option.active { border-color: #0d6efd; background: #0d6efd; color: white; }
    .mode-option input[type="radio"] { margin-right: 0.5rem; }
    .clause-item { cursor: pointer; transition: background-color .2s; }
    .clause-item:hover { background: #f8fafc; }
</style>
@endsection

@section('content')
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    <div class="col-lg-9">
        <form method="POST" action="{{ route('legal.documents.store') }}" enctype="multipart/form-data" id="documentForm">
            @csrf
            
            <div class="card shadow">
                <div class="card-header bg-light py-2 d-flex justify-content-between">
                    <h5 class="mb-0 text-info"><i class="fa fa-file-signature me-2"></i> Add Document</h5>
                    <a href="{{ route('legal.documents.index') }}" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
                </div>

                <div class="card-body">
                    <!-- Mode Selector -->
                    <div class="mode-selector">
                        <label class="form-label fw-semibold mb-3"><i class="fas fa-magic me-2"></i>How to create?</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="mode-option active" id="uploadModeLabel">
                                    <input type="radio" name="creation_mode" value="upload" checked id="uploadMode">
                                    <i class="fas fa-upload me-2"></i>Upload File
                                    <div class="small text-muted mt-1">Upload existing document</div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="mode-option" id="templateModeLabel">
                                    <input type="radio" name="creation_mode" value="template" id="templateMode">
                                    <i class="fas fa-file-contract me-2"></i>Create from Template
                                    <div class="small text-muted mt-1">Edit template content</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Metadata -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="DocumentTitle" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="DocumentTitle" id="DocumentTitle" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="DocumentType" class="form-label">Type <span class="text-danger">*</span></label>
                            <select name="DocumentType" id="DocumentType" class="form-control" required>
                                <option value="">-- Select type --</option>
                                @foreach($docTypes as $docType)
                                <option value="{{ $docType->Description }}">{{ $docType->Description }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="SourceModule" class="form-label">Source Module <span class="text-danger">*</span></label>
                            <select name="SourceModule" id="SourceModule" class="form-control" required>
                                <option value="">-- Select source module --</option>
                                @foreach($modules as $module)
                                <option value="{{ $module->ModuleID }}" {{ strtolower($module->Name) === 'legal' ? 'selected' : '' }}>{{ $module->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Upload Section -->
                        <div class="col-md-6" id="fileUploadSection">
                            <label for="LinkedDMSDocID" class="form-label">Upload <span class="text-danger">*</span></label>
                            <input type="file" name="LinkedDMSDocID" id="LinkedDMSDocID" class="form-control" accept=".pdf,.docx,.xlsx,.jpg,.jpeg,.png">
                            <small class="text-muted">PDF, DOCX, XLSX, JPG, PNG</small>
                        </div>
                        
                        <!-- Template Selection -->
                        <div class="col-md-6" id="templateSelectSection" style="display: none;">
                            <label for="template_id" class="form-label">Template <span class="text-danger">*</span></label>
                            <select name="template_id" id="template_id" class="form-control" style="width: 100%;">
                                <option value="">-- Select template --</option>
                            </select>
                            <small class="text-muted" id="templateHint">Choose a type to see templates</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="Remarks" class="form-label">Remarks</label>
                        <textarea name="Remarks" id="Remarks" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="DueDate" class="form-label">Due Date</label>
                            <input type="date" name="DueDate" id="DueDate" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="ExpiryDate" class="form-label">Expiry Date</label>
                            <input type="date" name="ExpiryDate" id="ExpiryDate" class="form-control">
                        </div>
                        <div class="col-12">
                            <small class="text-muted"><i class="fa fa-info-circle"></i> Optional: Setting a due date will automatically create a legal obligation to track compliance</small>
                        </div>
                    </div>

                    <!-- Editor Section -->
                    <div id="templateEditorSection" style="display: none;">
                        <hr>
                        <h6 class="text-info mb-3"><i class="fa fa-edit me-2"></i>Edit Content</h6>
                        <div class="mb-3">
                            <textarea name="document_body" id="editor" class="form-control" rows="12"></textarea>
                        </div>
                        <input type="hidden" name="selected_clause_ids" id="selected_clause_ids" value="[]">
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success" onclick="if(this.form.checkValidity()){this.disabled=true;this.innerText='Saving...';this.form.submit();}">
                            <i class="fa fa-save"></i> Save Document
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Clause Sidebar -->
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

@section('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
const CLAUSES = @json($clauses ?? []);
let editor = null;
let selectedClauses = [];

$(document).ready(function() {
    // Initialize Select2 for Type and Source Module
    $('#DocumentType').select2({ 
        theme: 'bootstrap-5', 
        placeholder: '-- Select document type --',
        allowClear: false
    });
    
    $('#SourceModule').select2({ 
        theme: 'bootstrap-5', 
        placeholder: '-- Select source module --',
        allowClear: false
    });
    
    $('#template_id').select2({ theme: 'bootstrap-5', placeholder: '-- Select template --', allowClear: true });

    // Mode toggle
    $('input[name="creation_mode"]').on('change', function() {
        const mode = $(this).val();
        $('.mode-option').removeClass('active');
        $(this).closest('.mode-option').addClass('active');
        
        if (mode === 'upload') {
            $('#fileUploadSection').show();
            $('#templateSelectSection, #templateEditorSection, #clauseSidebar').hide();
            $('#LinkedDMSDocID').attr('required', true);
            $('#template_id').attr('required', false);
        } else {
            $('#fileUploadSection, #templateEditorSection').hide();
            $('#templateSelectSection, #clauseSidebar').show();
            $('#LinkedDMSDocID').attr('required', false);
            $('#template_id').attr('required', true);
            renderClauseLibrary();
        }
    });

    // Document type change -> load templates
    $('#DocumentType').on('change', function() {
        if ($('input[name="creation_mode"]:checked').val() === 'template') {
            loadTemplatesByType($(this).val());
        }
    });

    // Template selection -> load content
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
                    response.templates.forEach(t => options += `<option value="${t.id}">${t.title} (v${t.version})</option>`);
                    $select.html(options);
                    $('#templateHint').text(`${response.templates.length} template(s) available`);
                } else {
                    $select.html('<option value="">No templates</option>');
                    $('#templateHint').html('<span class="text-warning">No templates. <a href="{{ route("legal.templates.create") }}" target="_blank">Create?</a></span>');
                }
            },
            error: () => {
                $select.prop('disabled', false).html('<option value="">Error</option>');
            }
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
            $results.html('<div class="text-muted small text-center py-3">No clauses</div>');
            return;
        }
        
        CLAUSES.forEach(clause => {
            const item = $(`
                <div class="clause-item border rounded p-2 mb-2">
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
                    const content = clause.Content || '';
                    editor.model.change(writer => {
                        const pos = editor.model.document.selection.getFirstPosition();
                        writer.insertText('\n\n' + content + '\n\n', pos);
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

    // Sync CKEditor content to textarea before form submission
    $('#documentForm').on('submit', function(e) {
        if (editor && $('input[name="creation_mode"]:checked').val() === 'template') {
            // Get the data from CKEditor and set it to the textarea
            const editorData = editor.getData();
            $('#editor').val(editorData);
            
            // Debug log to verify content
        }
        return true; // Allow form to continue submitting
    });

    function escapeHtml(str) {
        return (str ?? '').toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
});
</script>
@endsection
@endsection