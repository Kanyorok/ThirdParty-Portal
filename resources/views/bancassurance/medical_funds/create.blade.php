@extends('layouts.app')
@section('title', 'Create Medical Funds')

@section('content')
<div class="container-fluid px-3 py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div>
                    <h1 class="mb-1 fw-bold text-dark">Create Medical Fund</h1>
                    <p class="text-muted small mb-0">Set up a new medical insurance fund with basic information</p>
                </div>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong class="me-2">⚠️ Validation Errors</strong>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="alert alert-info alert-dismissible fade show border-start border-4 border-info" role="alert">
        <div class="d-flex gap-3">
            <div class="flex-shrink-0">
                <strong class="d-block mb-1">💡 Next Steps</strong>
            </div>
            <div class="flex-grow-1">
                <p class="mb-0 small">Detailed benefits and premiums are configured as <strong>Packages</strong> after you create the fund. Packages are built from your Coverage Catalog and can include compulsory or optional coverages.</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom border-light py-4">
            <h5 class="mb-0 fw-bold text-dark">Fund Information</h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('bancassurance.medicalfunds.store') }}" method="POST" id="fundCreateForm">
                @csrf
                <div class="row g-4">
                    <!-- Fund Name and Provider -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Fund Name <span class="text-danger">*</span></label>
                           <input type="text" name="FundName" class="form-control form-control-lg" 
                               value="{{ old('FundName') }}" placeholder="e.g., Employee Health Coverage" required>
                        <small class="text-muted d-block mt-1">Enter a unique name for this medical fund</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Provider <span class="text-danger">*</span></label>
                        <select name="ProviderId" class="form-select form-select-lg" required>
                            <option value="">-- Select a provider --</option>
                            @foreach($providers as $p)
                                <option value="{{ $p->Id }}" @selected(old('ProviderId')==$p->Id)>{{ $p->Name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">The insurance provider offering this fund. <a href="{{ route('bancassurance.medicalfunds.index') }}" class="link-primary">Manage providers</a></small>
                    </div>

                    <!-- Coverage Type and Limit -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Coverage Type <span class="text-danger">*</span></label>
                        <select name="CoverageType" class="form-select form-select-lg" required>
                            <option value="">-- Select Coverage Type --</option>
                            @foreach ($coverageTypes as $ct)
                                <option value="{{ $ct->ID }}" @selected(old('CoverageType')==$ct->ID)>
                                    {{ $ct->Description }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">Choose the type of coverage this fund provides</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Coverage Limit <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light">KES</span>
                            <input type="number" name="CoverageLimit" step="0.01" min="0"
                                value="{{ old('CoverageLimit') }}" 
                                class="form-control text-end" placeholder="0.00" 
                                   onblur="fixDecimal(this)" required>
                        </div>
                        <small class="text-muted d-block mt-1">Maximum coverage amount per beneficiary</small>
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark">Description</label>
                        <textarea name="Description" class="form-control" rows="4" 
                                  placeholder="Add notes about the fund, enrollment eligibility, special rules, or other important details...">{{ old('Description') }}</textarea>
                        <small class="text-muted d-block mt-1">Provide additional context about this fund</small>
                    </div>

                    <!-- Active Status -->
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="isActive" {{ old('IsActive',1) ? 'checked':'' }}>
                            <label class="form-check-label fw-semibold" for="isActive">
                                Active Fund
                            </label>
                            <small class="text-muted d-block mt-1 ms-0">Enable this fund for new enrollments</small>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-5 pt-4 border-top border-light d-flex justify-content-between align-items-center">
                    <a href="{{ route('bancassurance.medicalfunds.index') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-x-lg me-2"></i>Cancel
                    </a>
                    
                    <div class="d-flex gap-3">
                        <!-- Optional guided flows after create -->
                        <button class="btn btn-outline-primary btn-lg" name="next" value="contributors" type="submit">
                            <i class="bi bi-person-plus me-2"></i>Save &amp; Add Contributors
                        </button>
                        <button class="btn btn-outline-primary btn-lg" name="next" value="packages" type="submit">
                            <i class="bi bi-box-seam me-2"></i>Save &amp; Configure Packages
                        </button>
                        <button class="btn btn-primary btn-lg" name="next" value="none" type="submit">
                            <i class="bi bi-check-lg me-2"></i>Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Visibly distinct form controls across create & edit */
    .form-control,
    .form-select,
    textarea.form-control,
    .input-group-text {
        background-color: #ffffff;
        border: 1.5px solid #6b7280; /* gray-600 */
        border-radius: 0.5rem; /* rounded corners */
        color: #111827; /* gray-900 */
        box-shadow: none;
        padding: 0.625rem 0.75rem; /* adequate padding */
    }
    /* Placeholder */
    .form-control::placeholder,
    textarea.form-control::placeholder { color: #9ca3af; opacity: 1; }
    /* Focus state without breaking invalid */
    .form-control:focus:not(.is-invalid):not(:disabled),
    .form-select:focus:not(.is-invalid):not(:disabled),
    textarea.form-control:focus:not(.is-invalid):not(:disabled) {
        border-color: #3b82f6; /* blue */
        box-shadow: 0 0 0 0.25rem rgba(59,130,246,0.15);
        background-color: #ffffff;
        color: #111827;
        outline: none;
    }
    /* Disabled */
    .form-control:disabled,
    .form-select:disabled,
    textarea.form-control:disabled {
        background-color: #f3f4f6; /* gray-100 */
        border-color: #d1d5db; /* gray-300 */
        color: #6b7280; /* gray-600 */
    }
    /* Input group text consistency */
    .input-group-text { background-color: #f9fafb; border-color: #6b7280; color: #4b5563; }
    /* Keep invalid borders visible */
    .is-invalid { border-color: var(--bs-danger); box-shadow: 0 0 0 0.25rem rgba(220,53,69,0.08); }
</style>

<script>
function fixDecimal(input) {
    let val = parseFloat(input.value);
    if (!isNaN(val)) {
        input.value = val.toFixed(2);
    }
}
</script>
@endsection
