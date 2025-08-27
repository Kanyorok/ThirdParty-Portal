@extends('layouts.app')

@section('content')
<div class="container">
  <h4>New Disbursement — {{ $medical_fund->FundName }}</h4>

  @if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  <div class="card">
    <div class="card-body">
      <form action="{{ route('bancassurance.medicalfunds.disbursements.store', $medical_fund->ID) }}" method="POST" id="disbForm">
        @csrf

        <div class="row g-3">
          {{-- Contributor --}}
          <div class="col-md-6">
            @if(isset($contributor) && $contributor)
              <input type="hidden" name="ContributorID" id="ContributorID" value="{{ $contributor->ID }}">
              <label class="form-label">Contributor</label>
              <input type="text" class="form-control" value="{{ $contributor->FullName }}" disabled>
              <div class="form-text">Locked to this contributor.</div>
            @else
              <label class="form-label">Contributor *</label>
              <select name="ContributorID" id="ContributorID" class="form-select" required>
                <option value="">-- select contributor --</option>
                @foreach($contributors as $c)
                  <option value="{{ $c->ID }}">{{ $c->FullName }}</option>
                @endforeach
              </select>
            @endif
          </div>

          {{-- Beneficiary --}}
          <div class="col-md-6">
            <label class="form-label">Beneficiary *</label>
            <select name="BeneficiaryID" id="BeneficiaryID" class="form-select" required>
              <option value="">-- select beneficiary --</option>
              @if(isset($beneficiaries) && $beneficiaries->count())
                @foreach($beneficiaries as $b)
                  <option value="{{ $b->ID }}">{{ $b->FullName }}</option>
                @endforeach
              @endif
            </select>
          </div>

          {{-- Coverage --}}
          <div class="col-md-6">
            <label class="form-label">Coverage *</label>
            <select name="CoverageID" id="CoverageID" class="form-select" required>
              <option value="">-- select coverage --</option>
              @if(isset($coverages) && $coverages->count())
                @foreach($coverages as $cov)
                  <option value="{{ $cov->ID }}"
                          data-annual="{{ $cov->pivot->AnnualLimit ?? $cov->AnnualLimit ?? '' }}"
                          data-pervisit="{{ $cov->pivot->PerVisitLimit ?? $cov->PerVisitLimit ?? '' }}"
                          data-wait="{{ $cov->pivot->WaitingPeriodDays ?? $cov->WaitingPeriodDays ?? '' }}"
                          data-scope="{{ $cov->pivot->Scope ?? $cov->Scope ?? 'PerBeneficiary' }}">
                    {{ $cov->Name }}
                  </option>
                @endforeach
              @endif
            </select>
            <div class="form-text">Only coverages allowed by the contributor’s active packages are listed.</div>
          </div>

          <div class="col-md-3">
            <label class="form-label">Date *</label>
            <input type="date" name="DisbursementDate" id="DisbursementDate" class="form-control"
                   value="{{ old('DisbursementDate', now()->toDateString()) }}" required>
          </div>

          <div class="col-md-3">
            <label class="form-label">Amount *</label>
            <input type="number" step="0.01" name="Amount" id="Amount" class="form-control"
                   value="{{ old('Amount') }}" required>
          </div>

          <div class="col-12">
            <label class="form-label">Purpose / Notes</label>
            <textarea name="Purpose" class="form-control" rows="2">{{ old('Purpose') }}</textarea>
          </div>
        </div>

        <hr class="my-3">

        {{-- Limits area --}}
        <div class="row g-3">
          <div class="col-md-4">
            <div class="border rounded p-2 h-100">
              <div class="small text-muted">Annual Limit</div>
              <div class="fs-5 fw-semibold" id="limitAnnual">—</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="border rounded p-2 h-100">
              <div class="small text-muted">Used YTD</div>
              <div class="fs-5 fw-semibold" id="limitUsed">—</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="border rounded p-2 h-100">
              <div class="small text-muted">Remaining</div>
              <div class="fs-5 fw-semibold" id="limitRemain">—</div>
            </div>
          </div>
          <div class="col-12">
            <div id="limitNote" class="text-warning small"></div>
          </div>
        </div>

        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-primary">Save</button>
          <a href="{{ route('bancassurance.medicalfunds.disbursements.index', $medical_fund->ID) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Scripts --}}
