@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Medical Fund: {{ $medicalfund->FundName }}</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('bancassurance.medicalfunds.edit', $medicalfund->ID) }}" class="btn btn-primary">Edit</a>
            <a href="{{ route('bancassurance.medicalfunds.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    <div class="row g-3">
        <!-- Packages & Coverages (accordion) -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Packages & Coverages</h6>
                    <a href="{{ route('bancassurance.medicalfunds.packages.index', $medicalfund->ID) }}" class="btn btn-sm btn-primary">
                        Manage Packages
                    </a>
                </div>

                <div class="card-body">
                    @if($medicalfund->packages->count())
                        <div class="accordion" id="pkgAccordion">
                            @foreach($medicalfund->packages as $idx => $pkg)
                                @php
                                  $collapseId = "pkgCollapse{$pkg->ID}";
                                  $headingId  = "pkgHeading{$pkg->ID}";
                                @endphp
                                <div class="accordion-item mb-2 border rounded">
                                    <h2 class="accordion-header" id="{{ $headingId }}">
                                        <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                                                aria-expanded="false" aria-controls="{{ $collapseId }}">
                                            <div class="d-flex w-100 justify-content-between align-items-center">
                                                <div>
                                                    <span class="fw-semibold">{{ $pkg->Name }}</span>
                                                    @if($pkg->IsCompulsory)
                                                        <span class="badge bg-warning text-dark ms-2">Compulsory</span>
                                                    @endif
                                                    <div class="small text-muted">
                                                        Premium: {{ number_format($pkg->Premium,2) }}
                                                        @if($pkg->CoverageDescription)
                                                            • {{ $pkg->CoverageDescription }}
                                                        @endif
                                                    </div>
                                                </div>
                                                <span class="small text-muted ms-3">click to view coverages</span>
                                            </div>
                                        </button>
                                    </h2>

                                    <div id="{{ $collapseId }}" class="accordion-collapse collapse" aria-labelledby="{{ $headingId }}" data-bs-parent="#pkgAccordion">
                                        <div class="accordion-body">
                                            @php $covs = $pkg->coverages ?? collect(); @endphp
                                            @if($covs->count())
                                                <div class="table-responsive">
                                                    <table class="table table-sm align-middle mb-0">
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
                                                                        <div class="fw-semibold">{{ $cov->Name }}</div>
                                                                        @if($cov->Description)
                                                                            <div class="small text-muted">{{ $cov->Description }}</div>
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-end">
                                                                        {{ $cov->pivot->AnnualLimit !== null ? number_format((float)$cov->pivot->AnnualLimit,2) : '—' }}
                                                                    </td>
                                                                    <td class="text-end">
                                                                        {{ $cov->pivot->PerVisitLimit !== null ? number_format((float)$cov->pivot->PerVisitLimit,2) : '—' }}
                                                                    </td>
                                                                    <td>{{ $cov->pivot->Scope ?? 'PerBeneficiary' }}</td>
                                                                    <td>
                                                                        {{ $cov->pivot->WaitingPeriodDays ? $cov->pivot->WaitingPeriodDays.' days' : '—' }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-muted">No coverages attached to this package.</div>
                                            @endif

                                            <div class="mt-3">
                                                {{-- Optional quick actions for a package (edit/delete shallow routes) --}}
                                                <a href="{{ route('bancassurance.packages.edit', $pkg->ID) }}" class="btn btn-sm btn-outline-primary">Edit Package</a>
                                                <a href="{{ route('bancassurance.packages.show', $pkg->ID) }}" class="btn btn-sm btn-outline-secondary">View Package</a>
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

        <!-- Contributors Section (unchanged) -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Contributors</h6>
                    <a href="{{ route('bancassurance.medicalfunds.contributors.index', $medicalfund->ID) }}" class="btn btn-sm btn-primary">
                        Manage Contributors
                    </a>
                </div>
                <div class="card-body">
                    @if($medicalfund->contributors->count())
                        <ul class="list-group list-group-flush">
                            @foreach($medicalfund->contributors->take(5) as $c)
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>{{ $c->FullName }} ({{ $c->Status }})</span>
                                    <a href="{{ route('bancassurance.contributors.show', $c->ID) }}" class="btn btn-sm btn-outline-info">Open</a>
                                </li>
                            @endforeach
                        </ul>
                        @if($medicalfund->contributors->count() > 5)
                            <div class="mt-2"><em>Showing first 5. View all from Manage Contributors.</em></div>
                        @endif
                    @else
                        <p class="text-muted">No contributors registered yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
