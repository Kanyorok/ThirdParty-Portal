@extends('layouts.app')

@section('title', 'Create Medical Funds Packages')

@section('content')
<div class="container-fluid px-3 py-4">
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex align-items-center gap-3 mb-4">
        <div>
          <h1 class="mb-1 fw-bold text-dark">Create Package</h1>
          <p class="text-muted small mb-0">Add a new coverage package to <strong>{{ $medical_fund->FundName }}</strong></p>
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

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom border-light py-4">
      <h5 class="mb-0 fw-bold text-dark">Package Details</h5>
    </div>
    <div class="card-body p-4">
      <form action="{{ route('bancassurance.medicalfunds.packages.store', ['medical_fund' => $medical_fund->Id]) }}" method="POST" id="pkgForm">
        @csrf

        <div class="row g-4">
          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark">Package Name <span class="text-danger">*</span></label>
            <input type="text" name="Name" class="form-control form-control-lg border-light" 
                   value="{{ old('Name') }}" placeholder="e.g., Basic Coverage, Premium Plus" required>
            <small class="text-muted d-block mt-1">Choose a descriptive name for this package</small>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark">Premium <span class="text-danger">*</span></label>
            <div class="input-group input-group-lg">
              <span class="input-group-text bg-light border-light">KES</span>
              <input type="number" step="0.01" name="Premium" class="form-control border-light text-end" 
                     value="{{ old('Premium') }}" placeholder="0.00" required>
            </div>
            <small class="text-muted d-block mt-1">Monthly or annual premium amount</small>
          </div>
          <div class="col-md-3 d-flex flex-column justify-content-end">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="IsCompulsory" value="1" id="isComp" {{ old('IsCompulsory') ? 'checked' : '' }}>
              <label for="isComp" class="form-check-label fw-semibold">Compulsory Package</label>
              <small class="text-muted d-block mt-1 ms-0">Required for all beneficiaries</small>
            </div>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold text-dark">Coverage Summary</label>
            <input type="text" name="CoverageDescription" class="form-control form-control-lg border-light" 
                   value="{{ old('CoverageDescription') }}" placeholder="e.g., Inpatient 1M + Outpatient 50k">
            <small class="text-muted d-block mt-1">Brief overview of what this package covers</small>
          </div>
        </div>

        <hr class="my-5">

        @php
          $allCoverages = \App\Models\Insurance\Coverage::where('IsActive',1)->orderBy('Name')->get();
          $oldIds = collect(old('coverage_ids', []))->map(fn($v)=>(int)$v)->toArray();
        @endphp

        <div class="mb-4">
          <div class="d-flex align-items-center gap-2 mb-4">
            <h5 class="mb-0 fw-bold text-dark">Attach Coverages</h5>
            <span class="badge bg-light text-dark">{{ $allCoverages->count() }} available</span>
          </div>
          <p class="text-muted small mb-4">Select coverages to include in this package and configure their limits and terms</p>

          @if($allCoverages->count())
            <div class="row g-4">
              @foreach($allCoverages as $cov)
                @php
                  $checked = in_array($cov->Id, $oldIds);
                @endphp
                <div class="col-lg-6 mb-2">
                  <div class="card border-light bg-light-subtle">
                    <div class="card-body p-3">
                      <div class="form-check mb-3">
                        <input class="form-check-input cov-box" type="checkbox"
                               name="coverage_ids[]" value="{{ $cov->Id }}" id="cov{{ $cov->Id }}"
                               {{ $checked ? 'checked' : '' }}>
                        <label class="form-check-label w-100 cursor-pointer" for="cov{{ $cov->Id }}">
                          <span class="fw-bold text-dark">{{ $cov->Name }}</span>
                          <span class="small text-muted d-block mt-1">{{ $cov->Description }}</span>
                        </label>
                      </div>

                      <div class="coverage-config" data-for="{{ $cov->Id }}" style="{{ $checked ? '' : 'display:none' }}">
                        <hr class="my-2">
                        <div class="row gx-2 mt-3">
                          <div class="col-6">
                            <label class="form-label small fw-semibold text-dark mb-1">Annual Limit (KES)</label>
                            <input type="number" step="0.01" class="form-control form-control-sm border-light"
                                   name="coverage[AnnualLimit][{{ $cov->Id }}]"
                                   value="{{ old('coverage.AnnualLimit.'.$cov->Id) }}"
                                   placeholder="0.00">
                          </div>
                          <div class="col-6">
                            <label class="form-label small fw-semibold text-dark mb-1">Per-Visit Limit (KES)</label>
                            <input type="number" step="0.01" class="form-control form-control-sm border-light"
                                   name="coverage[PerVisitLimit][{{ $cov->Id }}]"
                                   value="{{ old('coverage.PerVisitLimit.'.$cov->Id) }}"
                                   placeholder="0.00">
                          </div>
                          <div class="col-6 mt-2">
                            <label class="form-label small fw-semibold text-dark mb-1">Waiting Period (days)</label>
                            <input type="number" min="0" class="form-control form-control-sm border-light"
                                   name="coverage[WaitingPeriod][{{ $cov->Id }}]"
                                   value="{{ old('coverage.WaitingPeriod.'.$cov->Id) }}"
                                   placeholder="0">
                          </div>
                          <div class="col-6 mt-2">
                            <label class="form-label small fw-semibold text-dark mb-1">Scope</label>
                            <select class="form-select form-select-sm border-light"
                                    name="coverage[Scope][{{ $cov->ID }}]">
                              @php $scope = old('coverage.Scope.'.$cov->ID,'PerBeneficiary'); @endphp
                              <option value="PerBeneficiary" {{ $scope==='PerBeneficiary'?'selected':'' }}>Per Beneficiary</option>
                              <option value="PerFamily"      {{ $scope==='PerFamily'?'selected':'' }}>Per Family</option>
                            </select>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
              <strong>⚠️ No Coverage Options Available</strong>
              <p class="mb-0 mt-2">Please ensure the coverage catalog is populated first. Contact your administrator to add coverages.</p>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
        </div>

        <div class="mt-5 pt-4 border-top border-light d-flex justify-content-between align-items-center">
          <a href="{{ route('bancassurance.medicalfunds.packages.index', ['medical_fund' => $medical_fund->Id]) }}" class="btn btn-outline-secondary btn-lg">
            <i class="bi bi-x-lg me-2"></i>Cancel
          </a>
          <button class="btn btn-primary btn-lg" type="submit">
            <i class="bi bi-check-lg me-2"></i>Save Package
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  .bg-light-subtle {
    background-color: rgba(0, 0, 0, 0.02);
  }
  .cursor-pointer {
    cursor: pointer;
  }
  .coverage-config {
    transition: all 0.3s ease;
  }
  
  /* Ensure all form controls have visible borders */
  .form-control,
  .form-select,
  textarea.form-control,
  input[type="text"].form-control,
  input[type="number"].form-control,
  input[type="date"].form-control,
  input[type="email"].form-control,
  .input-group-text {
    background-color: #ffffff !important;
    border: 1.5px solid #6b7280 !important;
    border-radius: 0.375rem !important;
    color: #1f2937 !important;
    box-shadow: none !important;
    padding: 0.5rem 0.75rem !important;
    font-size: 1rem !important;
    line-height: 1.5 !important;
  }
  
  /* Placeholder styling */
  .form-control::placeholder,
  textarea.form-control::placeholder {
    color: #9ca3af !important;
    opacity: 1 !important;
  }
  
  /* Focus state for better visibility */
  .form-control:focus,
  .form-select:focus,
  textarea.form-control:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    background-color: #ffffff !important;
    color: #1f2937 !important;
    outline: none !important;
  }
  
  /* Disabled state */
  .form-control:disabled,
  .form-select:disabled {
    background-color: #f3f4f6 !important;
    border-color: #d1d5db !important;
    color: #6b7280 !important;
    opacity: 0.6;
  }
  
  /* Input group text consistency */
  .input-group-text {
    background-color: #f9fafb !important;
    border-color: #6b7280 !important;
    color: #4b5563 !important;
  }
  
  /* Small form controls */
  .form-control-sm,
  .form-select-sm {
    border: 1.5px solid #6b7280 !important;
    border-radius: 0.25rem !important;
  }
  
  /* Large form controls */
  .form-control-lg,
  .form-select-lg {
    border: 2px solid #6b7280 !important;
    border-radius: 0.5rem !important;
    padding: 0.75rem 1rem !important;
  }
</style>

<script>
(function(){
  document.querySelectorAll('.cov-box').forEach(function(box){
    box.addEventListener('change', function(){
      const id = this.value;
      const panel = document.querySelector('.coverage-config[data-for="'+id+'"]');
      if(panel){ panel.style.display = this.checked ? '' : 'none'; }
    });
  });
})();
</script>
@endsection