<script>
(function(){
  function fmt(n){ if(n===null || n==='' || isNaN(n)) return '—'; return parseFloat(n).toFixed(2); }
  function getVal(id){ const el = document.getElementById(id); return el ? el.value : ''; }

  const $contrib = document.getElementById('ContributorID');
  const $benef   = document.getElementById('BeneficiaryID');
  const $cov     = document.getElementById('CoverageID');
  const $amt     = document.getElementById('Amount');

  // Populate beneficiaries + coverages for a contributor
  async function loadOptionsForContributor(cid){
    if(!$benef || !$cov) return;

    $benef.innerHTML = '<option value="">-- select beneficiary --</option>';
    $cov.innerHTML   = '<option value="">-- select coverage --</option>';
    if(!cid) { refreshLimits(); return; }

    // ✅ Use the new named route with both medical_fund & contributor
    const url = `{{ route('bancassurance.medicalfunds.contributors.options', ['medical_fund' => $medical_fund->ID, 'contributor' => ':cid']) }}`
                  .replace(':cid', cid);

    try{
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const json = await res.json();
      if(!json.ok) { refreshLimits(); return; }

      (json.beneficiaries || []).forEach(b => {
        const o = document.createElement('option');
        o.value = b.ID; o.textContent = b.FullName;
        $benef.appendChild(o);
      });

      (json.coverages || []).forEach(cv => {
        const o = document.createElement('option');
        o.value = cv.ID; o.textContent = cv.Name;
        o.dataset.annual   = cv.AnnualLimit ?? '';
        o.dataset.pervisit = cv.PerVisitLimit ?? '';
        o.dataset.wait     = cv.WaitingPeriodDays ?? '';
        o.dataset.scope    = cv.Scope ?? 'PerBeneficiary';
        $cov.appendChild(o);
      });

      refreshLimits();
    }catch(e){
      console.warn('Failed loading contributor options', e);
      refreshLimits();
    }
  }

  // Compute remaining limits via backend endpoint
  async function refreshLimits(){
    const cid = getVal('ContributorID');
    const bid = getVal('BeneficiaryID');
    const cov = getVal('CoverageID');
    const dt  = getVal('DisbursementDate');

    const a = document.getElementById('limitAnnual');
    const u = document.getElementById('limitUsed');
    const r = document.getElementById('limitRemain');
    const note = document.getElementById('limitNote');

    function setDisplay(payload, err){
      a.textContent = payload ? fmt(payload.annual)    : '—';
      u.textContent = payload ? fmt(payload.used)      : '—';
      r.textContent = payload ? fmt(payload.remaining) : '—';
      note.textContent = payload
        ? (payload.waitingOk ? '' : (payload.waitingMsg || ''))
        : (err || '');
    }

    if(!cid || !cov){ setDisplay(null); return; }

    const url = `{{ route('bancassurance.coverage.remaining', ':cid') }}`
      .replace(':cid', cid)
      + `?coverage_id=${encodeURIComponent(cov)}`
      + (bid ? `&beneficiary_id=${encodeURIComponent(bid)}` : '')
      + (dt ? `&on_date=${encodeURIComponent(dt)}` : '');

    try{
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const data = await res.json();
      if(!data.ok){ setDisplay(null, data.message || 'Not available'); return; }

      setDisplay({
        annual: data.annual_limit,
        used: data.used_ytd,
        remaining: data.remaining,
        perVisit: data.per_visit_limit,
        waitingOk: data.waiting_ok,
        waitingMsg: data.waiting_message
      });

      if($amt){
        $amt.oninput = function(){
          const amt = parseFloat(this.value || '0');
          if(data.per_visit_limit && amt > data.per_visit_limit){
            note.textContent = `Warning: amount exceeds Per-Visit limit (${data.per_visit_limit}).`;
          } else {
            note.textContent = data.waiting_ok ? '' : (data.waiting_message || '');
          }
        };
      }
    }catch(e){
      setDisplay(null, 'Could not load limits.');
    }
  }

  // Bind events
  if($contrib){
    $contrib.addEventListener('change', async function(){
      await loadOptionsForContributor(this.value);
    });
  }
  ['BeneficiaryID','CoverageID','DisbursementDate'].forEach(id=>{
    const el = document.getElementById(id);
    if(el){ el.addEventListener('change', refreshLimits); }
  });

  // Initial boot
  @if(isset($contributor) && $contributor)
    refreshLimits(); // pre-locked contributor: compute limits immediately
  @endif
})();
</script>
@endsection
