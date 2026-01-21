@extends('layouts.app')
@section('title', 'Contributor Details')

@section('content')
<div class="container my-4">
    <div class="card shadow-sm rounded-3">
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3">
            <h6 class="mb-0 text-muted">
                <i class="fas fa-user-circle text-primary me-2"></i>
                Contributor Profile
            </h6>
            <div class="d-flex gap-2">
                <a href="{{ route('bancassurance.contributors.edit', $contributor->Id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('bancassurance.medicalfunds.contributors.index', $contributor->FundId) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show small" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row g-4">
                {{-- LEFT COLUMN: PROFILE --}}
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="border-bottom pb-2 mb-3 text-muted">
                                <i class="fas fa-id-card me-2 text-secondary"></i> Profile Details
                            </h6>
                            <dl class="row small mb-0">
                                <dt class="col-sm-4 text-muted">Fund</dt>
                                <dd class="col-sm-8">{{ optional($contributor->fund)->FundName ?? '—' }}</dd>

                                <dt class="col-sm-4 text-muted">Contributor No</dt>
                                <dd class="col-sm-8">{{ $contributor->ContributorNo ?? '—' }}</dd>

                                <dt class="col-sm-4 text-muted">Email</dt>
                                <dd class="col-sm-8">{{ $contributor->thirdParty->Email ?? '—' }}</dd>

                                <dt class="col-sm-4 text-muted">Phone</dt>
                                <dd class="col-sm-8">{{ $contributor->thirdParty->Phone ?? '—' }}</dd>

                                <dt class="col-sm-4 text-muted">Status</dt>
                                <dd class="col-sm-8">
                                    <span class="badge {{ $contributor->status?->Description === 'Active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $contributor->status->Description ?? '—' }}
                                    </span>
                                </dd>

                                <dt class="col-sm-4 text-muted">Effective</dt>
                                <dd class="col-sm-8">
                                    @if($contributor->EffectiveFrom)
                                        {{ \Carbon\Carbon::parse($contributor->EffectiveFrom)->format('d M Y') }}
                                    @else
                                        —
                                    @endif
                                    @if($contributor->EffectiveTo)
                                        — {{ \Carbon\Carbon::parse($contributor->EffectiveTo)->format('d M Y') }}
                                    @endif
                                </dd>
                            </dl>

                            <hr>

                            <div class="small">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Total Contributions</span>
                                    <span class="fw-semibold">{{ number_format($totals['contrib_sum'] ?? 0, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Total Disbursed</span>
                                    <span class="fw-semibold">{{ number_format($totals['disb_sum'] ?? 0, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mt-2 border-top pt-2">
                                    <span class="fw-semibold">Balance</span>
                                    <span class="fw-bold text-success">{{ number_format(($totals['contrib_sum'] ?? 0) - ($totals['disb_sum'] ?? 0), 2) }}</span>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-3">
                                <a href="{{ route('bancassurance.medicalfunds.contributions.index', $contributor->FundId) }}?contributor={{ $contributor->Id }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-hand-holding-usd me-1"></i> View Contributions
                                </a>
                                <a href="{{ route('bancassurance.medicalfunds.contributions.create', $contributor->FundId) }}?contributor={{ $contributor->Id }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus me-1"></i> New Contribution
                                </a>
                                <a href="{{ route('bancassurance.medicalfunds.disbursements.index', $contributor->FundId) }}?contributor={{ $contributor->Id }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-wallet me-1"></i> View Disbursements
                                </a>
                                <a href="{{ route('bancassurance.medicalfunds.disbursements.create', $contributor->FundId) }}?contributor={{ $contributor->Id }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-plus me-1"></i> New Disbursement
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: BENEFICIARIES --}}
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="border-bottom pb-2 mb-3 text-muted">
                                <i class="fas fa-users me-2 text-secondary"></i> Beneficiaries
                            </h6>

                            @if($beneficiaries->count())
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Full Name</th>
                                                <th>Relationship</th>
                                                <th>Date of Birth</th>
                                                <th>National ID</th>
                                                <th>Contact</th>
                                                <th>Active</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($beneficiaries as $beneficiary)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td class="fw-semibold">{{ $beneficiary->FullName }}</td>
                                                    <td>{{ $beneficiary->relationship_display ?? '—' }}</td>
                                                    <td>{{ $beneficiary->DateOfBirth ? \Carbon\Carbon::parse($beneficiary->DateOfBirth)->format('d M Y') : '—' }}</td>
                                                    <td>{{ $beneficiary->NationalID ?? '—' }}</td>
                                                    <td>{{ $beneficiary->Contact ?? '—' }}</td>
                                                    <td>
                                                        <span class="badge {{ $beneficiary->IsActive ? 'bg-success' : 'bg-secondary' }}">
                                                            {{ $beneficiary->IsActive ? 'Yes' : 'No' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        <a href="{{ route('bancassurance.beneficiaries.edit', $beneficiary->Id) }}"
                                                           class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="fas fa-pen"></i>
                                                        </a>
                                                        <form action="{{ route('bancassurance.beneficiaries.destroy', $beneficiary->Id) }}"
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                                                    onclick="return confirm('Delete this beneficiary?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-2">
                                    {{ $beneficiaries->links('pagination::bootstrap-5') }}
                                </div>
                            @else
                                <div class="text-center p-4 border rounded-3 bg-light">
                                    <i class="fas fa-info-circle text-info fs-5 me-2"></i>
                                    <span class="text-muted">No beneficiaries found.</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- ADD BENEFICIARY --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="border-bottom pb-2 mb-3 text-muted">
                                <i class="fas fa-user-plus me-2 text-secondary"></i> Add Beneficiary
                            </h6>
                            <form action="{{ route('bancassurance.medicalfunds.beneficiaries.store', $medical_fund->Id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="FundId" value="{{ $medical_fund->Id }}">
                                <input type="hidden" name="ContributorId" value="{{ $contributor->Id }}">

                                <div class="row g-3 small">
                                    <div class="col-md-6">
                                        <label class="form-label text-muted">Full Name *</label>
                                        <input type="text" name="FullName" class="form-control form-control-sm @error('FullName') is-invalid @enderror" value="{{ old('FullName') }}" required>
                                        @error('FullName')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label text-muted">Relationship *</label>
                                        <select name="Relationship" class="form-select form-select-sm @error('Relationship') is-invalid @enderror" required>
                                            <option value="">-- select --</option>
                                            @foreach($relationships as $rel)
                                                <option value="{{ $rel->ID }}" {{ old('Relationship') == $rel->ID ? 'selected' : '' }}>
                                                    {{ $rel->Description }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('Relationship')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label text-muted">Date of Birth</label>
                                        <input type="date" name="DateOfBirth" value="{{ old('DateOfBirth') }}" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-muted">National ID</label>
                                        <input type="text" name="NationalID" value="{{ old('NationalID') }}" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-muted">Contact</label>
                                        <input type="text" name="Contact" value="{{ old('Contact') }}" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="IsActive" id="isActive" value="1" checked>
                                            <label class="form-check-label small" for="isActive">Active</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-sm btn-primary"
                                            onclick="this.disabled = true; this.innerHTML='<i class=\'fas fa-spinner fa-spin me-1\'></i> Saving...'; this.form.submit();">
                                        <i class="fas fa-save me-1"></i> Save Beneficiary
                                    </button>
                                    <a href="{{ route('bancassurance.medicalfunds.index') }}" class="btn btn-sm btn-outline-secondary">
                                        Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> 
</div>
@endsection
