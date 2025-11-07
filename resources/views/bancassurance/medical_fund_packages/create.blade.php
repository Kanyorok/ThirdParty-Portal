@extends('layouts.app')

@section('title', 'Create Medical Funds Packages')

@section('content')
<div class="container">
  <h4>New Package — {{ $medical_fund->FundName }}</h4>

  @if($errors->any())
    <div class="alert alert-danger">
      <strong>Fix the following:</strong>
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <div class="card"><div class="card-body">
    <form action="{{ route('bancassurance.medicalfunds.packages.store', ['medical_fund' => $medical_fund->Id]) }}" method="POST" id="pkgForm">
      @csrf

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Name <span class="text-danger">*</span></label>
          <input type="text" name="Name" class="form-control" value="{{ old('Name') }}" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Premium <span class="text-danger">*</span></label>
          <input type="number" step="0.01" name="Premium" class="form-control" value="{{ old('Premium') }}" required>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="IsCompulsory" value="1" id="isComp" {{ old('IsCompulsory') ? 'checked' : '' }}>
            <label for="isComp" class="form-check-label">Compulsory</label>
          </div>
        </div>
        <div class="col-12">
          <label class="form-label">Coverage Description</label>
          <input type="text" name="CoverageDescription" class="form-control" value="{{ old('CoverageDescription') }}" placeholder="e.g., Inpatient 1M + Outpatient 50k">
        </div>
      </div>

      @php
        $allCoverages = \App\Models\Insurance\Coverage::where('IsActive',1)->orderBy('Name')->get();
        $oldIds = collect(old('coverage_ids', []))->map(fn($v)=>(int)$v)->toArray();
      @endphp

      <hr class="my-4">
      <h6 class="mb-3">Attach Coverages</h6>

      @if($allCoverages->count())
        <div class="row">
          @foreach($allCoverages as $cov)
            @php
              $checked = in_array($cov->Id, $oldIds);
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
                         name="coverage[AnnualLimit][{{ $cov->Id }}]"
                         value="{{ old('coverage.AnnualLimit.'.$cov->Id) }}">
                </div>
                <div class="col-6">
                  <label class="form-label small mb-0">Per-Visit Limit</label>
                  <input type="number" step="0.01" class="form-control form-control-sm"
                         name="coverage[PerVisitLimit][{{ $cov->Id }}]"
                         value="{{ old('coverage.PerVisitLimit.'.$cov->Id) }}">
                </div>
                <div class="col-6 mt-2">
                  <label class="form-label small mb-0">Waiting (days)</label>
                  <input type="number" min="0" class="form-control form-control-sm"
                         name="coverage[WaitingPeriod][{{ $cov->Id }}]"
                         value="{{ old('coverage.WaitingPeriod.'.$cov->Id) }}">
                </div>
                <div class="col-6 mt-2">
                  <label class="form-label small mb-0">Scope</label>
                  <select class="form-select form-select-sm"
                          name="coverage[Scope][{{ $cov->ID }}]">
                    @php $scope = old('coverage.Scope.'.$cov->ID,'PerBeneficiary'); @endphp
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

      <div class="mt-3 d-flex gap-2">
        <button class="btn btn-primary">Save</button>
        <a href="{{ route('bancassurance.medicalfunds.packages.index', ['medical_fund' => $medical_fund->Id]) }}" class="btn btn-outline-secondary">Cancel</a>
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
@endsection
