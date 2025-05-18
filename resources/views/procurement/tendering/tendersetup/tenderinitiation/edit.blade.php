@extends('layouts.app')

@section('title', 'Edit Tender - ' . $tender->TenderNo)

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .tender-form {
        background-color: #f8f9fa;
    }

    .form-section {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .form-section-title {
        color: #2c3e50;
        border-bottom: 1px solid #eee;
        padding-bottom: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .action-buttons {
        background: white;
        padding: 1.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
</style>
@endpush

@section('content')
<div class="container tender-form py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-edit text-primary me-2"></i>Edit Tender: {{ $tender->TenderNo }}
        </h1>
        <a href="{{ route('initiatetender.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-chevron-left me-1"></i> Back to List
        </a>
    </div>

    <form method="POST" action="{{ route('initiatetender.update', $tender->Id) }}" enctype="multipart/form-data" id="tenderForm">
        @csrf
        @method('PUT')

        <div class="form-section">
            <h5 class="form-section-title">1. Basic Information</h5>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Tender Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('Title') is-invalid @enderror"
                        name="Title" value="{{ old('Title', $tender->Title) }}" required>
                    @error('Title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Tender Type <span class="text-danger">*</span></label>
                    <div>
                        @foreach(\App\Enums\TenderTypeEnum::cases() as $type)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input @error('TenderType') is-invalid @enderror" type="radio" name="TenderType"
                                id="type-{{ $type->value }}" value="{{ $type->value }}"
                                {{ old('TenderType', $tender->TenderType->value) == $type->value ? 'checked' : '' }} required>
                            <label class="form-check-label" for="type-{{ $type->value }}">
                                {{ $type->displayName() }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @error('TenderType')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-select @error('TenderCategory') is-invalid @enderror"
                        id="TenderCategory" name="TenderCategory" required>
                        <option value="" disabled {{ old('TenderCategory', optional($tender->TenderCategory)->value) ? '' : 'selected' }}>-- Select Category --</option>
                        @foreach(\App\Enums\TenderCategoryEnum::cases() as $category)
                        <option value="{{ $category->value }}"
                            {{ old('TenderCategory', optional($tender->TenderCategory)->value) == $category->value ? 'selected' : '' }}>
                            {{ $category->displayName() }}
                        </option>
                        @endforeach
                    </select>
                    @error('TenderCategory')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="ProcurementModeId" class="form-label">Procurement Mode <span class="text-danger">*</span></label>
                    <select class="form-select @error('ProcurementModeId') is-invalid @enderror" id="ProcurementModeId" name="ProcurementModeId" required>
                        <option value="" disabled {{ old('ProcurementModeId', $tender->ProcurementModeId) ? '' : 'selected' }}>-- Select Procurement Mode --</option>
                        @foreach($procurementModes as $mode)
                        <option value="{{ $mode->id }}" {{ old('ProcurementModeId', $tender->ProcurementModeId) == $mode->id ? 'selected' : '' }}>
                            {{ $mode->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('ProcurementModeId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="RelatedPRID" class="form-label">Related PR ID (Optional)</label>
                    <input type="number" class="form-control @error('RelatedPRID') is-invalid @enderror"
                        id="RelatedPRID" name="RelatedPRID" value="{{ old('RelatedPRID', $tender->RelatedPRID) }}">
                    @error('RelatedPRID') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Estimated Value <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control @error('EstimatedValue') is-invalid @enderror"
                        name="EstimatedValue" value="{{ old('EstimatedValue', $tender->EstimatedValue) }}" required>
                    @error('EstimatedValue')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Currency <span class="text-danger">*</span></label>
                    <select class="form-select @error('Currency') is-invalid @enderror" name="Currency" required>
                        <option value="" disabled {{ old('Currency', $tender->Currency) ? '' : 'selected' }}>-- Select Currency --</option>
                        @foreach($currencies as $code => $name)
                        <option value="{{ $code }}" {{ old('Currency', $tender->Currency) == $code ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                        @endforeach
                    </select>
                    @error('Currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Start Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('StartDate') is-invalid @enderror"
                        name="StartDate" value="{{ old('StartDate', optional($tender->StartDate)->format('Y-m-d')) }}"
                        required>
                    @error('StartDate')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="ScopeOfWork" class="form-label">Scope Of Work</label>
                    <textarea class="form-control @error('ScopeOfWork') is-invalid @enderror" id="ScopeOfWork" name="ScopeOfWork" rows="3">{{ old('ScopeOfWork', $tender->ScopeOfWork) }}</textarea>
                    @error('ScopeOfWork') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="Instructions" class="form-label">Instructions</label>
                    <textarea class="form-control @error('Instructions') is-invalid @enderror" id="Instructions" name="Instructions" rows="3">{{ old('Instructions', $tender->Instructions) }}</textarea>
                    @error('Instructions') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <h5 class="form-section-title">2. Timeline & Status</h5>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Submission Deadline <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control @error('SubmissionDeadline') is-invalid @enderror"
                        name="SubmissionDeadline"
                        value="{{ old('SubmissionDeadline', optional($tender->SubmissionDeadline)->format('Y-m-d\TH:i')) }}" required>
                    @error('SubmissionDeadline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Opening Date <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control @error('OpeningDate') is-invalid @enderror"
                        name="OpeningDate"
                        value="{{ old('OpeningDate', optional($tender->OpeningDate)->format('Y-m-d\TH:i')) }}" required>
                    @error('OpeningDate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="Status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('Status') is-invalid @enderror" id="Status" name="Status" required>
                        @foreach($statuses as $status)
                        <option value="{{ $status->value }}" {{ old('Status', $tender->Status->value) == $status->value ? 'selected' : '' }}>
                            {{ $status->displayName() }}
                        </option>
                        @endforeach
                    </select>
                    @error('Status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <h5 class="form-section-title">3. Documents</h5>

            <div class="mb-3">
                <label class="form-label">Attach New Documents</label>
                <input type="file" class="form-control @error('tender_documents.*') is-invalid @enderror @error('tender_documents') is-invalid @enderror" name="tender_documents[]" multiple>
                <small class="text-muted">You can select multiple files. PDF, DOC, XLS files up to 5MB each.</small>
                @error('tender_documents')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('tender_documents.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            @if($tender->attachments && $tender->attachments->count() > 0)
            <div class="mt-3">
                <h6 class="mb-2">Existing Documents:</h6>
                <ul class="list-group">
                    @foreach($tender->attachments as $attachment)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <input type="checkbox" class="form-check-input me-2" name="delete_attachments[]" value="{{ $attachment->id }}" id="delete_attachment_{{ $attachment->id }}">
                            <label class="form-check-label" for="delete_attachment_{{ $attachment->id }}">
                                {{ $attachment->original_name }} ({{ \Illuminate\Support\Str::bytesToHuman($attachment->size) }})
                            </label>
                        </div>
                        <div>
                            <a href="{{ route('tender.document.download', $attachment->id) }}" class="btn btn-sm btn-outline-primary me-2" title="Download">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </li>
                    @endforeach
                </ul>
                <small class="text-muted mt-1 d-block">Check any documents you wish to remove upon updating.</small>
            </div>
            @endif
        </div>

        <div class="action-buttons mt-4">
            <div class="d-flex justify-content-between">
                <a href="{{ route('initiatetender.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>

                <div>
                    @if($tender->Status === \App\Enums\TenderStatusEnum::Draft || old('Status', $tender->Status->value) == \App\Enums\TenderStatusEnum::Draft->value)
                    <button type="submit" name="action" value="save_draft" class="btn btn-outline-primary me-2">
                        <i class="fas fa-save me-1"></i> Save Draft
                    </button>
                    @endif

                    <button type="submit" name="action" value="publish" class="btn btn-primary">
                        <i class="fas fa-check-circle me-1"></i> Update Tender
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize TomSelect
        if (document.getElementById('TenderCategory')) {
            new TomSelect('#TenderCategory', {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        }
        if (document.getElementById('ProcurementModeId')) {
            new TomSelect('#ProcurementModeId', {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        }
        // Potentially initialize for other selects like Currency, Status if desired.

        const form = document.getElementById('tenderForm');
        form.addEventListener('submit', function(e) {
            const submitButtons = form.querySelectorAll('button[type="submit"]');
            submitButtons.forEach(button => {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
            });
        });

        const submissionInput = document.querySelector('input[name="SubmissionDeadline"]');
        const openingInput = document.querySelector('input[name="OpeningDate"]');
        const startDateInput = document.querySelector('input[name="StartDate"]');

        if (startDateInput && submissionInput) {
            startDateInput.addEventListener('change', function() {
                if (this.value) {
                    submissionInput.min = this.value + 'T00:00';
                    if (openingInput && submissionInput.value) {
                        openingInput.min = submissionInput.value;
                    }
                }
            });
            if (startDateInput.value) startDateInput.dispatchEvent(new Event('change'));
        }


        if (submissionInput && openingInput) {
            submissionInput.addEventListener('change', function() {
                if (this.value) {
                    openingInput.min = this.value;
                    if (openingInput.value && openingInput.value < this.value) {
                        openingInput.value = '';
                    }
                }
            });
            if (submissionInput.value) submissionInput.dispatchEvent(new Event('change'));
        }
    });
</script>
@endpush