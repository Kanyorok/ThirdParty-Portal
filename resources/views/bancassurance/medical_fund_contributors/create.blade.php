@extends('layouts.app')

@section('content')
<div class="container">
    <h4>New Contributor — {{ $medical_fund->FundName }}</h4>

    {{-- Validation Errors --}}
    @if($errors->any())
      <div class="alert alert-danger">
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    <div class="card">
      <div class="card-body">
        <form action="{{ route('bancassurance.medicalfunds.contributors.index', ['medical_fund' => $medical_fund->Id]) }}" method="POST" id="contributorForm">
          @csrf
          <div class="row g-3">

            {{-- Third Party --}}
            <div class="col-md-6">
              <label class="form-label">Third Party *</label>
              <select name="ThirdPartyId" class="form-select" required>
                <option value="">-- Select Third Party --</option>
                @foreach($thirdParties as $tp)
                  <option value="{{ $tp->Id }}" @selected(old('ThirdPartyId') == $tp->Id)>
                    {{ $tp->ThirdPartyName }}
                  </option>
                @endforeach
              </select>
            </div>

            {{-- Party ID (optional internal link to ERP person/org) --}}
            <div class="col-md-3">
              <label class="form-label">Party ID (optional)</label>
              <input type="number" name="PartyId" class="form-control" value="{{ old('PartyId') }}">
            </div>

            {{-- Contributor Number
            <div class="col-md-3">
              <label class="form-label">Contributor No</label>
              <input type="text" name="ContributorNo" class="form-control" value="{{ old('ContributorNo') }}" placeholder="HR/Payroll Ref">
            </div> --}}

            {{-- Effective Dates --}}
            <div class="col-md-3">
              <label class="form-label">Effective From</label>
              <input type="date" name="EffectiveFrom" class="form-control" value="{{ old('EffectiveFrom') }}">
            </div>

            <div class="col-md-3">
              <label class="form-label">Effective To</label>
              <input type="date" name="EffectiveTo" class="form-control" value="{{ old('EffectiveTo') }}">
            </div>

            {{-- Status --}}
            <div class="col-md-3">
              <label class="form-label">Status *</label>
              <select name="Status" class="form-select" required>
                <option value="">-- Select Status --</option>
                @foreach($statuses as $status)
                  <option value="{{ $status->ID }}" @selected(old('Status') == $status->ID)>
                    {{ $status->Description }}
                  </option>
                @endforeach
              </select>
            </div>

            {{-- Packages --}}
            @php
              $packages = $medical_fund->packages()->orderBy('Name')->get();
              $oldSelected = collect(old('package_ids', []))->map(fn($v)=>(int)$v)->toArray();
            @endphp
{{-- @dd($packages) --}}
            @if($packages->count())
              <div class="col-12">
                <hr class="mt-1 mb-2">
                <label class="form-label">Select Packages</label>
                <div class="row">
                  @foreach($packages as $pkg)
                    @php
                      $isChecked = $pkg->IsCompulsory || in_array($pkg->ID, $oldSelected);
                    @endphp
                    <div class="col-md-6">
                      <div class="form-check mb-2">
                        <input
                          class="form-check-input pkg-box"
                          type="checkbox"
                          name="package_ids[]"
                          value="{{ $pkg->Id }}"
                          id="pkg{{ $pkg->Id }}"
                          data-price="{{ (float)$pkg->Premium }}"
                          {{ $isChecked ? 'checked' : '' }}
                          {{ $pkg->IsCompulsory ? 'disabled' : '' }}
                        >
                        <label class="form-check-label" for="pkg{{ $pkg->Id }}">
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
                      {{-- Include hidden field for disabled (compulsory) packages --}}
                      @if($pkg->IsCompulsory)
                        <input type="hidden" name="package_ids[]" value="{{ $pkg->Id }}">
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
            <button class="btn btn-primary">Save Contributor</button>
            <a href="{{ route('bancassurance.medicalfunds.contributors.index', $medical_fund->Id) }}" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
</div>

{{-- Package Total Calculation --}}
@if($packages->count())
<script>
(function(){
  function updateTotal(){
    let total = 0;
    document.querySelectorAll('.pkg-box').forEach(cb => {
      if (cb.checked || cb.disabled) {
        total += parseFloat(cb.dataset.price || '0');
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
