@extends('layouts.app')

@section('content')
<div class="container">
  <h4>New Disbursement — {{ $medical_fund->FundName }}</h4>

  @if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif
  <div class="card">
    <div class="card-body">
      <form action="{{ route('bancassurance.medicalfunds.disbursements.store', ['medical_fund' => $medical_fund->Id]) }}" method="POST" id="disbForm">
        @csrf
        <input type="hidden" name="FundId" value="{{ $medical_fund->Id }}">

        <div class="row g-3">
          {{-- Contributor --}}
          <div class="col-md-6">
            @if(isset($contributor) && $contributor)
              <input type="hidden" name="ContributorId" id="ContributorId" value="{{ $contributor->Id }}">
              <label class="form-label">Contributor</label>
              <input type="text" class="form-control" value="{{ $contributor->thirdParty->ThirdPartyName }}" disabled>
              <div class="form-text">Locked to this contributor.</div>
            @else
              <label class="form-label">Contributor *</label>
              <select name="ContributorId" id="ContributorId" class="form-select" required>
                <option value="">-- select contributor --</option>
                @foreach($contributors as $c)
                  <option value="{{ $c->Id }}">{{ $c->thirdParty->ThirdPartyName }}</option>
                @endforeach
              </select>
            @endif
          </div>

          {{-- Beneficiary --}}
          <div class="col-md-6">
            <label class="form-label">Beneficiary *</label>
            <select name="BeneficiaryId" id="BeneficiaryId" class="form-select" required>
              <option value="">-- select beneficiary --</option>
              @if(isset($beneficiaries) && $beneficiaries->count())
                @foreach($beneficiaries as $b)
                  @php
                    $bId = data_get($b, 'Id') ?? data_get($b, 'ID');
                    $bName = data_get($b, 'FullName') ?? data_get($b, 'Fullname') ?? $b->FullName ?? '';
                  @endphp
                  <option value="{{ $bId }}">{{ $bName }}</option>
                @endforeach
              @endif
            </select>
          </div>
          {{-- coverages debug removed --}}
          {{-- Coverage --}}
          <select name="CoverageId" id="CoverageId" class="form-select" required>
            <option value="">-- select coverage --</option>
            @if(isset($coverages) && $coverages->count())
              @foreach($coverages as $cov)
                @php
                  $covId = data_get($cov, 'Id') ?? data_get($cov, 'ID');
                  $covName = data_get($cov, 'Name') ?? ($cov->Name ?? '');
                  $annual = data_get($cov, 'pivot.AnnualLimit') ?? data_get($cov, 'AnnualLimit') ?? '';
                  $pervisit = data_get($cov, 'pivot.PerVisitLimit') ?? data_get($cov, 'PerVisitLimit') ?? '';
                  $wait = data_get($cov, 'pivot.WaitingPeriodDays') ?? data_get($cov, 'WaitingPeriodDays') ?? '';
                  $scope = data_get($cov, 'pivot.Scope') ?? data_get($cov, 'Scope') ?? 'PerBeneficiary';
                @endphp
                <option value="{{ $covId }}"
                        data-annual="{{ $annual }}"
                        data-pervisit="{{ $pervisit }}"
                        data-wait="{{ $wait }}"
                        data-scope="{{ $scope }}">
                  {{ $covName }}
                </option>
              @endforeach
            @endif
          </select>


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
          <a href="{{ route('bancassurance.medicalfunds.disbursements.index', ['medical_fund' => $medical_fund->Id]) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Scripts --}}
