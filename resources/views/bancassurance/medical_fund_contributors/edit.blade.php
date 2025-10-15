@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Edit Contributor — {{ $medical_fund->FundName }}</h4>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any())
      <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card">
      <div class="card-body">
        <form action="{{ route('bancassurance.contributors.update', $contributor->ID) }}" method="POST" id="contributorEditForm">
          @csrf @method('PUT')

          <div class="row g-3">

            <div class="col-md-4">
              <label class="form-label">Contributor No</label>
              <input type="text" name="ContributorNo" class="form-control" value="{{ old('ContributorNo',$contributor->ContributorNo) }}">
            </div>

            <div class="col-md-8">
              <label class="form-label">Full Name *</label>
              <input type="text" name="FullName" class="form-control" value="{{ old('FullName',$contributor->FullName) }}" required>
            </div>

            <div class="col-md-4">
              <label class="form-label">Email</label>
              <input type="email" name="Email" class="form-control" value="{{ old('Email',$contributor->Email) }}">
            </div>

            <div class="col-md-4">
              <label class="form-label">Phone</label>
              <input type="text" name="Phone" class="form-control" value="{{ old('Phone',$contributor->Phone) }}">
            </div>

            <div class="col-md-2">
              <label class="form-label">Effective From</label>
              <input type="date" name="EffectiveFrom" class="form-control" value="{{ old('EffectiveFrom', optional($contributor->EffectiveFrom)->format('Y-m-d')) }}">
            </div>

            <div class="col-md-2">
              <label class="form-label">Effective To</label>
              <input type="date" name="EffectiveTo" class="form-control" value="{{ old('EffectiveTo', optional($contributor->EffectiveTo)->format('Y-m-d')) }}">
            </div>

            <div class="col-md-3">
              <label class="form-label">Status</label>
              <select name="Status" class="form-select">
                @foreach(['Active','Suspended','Closed'] as $st)
                  <option value="{{ $st }}" @selected(old('Status',$contributor->Status)===$st)>{{ $st }}</option>
                @endforeach
              </select>
            </div>

            @php
              $packages = $medical_fund->packages()->orderBy('Name')->get();
              $current  = $contributor->packages()->pluck('t_MedicalFundPackages.ID')->toArray();
              $oldSel   = collect(old('package_ids', $current))->map(fn($v)=>(int)$v)->toArray();
            @endphp

            @if($packages->count())
            <div class="col-12">
              <hr class="mt-1 mb-2">
              <label class="form-label">Packages</label>
              <div class="row">
                @foreach($packages as $pkg)
                  @php
                    $checked = $pkg->IsCompulsory || in_array($pkg->ID, $oldSel);
                  @endphp
                  <div class="col-md-6">
                    <div class="form-check mb-2">
                      <input
                        class="form-check-input pkg-box"
                        type="checkbox"
                        name="package_ids[]"
                        value="{{ $pkg->ID }}"
                        id="pkg{{ $pkg->ID }}"
                        data-price="{{ (float)$pkg->Premium }}"
                        {{ $checked ? 'checked' : '' }}
                        {{ $pkg->IsCompulsory ? 'disabled' : '' }}
                      >
                      <label class="form-check-label" for="pkg{{ $pkg->ID }}">
                        <span class="fw-semibold">{{ $pkg->Name }}</span>
                        — <span class="text-nowrap">{{ number_format((float)$pkg->Premium,2) }}</span>
                        @if($pkg->IsCompulsory)
                          <span class="badge bg-warning text-dark ms-1">Compulsory</span>
                        @endif
                        @if($pkg->CoverageDescription)
                          <div class="small text-muted">{{ $pkg->CoverageDescription }}</div>
                        @endif
                      </label>
                    </div>

                    @if($pkg->IsCompulsory)
                      <!-- ensure disabled checkbox value still posts -->
                      <input type="hidden" name="package_ids[]" value="{{ $pkg->ID }}">
                    @endif
                  </div>
                @endforeach
              </div>

              <div class="mt-2">
                <span class="small text-muted">Selected Packages Total:</span>
                <span class="fw-bold" id="pkgTotal">0.00</span>
              </div>
            </div>
            @endif

          </div>

          <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('bancassurance.medicalfunds.contributors.index', ['medical_fund' => $medical_fund->ID]) }}" class="btn btn-outline-secondary">Back</a>
          </div>

        </form>
      </div>
    </div>
</div>

@if($packages->count())
<script>
(function(){
  function updateTotal(){
    let total = 0;
    document.querySelectorAll('.pkg-box').forEach(cb => {
      if (cb.checked || cb.disabled) {
        total += parseFloat(cb.getAttribute('data-price') || '0');
      }
    });
    document.getElementById('pkgTotal').textContent = total.toFixed(2);
  }
  document.querySelectorAll('.pkg-box').forEach(cb => {
    cb.addEventListener('change', updateTotal);
  });
  updateTotal();
})();
</script>
@endif
@endsection
