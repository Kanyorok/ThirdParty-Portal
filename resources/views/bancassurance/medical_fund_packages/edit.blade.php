@extends('layouts.app')

@section('content')
<div class="container">
  <h4>Edit Package — {{ $medical_fund->FundName }}</h4>

  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  <div class="card"><div class="card-body">
    <form action="{{ route('bancassurance.packages.update', $package->ID) }}" method="POST" id="pkgEditForm">
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
        $selectedMap  = $package->coverages->keyBy('ID'); // with ->pivot
      @endphp

      <hr class="my-4">
      <h6 class="mb-3">Attach Coverages</h6>

      @if($allCoverages->count())
        <div class="row">
          @foreach($allCoverages as $cov)
            @php
              $link   = $selectedMap->get($cov->ID);
              $checked = $link ? true : false;
              $ann   = old('coverage.AnnualLimit.'.$cov->ID, $link->pivot->AnnualLimit ?? null);
              $pv    = old('coverage.PerVisitLimit.'.$cov->ID, $link->pivot->PerVisitLimit ?? null);
              $wp    = old('coverage.WaitingPeriod.'.$cov->ID, $link->pivot->WaitingPeriodDays ?? null);
              $scope = old('coverage.Scope.'.$cov->ID, $link->pivot->Scope ?? 'PerBeneficiary');
            @endphp
            <div class="col-md-6 mb-3">
              <div class="form-check mb-1">
                <input class="form-check-input cov-box" type="checkbox"
                       name="coverage_ids[]" value="{{ $cov->ID }}" id="cov{{ $cov->ID }}"
                       {{ $checked ? 'checked' : '' }}>
                <label class="form-check-label" for="cov{{ $cov->ID }}">
                  <span class="fw-semibold">{{ $cov->Name }}</span>
                  <span class="small text-muted d-block">{{ $cov->Description }}</span>
                </label>
              </div>

              <div class="row gx-2 coverage-config" data-for="{{ $cov->ID }}" style="{{ $checked ? '' : 'display:none' }}">
                <div class="col-6">
                  <label class="form-label small mb-0">Annual Limit</label>
                  <input type="number" step="0.01" class="form-control form-control-sm"
                         name="coverage[AnnualLimit][{{ $cov->ID }}]" value="{{ $ann }}">
                </div>
                <div class="col-6">
                  <label class="form-label small mb-0">Per-Visit Limit</label>
                  <input type="number" step="0.01" class="form-control form-control-sm"
                         name="coverage[PerVisitLimit][{{ $cov->ID }}]" value="{{ $pv }}">
                </div>
                <div class="col-6 mt-2">
                  <label class="form-label small mb-0">Waiting (days)</label>
                  <input type="number" min="0" class="form-control form-control-sm"
                         name="coverage[WaitingPeriod][{{ $cov->ID }}]" value="{{ $wp }}">
                </div>
                <div class="col-6 mt-2">
                  <label class="form-label small mb-0">Scope</label>
                  <select class="form-select form-select-sm"
                          name="coverage[Scope][{{ $cov->ID }}]">
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
        <button class="btn btn-primary">Update</button>
        <a href="{{ route('bancassurance.medicalfunds.packages.index', ['medical_fund' => $medical_fund->ID]) }}" class="btn btn-outline-secondary">Back</a>
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
