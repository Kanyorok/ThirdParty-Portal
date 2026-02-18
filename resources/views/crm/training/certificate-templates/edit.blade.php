@extends('layouts.app')

@section('title', 'Edit Certificate Template')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Certificate Template</h2>
        <a class="btn btn-outline-secondary" href="{{ route('crm.training.certificate-templates.index') }}">Back</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('crm.training.certificate-templates.update', $template->Id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Template Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name', $template->Name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Program Scope</label>
                        <select name="ProgramID" class="form-select">
                            <option value="">Global (all programs)</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->Id }}" @selected(old('ProgramID', $template->ProgramID) == $program->Id)>{{ $program->Title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea name="Description" class="form-control" rows="2">{{ old('Description', $template->Description) }}</textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Template Body (Placeholder Text)</label>
                        <textarea id="TemplateBody" name="TemplateBody" class="form-control" rows="6">{{ old('TemplateBody', $template->TemplateBody) }}</textarea>
                        <div class="form-text">Use placeholders from the reference card below.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Default Issuing Body</label>
                        <input type="text" name="DefaultIssuingBody" class="form-control" value="{{ old('DefaultIssuingBody', $template->DefaultIssuingBody) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Default Validity (Months)</label>
                        <input type="number" min="1" max="240" name="DefaultValidityMonths" class="form-control" value="{{ old('DefaultValidityMonths', $template->DefaultValidityMonths) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Background Sample</label>
                        <select id="BackgroundImagePath" name="BackgroundImagePath" class="form-select">
                            <option value="">None (white background)</option>
                            @foreach($backgroundImageChoices as $path => $label)
                                <option value="{{ $path }}" data-image-url="{{ asset($path) }}" @selected(old('BackgroundImagePath', $template->BackgroundImagePath) === $path)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Choose a built-in image style.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Template File (Optional)</label>
                        <input type="file" name="TemplateDocumentFile" class="form-control" accept=".jpg,.jpeg,.png,.svg,.pdf">
                        <div class="form-text">Upload a certificate background image (recommended) or a PDF template.</div>
                        @if($template->document)
                            <div class="form-text">
                                Current file: <a href="{{ route('file.preview', ['document' => $template->document->DocumentId]) }}" target="_blank">View</a>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="IsSample" name="IsSample" value="1" @checked(old('IsSample', $template->IsSample))>
                            <label class="form-check-label" for="IsSample">Mark as sample</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="IsActive" name="IsActive" value="1" @checked(old('IsActive', $template->IsActive))>
                            <label class="form-check-label" for="IsActive">Active</label>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h6 class="mb-2">Placeholders</h6>
                            <ul class="mb-0 small">
                                @foreach($placeholderTokens as $token => $description)
                                    <li><code>{{ $token }}</code> - {{ $description }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h6 class="mb-2">Preview</h6>
                            <img id="BackgroundSamplePreview" alt="Background sample preview" class="img-fluid rounded border d-none mb-2" style="max-height: 180px;">
                            <div id="TemplatePreview" class="small text-muted"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">Update Template</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bodyInput = document.getElementById('TemplateBody');
        const preview = document.getElementById('TemplatePreview');
        const backgroundSelect = document.getElementById('BackgroundImagePath');
        const backgroundPreview = document.getElementById('BackgroundSamplePreview');
        const token = function (name) {
            return '{' + '{' + name + '}' + '}';
        };

        const placeholderValues = {
            [token('participant_name')]: 'Jane Doe',
            [token('client_id')]: 'C001234',
            [token('program_name')]: 'Financial Literacy Program',
            [token('session_title')]: 'Morning Session',
            [token('issue_date')]: '2026-02-15',
            [token('expiry_date')]: '2028-02-15',
            [token('certificate_number')]: 'CERT-2026-001',
            [token('issuing_body')]: 'BR_ERP Academy'
        };

        const renderPreview = function () {
            let text = bodyInput.value || '';
            Object.keys(placeholderValues).forEach(function (token) {
                text = text.split(token).join(placeholderValues[token]);
            });
            preview.textContent = text.trim() !== '' ? text : 'No template body entered.';
        };

        const renderBackgroundPreview = function () {
            if (!backgroundSelect || !backgroundPreview) {
                return;
            }

            const selected = backgroundSelect.options[backgroundSelect.selectedIndex];
            const imageUrl = selected ? selected.getAttribute('data-image-url') : '';
            if (imageUrl) {
                backgroundPreview.src = imageUrl;
                backgroundPreview.classList.remove('d-none');
            } else {
                backgroundPreview.removeAttribute('src');
                backgroundPreview.classList.add('d-none');
            }
        };

        bodyInput.addEventListener('input', renderPreview);
        if (backgroundSelect) {
            backgroundSelect.addEventListener('change', renderBackgroundPreview);
        }
        renderPreview();
        renderBackgroundPreview();
    });
</script>
@endpush
