@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Package: {{ $package->Name }}</h4>
    <div class="d-flex gap-2">
      @if($package->fund)
        <a href="{{ route('bancassurance.medicalfunds.show', $package->FundID) }}" class="btn btn-outline-secondary">Back to Fund</a>
      @endif
      <a href="{{ route('bancassurance.packages.edit', $package->ID) }}" class="btn btn-primary">Edit Package</a>
      <a href="{{ route('bancassurance.packages.show', $package->ID) }}" class="btn btn-sm btn-outline-secondary">View</a>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4"><div class="small text-muted">Premium</div><div class="fs-5 fw-semibold">{{ number_format((float)$package->Premium,2) }}</div></div>
        <div class="col-md-4"><div class="small text-muted">Compulsory</div><div class="fs-5">{{ $package->IsCompulsory ? 'Yes' : 'No' }}</div></div>
        <div class="col-md-4"><div class="small text-muted">Description</div><div>{{ $package->CoverageDescription ?: '—' }}</div></div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h6 class="mb-0">Coverages in this Package</h6></div>
    <div class="card-body p-0">
      @if($package->coverages->count())
        <div class="table-responsive">
          <table class="table table-striped mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th>Coverage</th>
                <th class="text-end">Annual Limit</th>
                <th class="text-end">Per-Visit</th>
                <th>Scope</th>
                <th>Waiting</th>
              </tr>
            </thead>
            <tbody>
              @foreach($package->coverages as $cov)
                <tr>
                  <td>
                    <div class="fw-semibold">{{ $cov->Name }}</div>
                    <div class="small text-muted">{{ $cov->Description }}</div>
                  </td>
                  <td class="text-end">{{ $cov->pivot->AnnualLimit !== null ? number_format((float)$cov->pivot->AnnualLimit,2) : '—' }}</td>
                  <td class="text-end">{{ $cov->pivot->PerVisitLimit !== null ? number_format((float)$cov->pivot->PerVisitLimit,2) : '—' }}</td>
                  <td>{{ $cov->pivot->Scope ?? 'PerBeneficiary' }}</td>
                  <td>{{ $cov->pivot->WaitingPeriodDays ? $cov->pivot->WaitingPeriodDays.' days' : '—' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div class="p-4 text-center text-muted">No coverages attached.</div>
      @endif
    </div>
  </div>
</div>
@endsection
