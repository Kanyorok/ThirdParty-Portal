@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Contributions — {{ $medical_fund->FundName }}</h4>

    <div class="d-flex gap-2">
      @php $qOnly = request()->only(['contributor','date_from','date_to']); @endphp

      @if(isset($contributor) && $contributor)
        <span class="badge bg-info text-dark align-self-center">
          Filtered: {{ $contributor->FullName }}
        </span>
        <a class="btn btn-primary"
           href="{{ route('bancassurance.medicalfunds.contributions.create', ['medical_fund' => $medical_fund->ID]) }}?contributor={{ $contributor->ID }}">
          New Contribution
        </a>
      @else
        <a class="btn btn-primary"
           href="{{ route('bancassurance.medicalfunds.contributions.create', ['medical_fund' => $medical_fund->ID]) }}">
          New Contribution
        </a>
      @endif

      <a class="btn btn-outline-secondary"
         href="{{ route('bancassurance.medicalfunds.show',['medical_fund' => $medical_fund->ID]) }}">
        Back to Fund
      </a>
    </div>
  </div>

  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

  {{-- Filters --}}
  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" action="{{ route('bancassurance.medicalfunds.contributions.index', ['medical_fund' => $medical_fund->ID]) }}" class="row g-2">
        <div class="col-md-3">
          <label class="form-label">From</label>
          <input type="date" name="date_from" class="form-control"
                 value="{{ request('date_from') }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">To</label>
          <input type="date" name="date_to" class="form-control"
                 value="{{ request('date_to') }}">
        </div>

        @if(!isset($contributor) || !$contributor)
          <div class="col-md-4">
            <label class="form-label">Contributor</label>
            <select name="contributor" class="form-select">
              <option value="">-- all contributors --</option>
              @isset($contributors)
                @foreach($contributors as $ctr)
                  <option value="{{ $ctr->ID }}" @selected((string)request('contributor')===(string)$ctr->ID)>
                    {{ $ctr->FullName }}
                  </option>
                @endforeach
              @endisset
            </select>
            <div class="form-text">Shown only on fund-wide view.</div>
          </div>
        @else
          {{-- keep contributor id in the query silently to preserve state --}}
          <input type="hidden" name="contributor" value="{{ $contributor->ID }}">
        @endif

        <div class="col-md-2 d-flex align-items-end gap-2">
          <button class="btn btn-primary">Apply</button>
          <a class="btn btn-outline-secondary"
             href="{{ route('bancassurance.medicalfunds.contributions.index', $medical_fund->ID) }}">
            Reset
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- Totals --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="d-flex justify-content-between">
        <div>Total Contributions (filtered)</div>
        <div class="fw-bold">
          {{ number_format((float)($total ?? $contributions->sum('Amount')), 2) }}
        </div>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="card">
    <div class="card-body p-0">
      @if($contributions->count())
        <div class="table-responsive">
          <table class="table table-striped mb-0 align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Date</th>
                <th>Contributor</th>
                <th>Type</th>
                <th class="text-end">Amount</th>
                <th>Notes</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($contributions as $i => $c)
                <tr>
                  <td>{{ $contributions->firstItem() + $i }}</td>
                  <td>{{ optional($c->ContributionDate)->format('Y-m-d') }}</td>
                  <td>
                    {{-- Optional: show name if relation exists; fallback to ID --}}
                    {{ optional($c->contributor)->FullName ?? $c->ContributorID ?? '—' }}
                  </td>
                  <td>{{ $c->ContributorType ?? '—' }}</td>
                  <td class="text-end">{{ number_format((float)$c->Amount,2) }}</td>
                  <td>{{ $c->Notes ?? '—' }}</td>
                  <td class="text-end">
                    @php $qs = request()->getQueryString(); @endphp
                    <div class="btn-group">
                      <a class="btn btn-sm btn-outline-primary"
                         href="{{ route('bancassurance.contributions.edit', $c->ID) }}{{ $qs ? ('?'.$qs) : '' }}">
                        Edit
                      </a>
                      <form action="{{ route('bancassurance.contributions.destroy', $c->ID) }}"
                            method="POST"
                            onsubmit="return confirm('Delete this contribution?');">
                        @csrf
                        @method('DELETE')
                        @if($qs)
                          <input type="hidden" name="redirect_query" value="{{ $qs }}">
                        @endif
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div class="p-4 text-center text-muted">No contributions recorded.</div>
      @endif
    </div>

    @if($contributions->hasPages())
      <div class="card-footer">{{ $contributions->appends(request()->query())->links() }}</div>
    @endif
  </div>
</div>
@endsection