<script>
(function(){
  function fmt(n){ if(n===null || n==='' || isNaN(n)) return '—'; return parseFloat(n).toFixed(2); }
  function getEl(id){ return document.getElementById(id); }
  function getVal(id){ const el = getEl(id); return el ? el.value : ''; }

  const $contrib = getEl('ContributorId');
  const $benef   = getEl('BeneficiaryId');
  const $cov     = getEl('CoverageId');
  const $amt     = getEl('Amount');
  const $date    = getEl('DisbursementDate');

  const $annual  = getEl('limitAnnual');
  const $used    = getEl('limitUsed');
  const $remain  = getEl('limitRemain');
  const $note    = getEl('limitNote');

  async function loadOptionsForContributor(cid){
    if(!$benef || !$cov) return;

    if(!cid){
      $benef.innerHTML = '<option value="">-- select beneficiary --</option>';
      $cov.innerHTML   = '<option value="">-- select coverage --</option>';
      setDisplay();
      return;
    }

    // Preserve existing options as a fallback in case the request fails
    const prevBenefHTML = $benef.innerHTML;
    const prevCovHTML   = $cov.innerHTML;

    // Show lightweight loading state without destroying fallback yet
    $benef.innerHTML = '<option value="">Loading beneficiaries...</option>';
    $cov.innerHTML   = '<option value="">Loading coverages...</option>';

    const url = `{{ route('bancassurance.medicalfunds.contributors.options', ['medical_fund' => $medical_fund->Id, 'contributor' => ':cid']) }}`
                  .replace(':cid', cid);
    try{
      const res = await fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' } });
      const json = await res.json();
      if(!json.ok){
        // Restore previous state if backend signals failure
        $benef.innerHTML = prevBenefHTML;
        $cov.innerHTML   = prevCovHTML;
        setDisplay(null, 'Could not load contributor options.');
        return;
      }

      // Reset then refill from payload
      $benef.innerHTML = '<option value="">-- select beneficiary --</option>';
      (json.beneficiaries || []).forEach(b => {
        const o = document.createElement('option');
        o.value = b.ID; o.textContent = b.FullName;
        $benef.appendChild(o);
      });

      $cov.innerHTML = '<option value="">-- select coverage --</option>';
      (json.coverages || []).forEach(cv => {
        const o = document.createElement('option');
        o.value = cv.ID; o.textContent = cv.Name;
        o.dataset.annual   = cv.AnnualLimit ?? '';
        o.dataset.pervisit = cv.PerVisitLimit ?? '';
        o.dataset.wait     = cv.WaitingPeriodDays ?? '';
        o.dataset.scope    = cv.Scope ?? 'PerBeneficiary';
        $cov.appendChild(o);
      });

      setDisplay(); // clear figures until user picks a coverage
    }catch(e){
      // Restore previous options on network/parse errors
      $benef.innerHTML = prevBenefHTML;
      $cov.innerHTML   = prevCovHTML;
      setDisplay(null, 'Failed to load options.');
    }
  }

  function setDisplay(payload, err){
    $annual.textContent = payload ? fmt(payload.annual)    : '—';
    $used.textContent   = payload ? fmt(payload.used)      : '—';
    $remain.textContent = payload ? fmt(payload.remaining) : '—';
    $note.textContent   = payload
      ? (payload.waitingOk ? '' : (payload.waitingMsg || ''))
      : (err || '');
  }

  // When user picks a coverage, pre-fill Annual from option's data-*,
  // then call backend to compute Used/Remaining (and waiting period/per-visit note).
  async function onCoverageChange(){
    const opt = $cov.options[$cov.selectedIndex];
    if(!opt || !opt.value){ setDisplay(); return; }

    // pre-fill annual from pivot data for instant feedback
    const preAnnual = opt.dataset.annual ? parseFloat(opt.dataset.annual) : null;
    setDisplay({ annual: preAnnual, used: null, remaining: null, waitingOk: true });

    await refreshLimits(); // fetch Used YTD & Remaining from backend
  }

  async function refreshLimits(){
    const cid = getVal('ContributorId');
    const bid = getVal('BeneficiaryId');
    const cov = getVal('CoverageId');
    const dt  = getVal('DisbursementDate');

    if(!cid || !cov){ setDisplay(); return; }

  // coverage.remaining route only requires {contributor}; do not pass extra params
  const url = `{{ route('bancassurance.coverage.remaining', ['contributor' => ':cid']) }}`
      .replace(':cid', cid)
      + `?coverage_id=${encodeURIComponent(cov)}`
      + (bid ? `&beneficiary_id=${encodeURIComponent(bid)}` : '')
      + (dt ? `&on_date=${encodeURIComponent(dt)}` : '');

    try{
      const res = await fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' } });
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
            $note.textContent = `Warning: amount exceeds Per-Visit limit (${data.per_visit_limit}).`;
          } else {
            $note.textContent = data.waiting_ok ? '' : (data.waiting_message || '');
          }
        };
      }
    }catch(e){
      setDisplay(null,'Could not load limits.');
    }
  }

  // Bind events
  if($contrib){
    $contrib.addEventListener('change', async function(){
      await loadOptionsForContributor(this.value);
    });
  }
  if($cov){ $cov.addEventListener('change', onCoverageChange); }
  if($benef){ $benef.addEventListener('change', refreshLimits); }
  if($date){ $date.addEventListener('change', refreshLimits); }

  // Initial boot:
  // If contributor is pre-locked (hidden input), we must load options via AJAX now.
  @if(isset($contributor) && $contributor)
    loadOptionsForContributor('{{ $contributor->Id }}');
  @endif
})();
</script>
@endsection
