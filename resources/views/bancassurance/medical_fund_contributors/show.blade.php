@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Contributor: {{ $contributor->FullName }}</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('bancassurance.contributors.edit', $contributor->ID) }}" class="btn btn-primary">Edit</a>
            <a href="{{ route('bancassurance.medicalfunds.contributors.index', $contributor->FundID) }}" class="btn btn-outline-secondary">Back to Contributors</a>
        </div>
    </div>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title">Profile</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Fund</dt>
                        <dd class="col-sm-8">{{ optional($contributor->fund)->FundName ?? '—' }}</dd>

                        <dt class="col-sm-4">Contributor No</dt>
                        <dd class="col-sm-8">{{ $contributor->ContributorNo ?? '—' }}</dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">{{ $contributor->Email ?? '—' }}</dd>

                        <dt class="col-sm-4">Phone</dt>
                        <dd class="col-sm-8">{{ $contributor->Phone ?? '—' }}</dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">{{ $contributor->Status }}</dd>

                        <dt class="col-sm-4">Effective</dt>
                        <dd class="col-sm-8">
                            {{ optional($contributor->EffectiveFrom)->format('Y-m-d') ?: '—' }}
                            @if($contributor->EffectiveTo) — {{ $contributor->EffectiveTo->format('Y-m-d') }} @endif
                        </dd>
                    </dl>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <div class="text-muted small">Total Contributions</div>
                        <div class="fw-semibold">{{ number_format((float)($totals['contrib_sum'] ?? 0),2) }}</div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div class="text-muted small">Total Disbursed</div>
                        <div class="fw-semibold">{{ number_format((float)($totals['disb_sum'] ?? 0),2) }}</div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <div class="text-muted small">Balance</div>
                        <div class="fw-bold">{{ number_format((float)(($totals['contrib_sum'] ?? 0) - ($totals['disb_sum'] ?? 0)),2) }}</div>
                    </div>
                    <div class="d-grid gap-2 mt-3">
                    {{-- Contributions (filtered to this contributor) --}}
                    <a class="btn btn-outline-secondary"
                        href="{{ route('bancassurance.medicalfunds.contributions.index', $contributor->FundID) }}?contributor={{ $contributor->ID }}">
                        View Contributions
                    </a>
                    <a class="btn btn-primary"
                        href="{{ route('bancassurance.medicalfunds.contributions.create', $contributor->FundID) }}?contributor={{ $contributor->ID }}">
                        New Contribution
                    </a>

                    {{-- Disbursements (filtered to this contributor) --}}
                    <a class="btn btn-outline-secondary"
                        href="{{ route('bancassurance.medicalfunds.disbursements.index', $contributor->FundID) }}?contributor={{ $contributor->ID }}">
                        View Disbursements
                    </a>
                    <a class="btn btn-primary"
                        href="{{ route('bancassurance.medicalfunds.disbursements.create', $contributor->FundID) }}?contributor={{ $contributor->ID }}">
                        New Disbursement
                    </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Beneficiaries list + quick add -->
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title mb-3">Beneficiaries</h6>

                    @if($contributor->beneficiaries->count())
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle mb-0">
                                <thead><tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Relationship</th>
                                    <th>DOB</th>
                                    <th>National ID</th>
                                    <th>Contact</th>
                                    <th>Active</th>
                                </tr></thead>
                                <tbody>
                                @foreach($contributor->beneficiaries as $i => $b)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td class="fw-semibold">{{ $b->FullName }}</td>
                                        <td>{{ $b->Relationship ?? '—' }}</td>
                                        <td>{{ optional($b->DateOfBirth)->format('Y-m-d') ?: '—' }}</td>
                                        <td>{{ $b->NationalID ?? '—' }}</td>
                                        <td>{{ $b->Contact ?? '—' }}</td>
                                        <td>{!! $b->IsActive ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-muted">No beneficiaries yet.</div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Add Beneficiary</h6>
                    <form action="{{ route('bancassurance.contributors.beneficiaries.store', $contributor->ID) }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="FullName" class="form-control" required>
                            </div>
                        <div class="col-md-3">
                            <label class="form-label">Relationship *</label>
                            @php
                                // Ensure we have a list even if controller didn't send it
                                $rels = isset($relationships) && count($relationships)
                                ? $relationships
                                : collect([(object)['Name'=>'Self'],(object)['Name'=>'Spouse'],(object)['Name'=>'Child'],
                                            (object)['Name'=>'Parent'],(object)['Name'=>'Guardian'],(object)['Name'=>'Sibling'],
                                            (object)['Name'=>'Other']]);
                            @endphp
                            <select name="Relationship" class="form-select" required>
                                <option value="">-- select --</option>
                                @foreach($rels as $rel)
                                <option value="{{ $rel->Name }}">{{ $rel->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                            <div class="col-md-3">
                                <label class="form-label">DOB</label>
                                <input type="date" name="DateOfBirth" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">National ID</label>
                                <input type="text" name="NationalID" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Contact</label>
                                <input type="text" name="Contact" class="form-control">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="IsActive" id="isActive" value="1" checked>
                                    <label for="isActive" class="form-check-label">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            <button class="btn btn-primary">Save Beneficiary</button>
                            <a href="{{ route('bancassurance.medicalfunds.contributors.index', $contributor->FundID) }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
