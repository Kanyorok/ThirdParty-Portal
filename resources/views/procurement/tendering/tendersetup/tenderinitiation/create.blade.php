@extends('layouts.app')

{{-- The title was 'Item Sub Category' in your provided code, changing to 'Initiate New Tender' for consistency --}}
@section('title', 'Initiate New Tender')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    :root {
        --bs-primary-rgb: 58, 123, 213;
        /* Main primary color */
        --bs-secondary-rgb: 108, 117, 125;
        /* Standard secondary */
        --bs-light-rgb: 248, 249, 250;
        /* Standard light */
        --bs-dark-rgb: 33, 37, 41;
        /* Standard dark */
        --bs-border-color: #dee2e6;
        --bs-body-bg: #f4f7fc;
        /* Light blue/gray page background */
        --bs-card-bg: #ffffff;
        --form-label-color: #495057;
        --input-border-color: #ced4da;
        --input-focus-border-color: rgba(var(--bs-primary-rgb), 0.6);
        --input-focus-box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.15);
    }

    body {
        background-color: var(--bs-body-bg);
        color: var(--bs-dark-rgb);
        font-family: 'Inter', sans-serif;
        /* Using Inter font */
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
    }

    .page-title {
        color: var(--bs-dark-rgb);
        font-weight: 600;
        font-size: 1.75rem;
    }

    .page-title .fas {
        color: rgb(var(--bs-primary-rgb));
        margin-right: 0.75rem;
    }

    .card {
        background-color: var(--bs-card-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.075);
        margin-bottom: 2rem;
    }

    .card-header {
        background-color: #f7f9fc;
        border-bottom: 1px solid var(--bs-border-color);
        padding: 1rem 1.5rem;
    }

    .card-header .card-title-icon .fas {
        color: var(--bs-secondary-rgb);
        margin-right: 0.6rem;
    }

    .card-body {
        padding: 2rem 2.5rem;
    }

    .form-label {
        font-weight: 500;
        color: var(--form-label-color);
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .form-control,
    .form-select {
        border-radius: 0.375rem;
        font-size: 0.95rem;
        padding: 0.6rem 0.9rem;
        border: 1px solid var(--input-border-color);
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--input-focus-border-color);
        box-shadow: var(--input-focus-box-shadow);
    }

    .form-check-input:checked {
        background-color: rgb(var(--bs-primary-rgb));
        border-color: rgb(var(--bs-primary-rgb));
    }

    .form-check-label {
        font-weight: normal;
        font-size: 0.95rem;
    }

    .form-text {
        font-size: 0.85em;
        color: #6c757d;
    }

    h6.form-section-title {
        font-size: 1.15rem;
        font-weight: 600;
        color: rgb(var(--bs-primary-rgb));
        border-bottom: 2px solid rgba(var(--bs-primary-rgb), 0.2);
        padding-bottom: 0.6rem;
        margin-top: 2.5rem;
        margin-bottom: 1.75rem !important;
    }

    h6.form-section-title:first-of-type {
        margin-top: 0.5rem;
    }

    .ts-control {
        /* Tom Select Customization */
        padding: 0.525rem 0.9rem !important;
        border-radius: 0.375rem !important;
        font-size: 0.95rem;
        border: 1px solid var(--input-border-color) !important;
    }

    .ts-control.focus {
        border-color: var(--input-focus-border-color) !important;
        box-shadow: var(--input-focus-box-shadow) !important;
    }

    .is-invalid .ts-control {
        border-color: var(--bs-danger) !important;
    }

    .is-valid .ts-control {
        border-color: var(--bs-success) !important;
    }


    .btn-back-to-list-header {
        font-size: 0.875rem;
        font-weight: 500;
        color: rgb(var(--bs-secondary-rgb));
        background-color: #fff;
        border: 1px solid var(--bs-border-color);
        padding: 0.45rem 0.9rem;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
    }

    .btn-back-to-list-header:hover {
        background-color: #f8f9fa;
        color: var(--bs-dark-rgb);
        border-color: #adb5bd;
    }

    .btn-back-to-list-header .fas {
        margin-right: 0.4rem;
    }

    .action-buttons-toolbar {
        border-top: 1px solid var(--bs-border-color);
        padding-top: 1.5rem;
        margin-top: 2.5rem;
    }

    .btn-form-action {
        padding: 0.65rem 1.25rem;
        font-size: 0.95rem;
        font-weight: 500;
        border-radius: 0.375rem;
        min-width: 140px;
        text-align: center;
        transition: all 0.15s ease-in-out;
        letter-spacing: 0.02em;
    }

    .btn-form-action .fas {
        margin-right: 0.6rem;
    }

    .btn-form-action.btn-primary-publish {
        background-color: rgb(var(--bs-primary-rgb));
        border-color: rgb(var(--bs-primary-rgb));
        color: #fff;
        box-shadow: 0 4px 10px rgba(var(--bs-primary-rgb), 0.2);
    }

    .btn-form-action.btn-primary-publish:hover {
        background-color: rgba(var(--bs-primary-rgb), 0.88);
        border-color: rgba(var(--bs-primary-rgb), 0.88);
        box-shadow: 0 6px 12px rgba(var(--bs-primary-rgb), 0.25);
        transform: translateY(-1px);
    }

    .btn-form-action.btn-outline-primary-draft {
        color: rgb(var(--bs-primary-rgb));
        border-color: rgb(var(--bs-primary-rgb));
        background-color: transparent;
    }

    .btn-form-action.btn-outline-primary-draft:hover {
        background-color: rgba(var(--bs-primary-rgb), 0.05);
        border-color: rgb(var(--bs-primary-rgb));
        color: rgb(var(--bs-primary-rgb));
    }

    .btn-form-action.btn-light-cancel {
        background-color: #f1f3f5;
        border-color: #e0e0e0;
        color: var(--bs-secondary-rgb);
    }

    .btn-form-action.btn-light-cancel:hover {
        background-color: #e9ecef;
        border-color: #ced4da;
        color: var(--bs-dark-rgb);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-lg-4 px-xl-5 py-4">

    <div class="page-header">
        <h1 class="page-title h2 mb-0">
            <i class="fas fa-file-signature"></i>Initiate New Tender
        </h1>
        <a href="{{ route('initiatetender.index') }}" class="btn btn-back-to-list-header">
            <i class="fas fa-chevron-left"></i> Back to Tenders List
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 card-title-icon">
                <i class="fas fa-pencil-alt"></i>Tender Initiation Form
            </h5>
        </div>
        <div class="card-body p-lg-5 p-md-4 p-3">
            {{--
                Assuming your store route is named 'initiatetender.store'.
                Adjust if your route name is different.
            --}}
            <form method="POST" action="{{ route('initiatetender.store') }}" id="createTenderForm" enctype="multipart/form-data">
                @csrf

                <h6 class="form-section-title">1. Basic Information</h6>
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <label for="title" class="form-label">Tender Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" placeholder="e.g., Supply of Stationery for FY 2025/2026" value="{{ old('title') }}" required>
                        @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Tender Type <span class="text-danger">*</span></label>
                        <div class="pt-1">
                            {{--
                                Assuming \App\Enums\TenderTypeEnum exists and has cases() and displayName() methods.
                                And that the enum values are appropriate for form submission.
                                Defaulting to 'Open' tender type if no old input.
                            --}}
                            @php
                            $defaultTenderType = \App\Enums\TenderTypeEnum::Open->value; // Define a default
                            @endphp
                            @foreach(\App\Enums\TenderTypeEnum::cases() as $typeEnum)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input @error('tender_type') is-invalid @enderror"
                                    type="radio"
                                    name="tender_type"
                                    id="tender_type_{{ $typeEnum->value }}"
                                    value="{{ $typeEnum->value }}"
                                    {{ old('tender_type', $defaultTenderType) == $typeEnum->value ? 'checked' : '' }}
                                    required>
                                <label class="form-check-label" for="tender_type_{{ $typeEnum->value }}">{{ $typeEnum->displayName() }}</label>
                            </div>
                            @endforeach
                        </div>
                        @error('tender_type')
                        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                        <div id="tenderTypeHelp" class="form-text mt-2">
                            Open: Publicly advertised. Restricted: Invitation to pre-selected suppliers only.
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label for="tender_category_id" class="form-label">Tender Category <span class="text-danger">*</span></label>
                        {{--
                            Assuming \App\Enums\TenderCategoryEnum exists and has cases() and displayName() methods.
                        --}}
                        <select class="form-select @error('tender_category_id') is-invalid @enderror" id="tender_category_id" name="tender_category_id" required>
                            <option value="" disabled {{ !old('tender_category_id') ? 'selected' : '' }}>-- Select Category --</option>
                            @foreach(\App\Enums\TenderCategoryEnum::cases() as $categoryEnum)
                            <option value="{{ $categoryEnum->value }}" {{ old('tender_category_id') == $categoryEnum->value ? 'selected' : '' }}>
                                {{ $categoryEnum->displayName() }}
                            </option>
                            @endforeach
                        </select>
                        @error('tender_category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="related_pr_id" class="form-label">Related Purchase Requisition (PR) <small class="text-muted">(Optional)</small></label>
                        {{--
                            This select should be populated by data from your controller, e.g., $purchaseRequisitions.
                            Using TomSelect for enhanced UX.
                        --}}
                        <select class="tom-select @error('related_pr_id') is-invalid @enderror" id="related_pr_id" name="related_pr_id" placeholder="Search and select a PR...">
                            <option value="">-- Select or Search PR --</option>
                            {{-- Example: Replace with dynamic options from your controller --}}
                            {{-- @foreach($purchaseRequisitions ?? [] as $pr)
                                <option value="{{ $pr->id }}" {{ old('related_pr_id') == $pr->id ? 'selected' : '' }}>{{ $pr->pr_number }} - {{ $pr->title }}</option>
                            @endforeach --}}
                            <option value="PR/2025/001" {{ old('related_pr_id') == 'PR/2025/001' ? 'selected' : '' }}>PR/2025/001 - Office Supplies</option>
                            <option value="PR/2025/002" {{ old('related_pr_id') == 'PR/2025/002' ? 'selected' : '' }}>PR/2025/002 - IT Equipment</option>
                        </select>
                        @error('related_pr_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text mt-1">Linking a PR can pre-fill some tender details.</div>
                    </div>
                </div>

                <h6 class="form-section-title">2. Scope, Instructions & Timelines</h6>
                <div class="mb-4">
                    <label for="scope_of_work" class="form-label">Scope of Work / Description <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('scope_of_work') is-invalid @enderror" id="scope_of_work" name="scope_of_work" rows="5" placeholder="Provide a detailed description of the goods, services, or works required." required>{{ old('scope_of_work') }}</textarea>
                    @error('scope_of_work')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="instructions_to_bidders" class="form-label">Instructions to Bidders</label>
                    <textarea class="form-control @error('instructions_to_bidders') is-invalid @enderror" id="instructions_to_bidders" name="instructions_to_bidders" rows="5" placeholder="e.g., Submission format, evaluation criteria summary, contact for clarifications.">{{ old('instructions_to_bidders') }}</textarea>
                    @error('instructions_to_bidders')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="submission_deadline" class="form-label">Submission Deadline <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('submission_deadline') is-invalid @enderror" id="submission_deadline" name="submission_deadline" value="{{ old('submission_deadline') }}" required>
                        @error('submission_deadline')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="opening_date" class="form-label">Tender Opening Date <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('opening_date') is-invalid @enderror" id="opening_date" name="opening_date" value="{{ old('opening_date') }}" required>
                        @error('opening_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <h6 class="form-section-title">3. Documents & Supplier Selection</h6>
                <div class="mb-4">
                    <label for="tender_documents" class="form-label">Attach Tender Documents <small class="text-muted">(e.g., TOR, Specifications, Drawings)</small></label>
                    <input class="form-control @error('tender_documents.*') is-invalid @enderror @error('tender_documents') is-invalid @enderror" type="file" id="tender_documents" name="tender_documents[]" multiple>
                    @error('tender_documents.*')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @error('tender_documents')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text mt-1">You can upload multiple files. Max file size: 5MB each. Allowed types: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG.</div>
                </div>

                <div class="mb-4" id="restrictedSuppliersSection" style="display: {{ old('tender_type', $defaultTenderType) == \App\Enums\TenderTypeEnum::Restricted->value ? 'block' : 'none' }};">
                    <label for="supplier_ids" class="form-label">Invite Suppliers <span id="suppliers_required_asterisk" class="text-danger" style="display:none;">*</span> <small class="text-muted">(For Restricted Tenders)</small></label>
                    {{--
                        This select should be populated by data from your controller, e.g., $suppliers.
                        Using TomSelect for enhanced UX.
                    --}}
                    <select class="tom-select-multiple @error('supplier_ids') is-invalid @enderror @error('supplier_ids.*') is-invalid @enderror" id="supplier_ids" name="supplier_ids[]" multiple placeholder="Search and select suppliers...">
                        {{-- Example: Replace with dynamic options from your controller --}}
                        {{-- @foreach($suppliers ?? [] as $supplier)
                            <option value="{{ $supplier->id }}" {{ in_array($supplier->id, old('supplier_ids', [])) ? 'selected' : '' }}>{{ $supplier->name }} ({{ $supplier->contact_email }})</option>
                        @endforeach --}}
                        <option value="S001" {{ in_array('S001', old('supplier_ids', [])) ? 'selected' : '' }}>Supplier A - Tech Supplies Ltd (contact@techsupplies.com)</option>
                        <option value="S002" {{ in_array('S002', old('supplier_ids', [])) ? 'selected' : '' }}>Supplier B - Nova Solutions (info@novasolutions.net)</option>
                        <option value="S003" {{ in_array('S003', old('supplier_ids', [])) ? 'selected' : '' }}>Supplier C - EquiBuild Ltd (tenders@equibuild.co.ke)</option>
                    </select>
                    @error('supplier_ids') {{-- For errors on the array itself (e.g., if it's required and empty) --}}
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('supplier_ids.*') {{-- For errors on individual items within the array --}}
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="action-buttons-toolbar d-flex justify-content-end align-items-center gap-2">
                    <a href="{{ route('initiatetender.index') }}" class="btn btn-light-cancel btn-form-action">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" name="action" value="draft" class="btn btn-outline-primary-draft btn-form-action">
                        <i class="fas fa-save"></i> Save Draft
                    </button>
                    <button type="submit" name="action" value="publish" class="btn btn-primary-publish btn-form-action px-lg-4">
                        <i class="fas fa-paper-plane"></i> Publish Tender
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const prSelectElement = document.getElementById('related_pr_id');
        if (prSelectElement) {
            new TomSelect(prSelectElement, {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        }

        const supplierSelectElement = document.getElementById('supplier_ids');
        if (supplierSelectElement) {
            new TomSelect(supplierSelectElement, {
                plugins: ['remove_button'],
                create: false,
                delimiter: ',',
                persist: false,
            });
        }

        const tenderTypeRadios = document.querySelectorAll('input[name="tender_type"]');
        const restrictedSuppliersSection = document.getElementById('restrictedSuppliersSection');
        const suppliersSelectInput = document.getElementById('supplier_ids'); // The original select element
        const suppliersRequiredAsterisk = document.getElementById('suppliers_required_asterisk');
        const restrictedTenderTypeValue = "{{ \App\Enums\TenderTypeEnum::Restricted->value }}";

        function toggleSuppliersSectionVisibility() {
            const selectedTypeRadio = document.querySelector('input[name="tender_type"]:checked');
            const isRestricted = selectedTypeRadio && selectedTypeRadio.value === restrictedTenderTypeValue;

            restrictedSuppliersSection.style.display = isRestricted ? 'block' : 'none';
            if (suppliersRequiredAsterisk) {
                suppliersRequiredAsterisk.style.display = isRestricted ? 'inline' : 'none';
            }


            if (suppliersSelectInput) {
                if (isRestricted) {
                    suppliersSelectInput.setAttribute('required', 'required');
                } else {
                    suppliersSelectInput.removeAttribute('required');
                }
            }
        }

        tenderTypeRadios.forEach(radio => radio.addEventListener('change', toggleSuppliersSectionVisibility));
        toggleSuppliersSectionVisibility();

        const today = new Date();
        const offset = today.getTimezoneOffset();
        const todayLocal = new Date(today.getTime() - (offset * 60 * 1000));
        const todayISO = todayLocal.toISOString().slice(0, 16);

        const submissionDeadlineInput = document.getElementById('submission_deadline');
        const openingDateInput = document.getElementById('opening_date');

        if (submissionDeadlineInput) {
            submissionDeadlineInput.min = todayISO;
        }
        if (openingDateInput) {
            openingDateInput.min = todayISO;
        }

        if (submissionDeadlineInput && openingDateInput) {
            submissionDeadlineInput.addEventListener('change', function() {
                if (this.value) {
                    openingDateInput.min = this.value;
                    if (openingDateInput.value && openingDateInput.value < this.value) {
                        openingDateInput.value = '';
                    }
                } else {
                    openingDateInput.min = todayISO;
                }
            });
        }

        const createTenderForm = document.getElementById('createTenderForm');
        if (createTenderForm) {
            createTenderForm.addEventListener('submit', function(event) {
                const submitter = event.submitter;
                const action = submitter && submitter.getAttribute('name') === 'action' ? submitter.value : null;

                if (action === 'publish') {
                    const selectedTenderTypeRadio = document.querySelector('input[name="tender_type"]:checked');
                    if (selectedTenderTypeRadio && selectedTenderTypeRadio.value === restrictedTenderTypeValue) {
                        const tomSelectInstance = suppliersSelectInput ? suppliersSelectInput.tomselect : null;
                        if (tomSelectInstance && tomSelectInstance.getValue().length === 0) {
                            alert('For Restricted Tenders, please select at least one supplier to invite.');
                            event.preventDefault();
                            if (tomSelectInstance.control_input) {
                                tomSelectInstance.focus();
                            }
                            return;
                        }
                    }

                    if (!confirm('Are you sure you want to PUBLISH this tender? This action may notify suppliers or make the tender public.')) {
                        event.preventDefault();
                    }
                }
            });
        }
    });
</script>
@endpush