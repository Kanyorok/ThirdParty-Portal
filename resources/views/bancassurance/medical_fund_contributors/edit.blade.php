@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 py-4">
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex align-items-center gap-3 mb-4">
        <div>
          <h1 class="mb-1 fw-bold text-dark">Edit Contributor</h1>
          <p class="text-muted small mb-0">Update contributor details for <strong>{{ $medical_fund->FundName }}</strong></p>
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
      <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom border-light py-4">
      <h5 class="mb-0 fw-bold text-dark">Contributor Information</h5>
    </div>
    <div class="card-body p-4">
      <form action="{{ route('bancassurance.contributors.update', $contributor->Id) }}" method="POST" id="contributorEditForm">
        @csrf @method('PUT')

        <div class="row g-4">
          <!-- 2-column consistent layout -->
          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark">Contributor No</label>
            <input type="text" name="ContributorNo" class="form-control form-control-lg" value="{{ old('ContributorNo',$contributor->ContributorNo) }}" readonly>
            <small class="text-muted d-block mt-1">System-generated unique number</small>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark">Full Name</label>
            <input type="text" name="FullName" class="form-control form-control-lg" value="{{ old('FullName',$contributor->thirdParty->ThirdPartyName) }}" readonly>
            <small class="text-muted d-block mt-1">Selected Third Party</small>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark">Email</label>
            <input type="email" class="form-control form-control-lg" value="{{ old('Email',$contributor->thirdParty->Email) }}" readonly>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark">Phone</label>
            <input type="text" class="form-control form-control-lg" value="{{ old('Phone',$contributor->thirdParty->Phone) }}" readonly>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark">Party ID <small class="text-muted">(optional)</small></label>
            <input type="number" name="PartyId" class="form-control form-control-lg" value="{{ old('PartyId',$contributor->PartyId) }}" placeholder="Enter Party ID">
            <small class="text-muted d-block mt-1">Internal ERP reference</small>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark">Status <span class="text-danger">*</span></label>
            <select name="Status" class="form-select form-select-lg" required>
              <option value="">-- Select Status --</option>
              @foreach($statuses as $status)
                <option value="{{ $status->ID }}" @selected((string) old('Status', (string)$contributor->Status) === (string) $status->ID)>{{ $status->Description }}</option>
              @endforeach
            </select>
            <small class="text-muted d-block mt-1">Current contributor status</small>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark">Effective From</label>
            <input type="date" name="EffectiveFrom" class="form-control form-control-lg" value="{{ old('EffectiveFrom', optional($contributor->EffectiveFrom)->format('Y-m-d')) }}">
            <small class="text-muted d-block mt-1">Start date of contribution</small>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark">Effective To</label>
            <input type="date" name="EffectiveTo" class="form-control form-control-lg" value="{{ old('EffectiveTo', optional($contributor->EffectiveTo)->format('Y-m-d')) }}">
            <small class="text-muted d-block mt-1">End date of contribution (optional)</small>
          </div>

          @php
            $packages = $medical_fund->packages()->orderBy('Name')->get();
            $current  = $contributor->packages()->pluck('t_MedicalFundPackages.ID')->toArray();
            $oldSel   = collect(old('package_ids', $current))->map(fn($v)=>(int)$v)->toArray();
          @endphp

          @if($packages->count())
            <div class="col-12">
              <hr class="my-4">
              <div class="mb-2">
                <h5 class="fw-bold text-dark mb-2">Select Coverage Packages</h5>
                <p class="text-muted small mb-4">Packages are set at fund level and shown here for reference</p>

                <div class="row g-3">
                  @foreach($packages as $pkg)
                    @php $checked = $pkg->IsCompulsory || in_array($pkg->ID, $oldSel); @endphp
                    <div class="col-md-6">
                      <div class="card package-card hover-shadow border-light bg-light-subtle {{ $checked ? 'border-primary selected' : '' }}">
                        <div class="card-body p-3">
                          <div class="form-check">
                            <input class="form-check-input pkg-box" type="checkbox" id="pkg{{ $pkg->ID }}" value="{{ $pkg->ID }}" data-price="{{ (float)$pkg->Premium }}" {{ $checked ? 'checked' : '' }} disabled>
                            <label class="form-check-label w-100" for="pkg{{ $pkg->ID }}">
                              <div class="d-flex justify-content-between align-items-start">
                                <div>
                                  <span class="fw-bold text-dark d-block">{{ $pkg->Name }}</span>
                                  @if($pkg->CoverageDescription)
                                    <span class="small text-muted d-block mt-1">{{ $pkg->CoverageDescription }}</span>
                                  @endif
                                </div>
                                <div class="text-end">
                                  <span class="fw-bold text-primary pkg-price">KES {{ number_format((float)$pkg->Premium,2) }}</span>
                                  @if($pkg->IsCompulsory)
                                    <span class="badge badge-compulsory bg-warning text-dark d-block mt-1">Compulsory</span>
                                  @endif
                                </div>
                              </div>
                            </label>
                          </div>
                          @if($checked)
                            <input type="hidden" name="package_ids[]" value="{{ $pkg->ID }}">
                          @endif
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>

                <div class="mt-4 p-3 summary-bar rounded-3">
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-semibold text-dark">Total Premium:</span>
                    <span class="fs-4 fw-bold text-primary">KES <span id="pkgTotal">0.00</span></span>
                  </div>
                </div>
              </div>
            </div>
          @endif
        </div>

        <div class="mt-5 pt-4 border-top border-light d-flex justify-content-between align-items-center">
          <a href="{{ route('bancassurance.medicalfunds.contributors.index', ['medical_fund' => $medical_fund->Id]) }}" class="btn btn-outline-secondary btn-lg">
            <i class="bi bi-arrow-left me-2"></i>Back
          </a>
          <button class="btn btn-primary btn-lg" type="submit">
            <i class="bi bi-check-lg me-2"></i>Update Contributor
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  /* Consistent, visible controls (match create) */
  .form-control,
  .form-select,
  textarea.form-control,
  .input-group-text {
    background-color: #ffffff;
    border: 1.5px solid #6b7280; /* gray-600 */
    border-radius: 0.5rem;      /* rounded */
    color: #111827;             /* gray-900 */
    box-shadow: none;
    padding: 0.625rem 0.75rem;  /* adequate padding */
  }
  .form-control::placeholder,
  textarea.form-control::placeholder { color: #9ca3af; opacity: 1; }
  .form-control:focus:not(.is-invalid):not(:disabled),
  .form-select:focus:not(.is-invalid):not(:disabled),
  textarea.form-control:focus:not(.is-invalid):not(:disabled) {
    border-color: #3b82f6; /* blue */
    box-shadow: 0 0 0 0.25rem rgba(59,130,246,0.15);
    background-color: #ffffff;
    color: #111827;
    outline: none;
  }
  .form-control:disabled,
  .form-select:disabled,
  textarea.form-control:disabled {
    background-color: #f3f4f6; /* gray-100 */
    border-color: #d1d5db; /* gray-300 */
    color: #6b7280;        /* gray-600 */
  }
  .input-group-text { background-color: #f9fafb; border-color: #6b7280; color: #4b5563; }
  .is-invalid { border-color: var(--bs-danger); box-shadow: 0 0 0 0.25rem rgba(220,53,69,0.08); }

  /* Package card enhancements */
  .hover-shadow { transition: box-shadow .2s ease, transform .2s ease; }
  .hover-shadow:hover { box-shadow: 0 .5rem 1rem rgba(0,0,0,.08); transform: translateY(-2px); }
  .package-card { border-radius: .75rem !important; }
  .package-card.selected { border-color: #3b82f6 !important; box-shadow: 0 0 0 .15rem rgba(59,130,246,.12); }
  .pkg-price { letter-spacing: .2px; }
  .badge-compulsory { font-weight: 600; }
  .summary-bar { background: rgba(59,130,246,.08); border: 1px solid rgba(59,130,246,.25); }
</style>

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
    const fmt = new Intl.NumberFormat('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('pkgTotal').textContent = fmt.format(total);
  }
  document.querySelectorAll('.pkg-box').forEach(cb => {
    cb.addEventListener('change', updateTotal);
  });
  updateTotal();
})();
</script>
@endif
@endsection
