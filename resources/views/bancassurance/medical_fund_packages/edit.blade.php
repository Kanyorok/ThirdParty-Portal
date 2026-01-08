@extends('layouts.app')

@section('content')
<div class="container">
  <h4>Edit Package — {{ $medical_fund->FundName }}</h4>

  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  <div class="card"><div class="card-body">
    <form action="{{ route('bancassurance.packages.update', $package->Id) }}" method="POST" id="pkgEditForm">
      @csrf @method('PUT')

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Name *</label>
          <input type="text" name="Name" class="form-control" value="{{ old('Name',$package->Name) }}" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Premium *</label>
          <input type="number" step="0.01" name="Premium" class="form-control" value="{{ old('Premium',$package->Premium) }}" required>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="IsCompulsory" value="1" id="isComp"
              {{ old('IsCompulsory',$package->IsCompulsory) ? 'checked' : '' }}>
            <label for="isComp" class="form-check-label">Compulsory</label>
          </div>
        </div>
        <div class="col-12">
          <label class="form-label">Coverage Description</label>
          <input type="text" name="CoverageDescription" class="form-control" value="{{ old('CoverageDescription',$package->CoverageDescription) }}">
        </div>
      </div>

      @php
        $allCoverages = \App\Models\Insurance\Coverage::where('IsActive',1)->orderBy('Name')->get();
        $selectedMap  = $package->coverages->keyBy('Id'); // with ->pivot
      @endphp

      <hr class="my-4">
      <h6 class="mb-3">Attach Coverages</h6>

      @if($allCoverages->count())
        <div class="row">
          @foreach($allCoverages as $cov)
            @php
              $link   = $selectedMap->get($cov->Id);
              $checked = $link ? true : false;
              $ann   = old('coverage.AnnualLimit.'.$cov->Id, optional($link)->pivot->AnnualLimit ?? null);
              $pv    = old('coverage.PerVisitLimit.'.$cov->Id, optional($link)->pivot->PerVisitLimit ?? null);
              $wp    = old('coverage.WaitingPeriod.'.$cov->Id, optional($link)->pivot->WaitingPeriodDays ?? null);
              $scope = old('coverage.Scope.'.$cov->Id, optional($link)->pivot->Scope ?? 'PerBeneficiary');
            @endphp
            <div class="col-md-6 mb-3">
              <div class="form-check mb-1">
                <input class="form-check-input cov-box" type="checkbox"
                       name="coverage_ids[]" value="{{ $cov->Id }}" id="cov{{ $cov->Id }}"
                       {{ $checked ? 'checked' : '' }}>
                <label class="form-check-label" for="cov{{ $cov->Id }}">
                  <span class="fw-semibold">{{ $cov->Name }}</span>
                  <span class="small text-muted d-block">{{ $cov->Description }}</span>
                </label>
              </div>

              <div class="row gx-2 coverage-config" data-for="{{ $cov->Id }}" style="{{ $checked ? '' : 'display:none' }}">
                <div class="col-6">
                  <label class="form-label small mb-0">Annual Limit</label>
                  <input type="number" step="0.01" class="form-control form-control-sm"
                         name="coverage[AnnualLimit][{{ $cov->Id }}]" value="{{ $ann }}">
                </div>
                <div class="col-6">
                  <label class="form-label small mb-0">Per-Visit Limit</label>
                  <input type="number" step="0.01" class="form-control form-control-sm"
                         name="coverage[PerVisitLimit][{{ $cov->Id }}]" value="{{ $pv }}">
                </div>
                <div class="col-6 mt-2">
                  <label class="form-label small mb-0">Waiting (days)</label>
                  <input type="number" min="0" class="form-control form-control-sm"
                         name="coverage[WaitingPeriod][{{ $cov->Id }}]" value="{{ $wp }}">
                </div>
                <div class="col-6 mt-2">
                  <label class="form-label small mb-0">Scope</label>
                  <select class="form-select form-select-sm"
                          name="coverage[Scope][{{ $cov->Id }}]">
                    <option value="PerBeneficiary" {{ $scope==='PerBeneficiary'?'selected':'' }}>Per Beneficiary</option>
                    <option value="PerFamily"      {{ $scope==='PerFamily'?'selected':'' }}>Per Family</option>
                  </select>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="alert alert-warning">No coverages in the catalog. Please seed <code>t_Coverages</code> first.</div>
      @endif

      <div class="mt-3 d-flex justify-content-between align-items-center">
        <a href="{{ route('bancassurance.medicalfunds.packages.index', ['medical_fund' => $medical_fund->Id]) }}" class="btn btn-outline-secondary">Back</a>
        <button class="btn btn-primary" type="submit">Update</button>
      </div>
    </form>
  </div></div>
</div>

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

<style>
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
@endsection
