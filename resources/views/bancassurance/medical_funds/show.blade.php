@extends('layouts.app')
@section('title', 'Medical Funds')

@section('content')
<div class="container py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
        <h4 class="fw-bold mb-0 text-primary">
            <i class="bi bi-heart-pulse me-2"></i>
            Fund: {{ $medical_fund->FundName ?? '-' }}
        </h4>
        <div class="d-flex gap-2">
            <a href="{{ route('bancassurance.medicalfunds.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            <a href="{{ route('bancassurance.medicalfunds.edit', $medical_fund) }}" class="btn btn-primary">
                <i class="bi bi-pencil-square me-1"></i> Edit
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">

        <!-- Packages & Coverages -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-box-seam me-1 text-primary"></i> Packages & Coverages
                    </h6>
                    <a href="{{ route('bancassurance.medicalfunds.packages.index', $medical_fund) }}"
                       class="btn btn-sm btn-primary">
                        <i class="bi bi-gear-fill me-1"></i> Manage
                    </a>
                </div>

                <div class="card-body">
                    @if($medical_fund->packages->count())
                        <div class="accordion" id="pkgAccordion">
                            @foreach($medical_fund->packages as $pkg)
                                @php
                                    $collapseId = "pkgCollapse{$pkg->Id}";
                                    $headingId  = "pkgHeading{$pkg->Id}";
                                @endphp

                                <div class="accordion-item border-0 mb-3 shadow-sm rounded">
                                    <h2 class="accordion-header" id="{{ $headingId }}">
                                        <button class="accordion-button collapsed bg-light fw-semibold"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#{{ $collapseId }}"
                                                aria-expanded="false" aria-controls="{{ $collapseId }}">
                                            <div class="w-100 d-flex justify-content-between align-items-center">
                                                <div>
                                                    {{ $pkg->Name ?? '-' }}
                                                    @if($pkg->IsCompulsory)
                                                        <span class="badge bg-warning text-dark ms-2">Compulsory</span>
                                                    @endif
                                                    <div class="small text-muted mt-1">
                                                        Premium: <strong>{{ number_format((float)$pkg->Premium, 2) }}</strong>
                                                        @if($pkg->CoverageDescription)
                                                            • {{ $pkg->CoverageDescription }}
                                                        @endif
                                                    </div>
                                                </div>
                                                <span class="small text-muted"><i class="bi bi-chevron-down"></i></span>
                                            </div>
                                        </button>
                                    </h2>

                                    <div id="{{ $collapseId }}" class="accordion-collapse collapse"
                                         aria-labelledby="{{ $headingId }}" data-bs-parent="#pkgAccordion">
                                        <div class="accordion-body bg-white">
                                            @php $covs = $pkg->coverages ?? collect(); @endphp

                                            @if($covs->count())
                                                <div class="table-responsive mb-2">
                                                    <table class="table table-sm table-hover align-middle mb-0">
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
                                                            @foreach($covs as $cov)
                                                                <tr>
                                                                    <td>
                                                                        <div class="fw-semibold">{{ $cov->Name ?? '-' }}</div>
                                                                        @if($cov->Description)
                                                                            <div class="small text-muted">{{ $cov->Description }}</div>
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-end">
                                                                        {{ $cov->pivot->AnnualLimit !== null ? number_format((float)$cov->pivot->AnnualLimit, 2) : '—' }}
                                                                    </td>
                                                                    <td class="text-end">
                                                                        {{ $cov->pivot->PerVisitLimit !== null ? number_format((float)$cov->pivot->PerVisitLimit, 2) : '—' }}
                                                                    </td>
                                                                    <td>{{ $cov->pivot->Scope ?? 'PerBeneficiary' }}</td>
                                                                    <td>{{ $cov->pivot->WaitingPeriodDays ? $cov->pivot->WaitingPeriodDays.' days' : '—' }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-muted">No coverages attached to this package.</div>
                                            @endif

                                            <div class="mt-3 d-flex gap-2">
                                                <a href="{{ route('bancassurance.packages.edit', $pkg->Id) }}"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                                </a>
                                                <a href="{{ route('bancassurance.packages.show', $pkg->Id) }}"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-eye me-1"></i> View
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No packages defined yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Contributors -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-people-fill me-1 text-primary"></i> Contributors
                    </h6>
                    <a href="{{ route('bancassurance.medicalfunds.contributors.index', $medical_fund) }}"
                       class="btn btn-sm btn-primary">
                        <i class="bi bi-gear-fill me-1"></i> Manage
                    </a>
                </div>

                <div class="card-body">
                    @if($medical_fund->contributors->count())
                        <ul class="list-group list-group-flush">
                            @foreach($medical_fund->contributors->take(5) as $c)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-semibold">{{ $c->thirdParty->ThirdPartyName ?? '-' }}</span>
                                        <small class="text-muted d-block">{{ $c->status->Description ?? '-' }}</small>
                                    </div>
                                    <a href="{{ route('bancassurance.contributors.show', $c->Id) }}"
                                       class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Open
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        @if($medical_fund->contributors->count() > 5)
                            <div class="mt-2 text-muted small">
                                Showing first 5. View all from <strong>Manage Contributors</strong>.
                            </div>
                        @endif
                    @else
                        <p class="text-muted mb-0">No contributors registered yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-control,
    .form-select,
    .input-group-text {
        background-color: #fff;
        border: 1px solid #111;
        color: #212529;
    }
</style>
@endpush
