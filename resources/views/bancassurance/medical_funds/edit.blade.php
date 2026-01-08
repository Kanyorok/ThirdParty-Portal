@extends('layouts.app')

@section('title', 'Edit Medical Funds')

@section('content')
<div class="container-fluid px-3 py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div>
                    <h1 class="mb-1 fw-bold text-dark">Edit Medical Fund</h1>
                    <p class="text-muted small mb-0">Update fund information and manage related resources</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success')) 
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>✓ Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong class="me-2">⚠️ Validation Errors</strong>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom border-light py-4">
            <h5 class="mb-0 fw-bold text-dark">Fund Information</h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('bancassurance.medicalfunds.update', ['medical_fund' => $medical_fund->Id]) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Fund Name <span class="text-danger">*</span></label>
                        <input type="text" name="FundName" class="form-control form-control-lg" 
                               value="{{ old('FundName',$medical_fund->FundName) }}" placeholder="e.g., Employee Health Coverage" required>
                        <small class="text-muted d-block mt-1">Official name of the medical fund</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Provider <span class="text-danger">*</span></label>
                        <select name="ProviderId" class="form-select form-select-lg" required>
                            @foreach($providers as $p)
                                <option value="{{ $p->Id }}" @selected(old('ProviderId',$medical_fund->ProviderId)==$p->Id)>{{ $p->Name ?? '-' }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">Insurance provider offering this fund</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Coverage Type <span class="text-danger">*</span></label>
                        <select name="CoverageType" class="form-select form-select-lg" required>
                            <option value="">-- Select Coverage Type --</option>
                            @foreach ($coverageTypes as $ct)
                                <option value="{{ $ct->ID }}" @selected(old('CoverageType', $medical_fund->CoverageType) == $ct->ID)>
                                    {{ $ct->Description }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">Type of medical coverage provided</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Coverage Limit</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light">KES</span>
                            <input type="number" step="0.01" min="0" name="CoverageLimit" class="form-control text-end" 
                                   value="{{ old('CoverageLimit',$medical_fund->CoverageLimit) }}" placeholder="0.00" onblur="fixDecimal(this)">
                        </div>
                        <small class="text-muted d-block mt-1">Maximum coverage amount per beneficiary</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark">Description</label>
                        <textarea name="Description" class="form-control" rows="4" placeholder="Notes about the fund or any special conditions...">{{ old('Description',$medical_fund->Description) }}</textarea>
                        <small class="text-muted d-block mt-1">Additional information about this fund</small>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="isActive" {{ old('IsActive',$medical_fund->IsActive) ? 'checked':'' }}>
                            <label class="form-check-label fw-semibold" for="isActive">Active Fund</label>
                            <small class="text-muted d-block mt-1 ms-0">Enable this fund for new enrollments</small>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-top border-light d-flex justify-content-between align-items-center">
                    <a href="{{ route('bancassurance.medicalfunds.index') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-x-lg me-2"></i>Cancel
                    </a>
                    <button class="btn btn-primary btn-lg" type="submit">
                        <i class="bi bi-check-lg me-2"></i>Update Fund
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Related Resources Section -->
    <div class="row mt-4 g-3">
        <div class="col-12">
            <h5 class="fw-bold text-dark mb-3">Related Resources</h5>
        </div>

        <div class="col-md-4">
            <div class="card border-light h-100 shadow-sm hover-shadow">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                            <i class="bi bi-people text-primary fs-5"></i>
                        </div>
                        <div>
                            <h6 class="card-title fw-bold mb-0">Beneficiaries</h6>
                            <small class="text-muted">Manage fund members</small>
                        </div>
                    </div>
                    <p class="text-muted mb-3 small">View and manage all beneficiaries enrolled in this fund.</p>
                    <a class="btn btn-sm btn-primary"
                       href="{{ route('bancassurance.medicalfunds.beneficiaries.index', ['medical_fund' => $medical_fund->Id]) }}">
                       <i class="bi bi-arrow-right me-1"></i>Open
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-light h-100 shadow-sm hover-shadow">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-success bg-opacity-10 p-3 rounded-3">
                            <i class="bi bi-cash-coin text-success fs-5"></i>
                        </div>
                        <div>
                            <h6 class="card-title fw-bold mb-0">Contributions</h6>
                            <small class="text-muted">Track payments</small>
                        </div>
                    </div>
                    <p class="text-muted mb-3 small">Record, review and manage all fund contributions.</p>
                    <a class="btn btn-sm btn-success"
                       href="{{ route('bancassurance.medicalfunds.contributions.index', ['medical_fund' => $medical_fund->Id]) }}">
                       <i class="bi bi-arrow-right me-1"></i>Open
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-light h-100 shadow-sm hover-shadow">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-info bg-opacity-10 p-3 rounded-3">
                            <i class="bi bi-arrow-left-right text-info fs-5"></i>
                        </div>
                        <div>
                            <h6 class="card-title fw-bold mb-0">Disbursements</h6>
                            <small class="text-muted">Authorize claims</small>
                        </div>
                    </div>
                    <p class="text-muted mb-3 small">Authorize and track all fund disbursements.</p>
                    <a class="btn btn-sm btn-info"
                       href="{{ route('bancassurance.medicalfunds.disbursements.index', ['medical_fund' => $medical_fund->Id]) }}">
                       <i class="bi bi-arrow-right me-1"></i>Open
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-shadow {
        transition: box-shadow 0.3s ease, transform 0.3s ease;
    }
    .hover-shadow:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }
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
