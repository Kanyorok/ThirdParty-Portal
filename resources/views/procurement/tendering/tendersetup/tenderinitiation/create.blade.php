@extends('layouts.app')

@section('title', 'Tender Initiation Form')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        .card-tender-initiation {
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
        .form-label.fw-bold {
            color: #343a40;
        }
    </style>
@endpush

@section('content')
    <div class="container mt-4 mb-5">
        <div class="card card-tender-initiation">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-plus-circle me-2"></i>New Tender Initiation
                </h4>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('initiatetender.store') }}" method="POST" enctype="multipart/form-data" id="createTenderForm">
                    @csrf

                    {{-- Section 1: Basic Information --}}
                    <h5 class="mb-3 text-primary border-bottom pb-2">1. Basic Information</h5>

                    <div class="mb-3">
                        <label for="Title" class="form-label fw-bold">Tender Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('Title') is-invalid @enderror"
                               id="Title" name="Title" value="{{ old('Title') }}" required>
                        @error('Title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tender Type <span class="text-danger">*</span></label>
                        <div>
                            @foreach(\App\Enums\TenderTypeEnum::cases() as $type)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input @error('TenderType') is-invalid @enderror"
                                           type="radio" name="TenderType" id="type-{{ $type->value }}" {{-- Changed ID to be more specific --}}
                                           value="{{ $type->value }}"
                                           {{ old('TenderType') == $type->value ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="type-{{ $type->value }}">
                                        {{ $type->displayName() }} {{-- Assuming displayName() method exists --}}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('TenderType')
                        <div class="invalid-feedback d-block">{{ $message }}</div> {{-- Ensure d-block for radio errors --}}
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="TenderCategory" class="form-label fw-bold">Tender Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('TenderCategory') is-invalid @enderror"
                                    id="TenderCategory" name="TenderCategory" required>
                                <option value="" {{ old('TenderCategory') ? '' : 'selected' }} disabled>-- Select Category --</option>
                                @foreach(\App\Enums\TenderCategoryEnum::cases() as $category)
                                    <option value="{{ $category->value }}"
                                        {{ old('TenderCategory') == $category->value ? 'selected' : '' }}>
                                        {{ $category->displayName() }} {{-- Assuming displayName() method exists --}}
                                    </option>
                                @endforeach
                            </select>
                            @error('TenderCategory')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="RelatedPRID" class="form-label fw-bold">Related PR No <small>(Optional)</small></label>
                            <input type="number" class="form-control @error('RelatedPRID') is-invalid @enderror"
                                   id="RelatedPRID" name="RelatedPRID" value="{{ old('RelatedPRID') }}" placeholder="Enter PR number if applicable">
                            @error('RelatedPRID')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="ProcurementModeId" class="form-label fw-bold">Procurement Mode <span class="text-danger">*</span></label>
                        <select class="form-select @error('ProcurementModeId') is-invalid @enderror"
                                id="ProcurementModeId" name="ProcurementModeId" required>
                            <option value="" {{ old('ProcurementModeId') ? '' : 'selected' }} disabled>-- Select Mode --</option>
                            @foreach($procurementModes as $mode)
                                <option value="{{ $mode->id }}" {{ old('ProcurementModeId') == $mode->id ? 'selected' : '' }}>
                                    {{ $mode->Name }}
                                </option>
                            @endforeach
                        </select>
                        @error('ProcurementModeId')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Section 2: Scope & Financials --}}
                    <h5 class="mt-4 mb-3 text-primary border-bottom pb-2">2. Scope & Financials</h5>

                    <div class="mb-3">
                        <label for="ScopeOfWork" class="form-label fw-bold">Scope of Work</label>
                        <textarea class="form-control @error('ScopeOfWork') is-invalid @enderror"
                                  id="ScopeOfWork" name="ScopeOfWork" rows="3">{{ old('ScopeOfWork') }}</textarea>
                        @error('ScopeOfWork')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="EstimatedValue" class="form-label fw-bold">Estimated Value</label>
                            {{-- Removed * and required to align with nullable validation, add back if mandatory --}}
                            <input type="number" step="0.01" class="form-control @error('EstimatedValue') is-invalid @enderror"
                                   id="EstimatedValue" name="EstimatedValue" value="{{ old('EstimatedValue') }}">
                            @error('EstimatedValue')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="Currency" class="form-label fw-bold">Currency <span class="text-danger">*</span></label>
                            <select class="form-select @error('Currency') is-invalid @enderror"
                                    id="Currency" name="Currency" required>
                                <option value="" {{ old('Currency') ? '' : 'selected' }} disabled>-- Select Currency --</option>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->Id }}"
                                        {{ old('Currency') == $currency->Id ? 'selected' : '' }}> {{-- Corrected comparison --}}
                                        {{ $currency->Code }} ({{ $currency->Name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('Currency')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Section 3: Timeline & Instructions --}}
                    <h5 class="mt-4 mb-3 text-primary border-bottom pb-2">3. Timeline & Instructions</h5>

                    <div class="mb-3">
                        <label for="Instructions" class="form-label fw-bold">Instructions to Bidders</label>
                        <textarea class="form-control @error('Instructions') is-invalid @enderror"
                                  id="Instructions" name="Instructions" rows="3">{{ old('Instructions') }}</textarea>
                        @error('Instructions')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="StartDate" class="form-label fw-bold">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('StartDate') is-invalid @enderror"
                                   id="StartDate" name="StartDate" value="{{ old('StartDate', date('Y-m-d')) }}" required> {{-- Default to today --}}
                            @error('StartDate')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="SubmissionDeadline" class="form-label fw-bold">Submission Deadline <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('SubmissionDeadline') is-invalid @enderror"
                                   id="SubmissionDeadline" name="SubmissionDeadline" value="{{ old('SubmissionDeadline') }}" required>
                            @error('SubmissionDeadline')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="OpeningDate" class="form-label fw-bold">Opening Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('OpeningDate') is-invalid @enderror"
                                   id="OpeningDate" name="OpeningDate" value="{{ old('OpeningDate') }}" required>
                            @error('OpeningDate')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="Status" class="form-label fw-bold">Initial Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('Status') is-invalid @enderror"
                                id="Status" name="Status" required>
                            <option value="" {{ old('Status') ? '' : 'selected' }} disabled>-- Select Status --</option>
                            @foreach($statuses as $status) {{-- Assuming $statuses is passed from controller --}}
                            <option value="{{ $status->value }}"
                                {{ old('Status', \App\Enums\TenderStatusEnum::Draft->value) == $status->value ? 'selected' : '' }}>
                                {{-- Default to Draft --}}
                                {{ $status->displayName() }} {{-- Assuming displayName() method exists --}}
                            </option>
                            @endforeach
                        </select>
                        @error('Status')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Section 4: Documents & Suppliers --}}
                    <h5 class="mt-4 mb-3 text-primary border-bottom pb-2">4. Documents & Suppliers</h5>

                    <div class="mb-3">
                        <label for="tender_documents" class="form-label fw-bold">Attach Tender Documents</label>
                        <input class="form-control @error('tender_documents') is-invalid @enderror @error('tender_documents.*') is-invalid @enderror"
                               type="file" id="tender_documents" name="tender_documents[]" multiple> {{-- Corrected name --}}
                        <small class="form-text text-muted">You can select multiple files (PDF, DOC, XLS, etc. Max 20MB per file).</small>
                        @if($errors->has('tender_documents') || $errors->has('tender_documents.*'))
                            <div class="invalid-feedback d-block">
                                @foreach($errors->get('tender_documents') as $msg)
                                    <div>{{ $msg }}</div>
                                @endforeach
                                @foreach($errors->get('tender_documents.*') as $fileErrors)
                                    @foreach($fileErrors as $msg)
                                        <div>{{ $msg }}</div>
                                    @endforeach
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="mb-3" id="restrictedSuppliersSection" style="display: none;">
                        <label for="suppliers" class="form-label fw-bold">Add Suppliers to Invite (for Restricted Tenders):</label>
                        <select class="form-select tom-select-suppliers @error('suppliers') is-invalid @enderror" id="suppliers" name="suppliers[]" multiple>
                            {{-- $allSuppliers should be passed from controller for this to work --}}
                            @if(isset($allSuppliers))
                                @foreach($allSuppliers as $supplier)
                                    <option value="{{ $supplier->Id }}"
                                        {{ in_array($supplier->Id, old('suppliers', [])) ? 'selected' : '' }}>
                                        {{ $supplier->Name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <small class="form-text text-muted">Select one or more suppliers if this is a restricted tender.</small>
                        @error('suppliers') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        @error('suppliers.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>


                    <div class="d-flex justify-content-end gap-2 mt-5 border-top pt-4">
                        <a href="{{ route('initiatetender.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-outline-primary" name="action" value="draft">
                            <i class="fas fa-save me-1"></i> Save as Draft
                        </button>
                        <button type="submit" class="btn btn-primary" name="action" value="publish">
                            <i class="fas fa-paper-plane me-1"></i> Publish Tender
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
            // Initialize TomSelect for dropdowns
            ['TenderCategory', 'ProcurementModeId', 'Currency', 'Status'].forEach(id => {
                if (document.getElementById(id)) {
                    new TomSelect('#' + id, { create: false, sortField: { field: "text", direction: "asc" } });
                }
            });

            if (document.getElementById('suppliers')) {
                new TomSelect('#suppliers', {
                    plugins: ['remove_button'],
                    create: false,
                    placeholder: 'Select or search suppliers...'
                });
            }


            const tenderTypeRadios = document.querySelectorAll('input[name="TenderType"]');
            const supplierSection = document.getElementById('restrictedSuppliersSection');
            const form = document.getElementById('createTenderForm');

            function toggleSupplierSection() {
                if (!supplierSection) return;
                const selectedTypeRadio = document.querySelector('input[name="TenderType"]:checked');
                if (selectedTypeRadio) {
                    supplierSection.style.display = (selectedTypeRadio.value === '{{ \App\Enums\TenderTypeEnum::Restricted->value }}') ? 'block' : 'none';
                } else {
                    supplierSection.style.display = 'none';
                }
            }

            tenderTypeRadios.forEach(radio => {
                radio.addEventListener('change', toggleSupplierSection);
            });

            // Initial check on page load
            toggleSupplierSection();

            if (form) {
                form.addEventListener('submit', function(e) {
                    this.querySelectorAll('button[type="submit"]').forEach(btn => {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
                    });
                });
            }

            const startDateInput = document.getElementById('StartDate');
            const submissionDeadlineInput = document.getElementById('SubmissionDeadline');
            const openingDateInput = document.getElementById('OpeningDate');

            if(startDateInput) {
                startDateInput.addEventListener('change', function() {
                    if (submissionDeadlineInput) {
                        submissionDeadlineInput.min = this.value + 'T00:00';
                        if (submissionDeadlineInput.value && submissionDeadlineInput.value < submissionDeadlineInput.min) {
                        }
                    }
                });
                if(startDateInput.value) startDateInput.dispatchEvent(new Event('change'));
            }


            if(submissionDeadlineInput) {
                submissionDeadlineInput.addEventListener('change', function() {
                    if (openingDateInput) {
                        openingDateInput.min = this.value;
                        if (openingDateInput.value && openingDateInput.value < this.value) {
                        }
                    }
                });
                if(submissionDeadlineInput.value) submissionDeadlineInput.dispatchEvent(new Event('change'));
            }
        });
    </script>
@endpush
