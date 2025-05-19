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

            {{-- Section 1: Basic Information --}}
            <div class="form-section">
                <h5 class="form-section-title">1. Basic Information</h5>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="Title" class="form-label">Tender Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('Title') is-invalid @enderror"
                               id="Title" name="Title" value="{{ old('Title', $tender->Title) }}" required>
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
                        <label for="TenderCategory" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select @error('TenderCategory') is-invalid @enderror"
                                id="TenderCategory" name="TenderCategory" required>
                            <option value="" disabled {{ !old('TenderCategory', optional($tender->TenderCategory)->value) ? 'selected' : '' }}>-- Select Category --</option>
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
                        <label for="ProcurementModeId" class="form-label fw-bold">Procurement Mode <span class="text-danger">*</span></label>
                        <select class="form-select @error('ProcurementModeId') is-invalid @enderror"
                                id="ProcurementModeId"
                                name="ProcurementModeId"
                                required>
                            <option value="" disabled {{ !old('ProcurementModeId', $tender->ProcurementModeId) ? 'selected' : '' }}>-- Select Mode --</option>
                            @foreach($procurementModes as $mode)
                                <option value="{{ $mode->id }}" {{ old('ProcurementModeId', $tender->ProcurementModeId) == $mode->id ? 'selected' : '' }}>
                                    {{ $mode->Name }}
                                </option>
                            @endforeach
                        </select>
                        @error('ProcurementModeId')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                        <label for="EstimatedValue" class="form-label">Estimated Value <span class="text-danger">*</span></label> {{-- Consider making * conditional if field is nullable --}}
                        <input type="number" step="0.01" class="form-control @error('EstimatedValue') is-invalid @enderror"
                               id="EstimatedValue" name="EstimatedValue" value="{{ old('EstimatedValue', $tender->EstimatedValue) }}" > {{-- Removed 'required' to align with 'nullable' validation, add back if it's truly required --}}
                        @error('EstimatedValue')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="Currency" class="form-label fw-bold">Currency <span class="text-danger">*</span></label>
                        <select class="form-select @error('Currency') is-invalid @enderror"
                                id="Currency" name="Currency" required>
                            <option value="" {{ !old('Currency', $tender->CurrencyId ?? null) ? 'selected' : '' }} disabled>
                                -- Select Currency --
                            </option>
                            @foreach($currencies as $currency)
                                <option value="{{ $currency->Id }}" {{ old('Currency', $tender->CurrencyId ?? null) == $currency->Id ? 'selected' : '' }}>
                                    {{ $currency->Code }} ({{ $currency->Name }})
                                </option>
                            @endforeach
                        </select>
                        @error('Currency')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="StartDate" class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('StartDate') is-invalid @enderror"
                               id="StartDate" name="StartDate" value="{{ old('StartDate', optional($tender->StartDate)->format('Y-m-d')) }}"
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

            {{-- Section 2: Timeline & Status --}}
            <div class="form-section">
                <h5 class="form-section-title">2. Timeline & Status</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="SubmissionDeadline" class="form-label">Submission Deadline <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('SubmissionDeadline') is-invalid @enderror"
                               id="SubmissionDeadline" name="SubmissionDeadline"
                               value="{{ old('SubmissionDeadline', optional($tender->SubmissionDeadline)->format('Y-m-d\TH:i')) }}" required>
                        @error('SubmissionDeadline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="OpeningDate" class="form-label">Opening Date <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('OpeningDate') is-invalid @enderror"
                               id="OpeningDate" name="OpeningDate"
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

            {{-- Section 3: Documents --}}
            <div class="form-section">
                <h5 class="form-section-title">3. Documents</h5>
                <div class="mb-3">
                    <label class="form-label">Attach New Documents</label>
                    <input type="file" class="form-control @error('tender_documents.*') is-invalid @enderror @error('tender_documents') is-invalid @enderror" name="tender_documents[]" multiple>
                    <small class="text-muted">You can select multiple files. PDF, DOC, XLS files up to 20MB each.</small>
                    @error('tender_documents')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror>
                    @error('tender_documents.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                @if($tender->documents && $tender->documents->count() > 0)
                    <div class="mt-3">
                        <h6 class="mb-2">Existing Documents:</h6>
                        <ul class="list-group">
                            @foreach($tender->documents as $document)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <input type="checkbox" class="form-check-input me-2" name="delete_documents[]" value="{{ $document->Id }}" id="delete_document_{{ $document->Id }}">
                                        <label class="form-check-label" for="delete_document_{{ $document->Id }}">
                                            {{ $document->FileName ?? 'N/A' }}
                                        </label>
                                    </div>
                                    <div>
                                        <a href="{{-- route('tender.document.download', $document->Id) --}}" class="btn btn-sm btn-outline-primary me-2" title="Download" target="_blank">
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

            {{-- Action Buttons --}}
            <div class="action-buttons mt-4">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('initiatetender.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <div>
                        @if($tender->Status === \App\Enums\TenderStatusEnum::Draft || old('Status', optional($tender->Status)->value) == \App\Enums\TenderStatusEnum::Draft->value)
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
            // Initialize TomSelect for specified elements
            ['TenderCategory', 'ProcurementModeId', 'Currency'].forEach(id => {
                if (document.getElementById(id)) {
                    new TomSelect('#' + id, {
                        create: false,
                        sortField: { field: "text", direction: "asc" }
                    });
                }
            });

            const form = document.getElementById('tenderForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Disable submit buttons to prevent multiple submissions
                    const submitButtons = form.querySelectorAll('button[type="submit"]');
                    submitButtons.forEach(button => {
                        button.disabled = true;
                        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
                    });
                });
            }

            // Date input logic for StartDate, SubmissionDeadline, and OpeningDate
            const startDateInput = document.querySelector('input[name="StartDate"]');
            const submissionInput = document.querySelector('input[name="SubmissionDeadline"]');
            const openingInput = document.querySelector('input[name="OpeningDate"]');

            function updateSubmissionMinDate() {
                if (startDateInput && submissionInput && startDateInput.value) {
                    // Submission deadline must be on or after start date.
                    // For datetime-local, we need to append time.
                    const startDateVal = startDateInput.value;
                    submissionInput.min = startDateVal + 'T00:00';
                    // If submission is before new min, clear or adjust it
                    if (submissionInput.value && submissionInput.value < submissionInput.min) {
                        // submissionInput.value = ''; // Option 1: Clear
                    }
                }
            }

            function updateOpeningMinDate() {
                if (submissionInput && openingInput && submissionInput.value) {
                    // Opening date must be after submission deadline.
                    openingInput.min = submissionInput.value;
                    // If opening date is before new min, clear or adjust it
                    if (openingInput.value && openingInput.value <= submissionInput.min) {
                        // openingInput.value = ''; // Option 1: Clear
                    }
                }
            }

            if (startDateInput) {
                startDateInput.addEventListener('change', function() {
                    updateSubmissionMinDate();
                    updateOpeningMinDate();
                });
                // Initial check
                if (startDateInput.value) updateSubmissionMinDate();
            }

            if (submissionInput) {
                submissionInput.addEventListener('change', updateOpeningMinDate);
                // Initial check
                if (submissionInput.value) updateOpeningMinDate();
            }
        });
    </script>
@endpush
