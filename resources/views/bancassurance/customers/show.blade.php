@extends('layouts.app')
@section('title', 'Customer Profile View')

@section('content')

{{-- ================= STYLES ================= --}}
<style>
    .section-title {
        color: #000;
        font-weight: 600;
        font-size: .9rem;
        padding-bottom: .35rem;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 1rem;
    }
</style>

<div class="container mt-4" style="max-width: 850px;">
    <div class="card shadow border-0 rounded-4">

        {{-- Header --}}
        <div class="card-header bg-primary rounded-top-4 border-bottom">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-person-badge me-2"></i>
                Customer Profile
            </h5>
        </div>

        <div class="card-body p-4">

            {{-- ================= PERSONAL DETAILS ================= --}}
            <div class="mb-4">
                <h6 class="section-title">Personal Information</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small  text-muted">Full Name</label>
                        <input type="text" class="form-control form-control-sm bg-light"
                               value="{{ $customer->thirdParty->ThirdPartyName ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small  text-muted">National ID</label>
                        <input type="text" class="form-control form-control-sm bg-light"
                               value="{{ $customer->thirdParty->RegistrationNumber ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small  text-muted">KRA PIN</label>
                        <input type="text" class="form-control form-control-sm bg-light"
                               value="{{ $customer->ThirdParty->TaxPIN ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small  text-muted">Date of Birth</label>
                        <input type="text" class="form-control form-control-sm bg-light"
                               value="{{ $customer->DateOfBirth ? \Carbon\Carbon::parse($customer->DateOfBirth)->format('d M Y') : '-' }}"
                               readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small  text-muted">Gender</label>
                        <input type="text" class="form-control form-control-sm bg-light"
                               value="{{ $customer->genders->Description ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small  text-muted">Marital Status</label>
                        <input type="text" class="form-control form-control-sm bg-light"
                               value="{{ $customer->maritalstatus->Description ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small  text-muted">Occupation</label>
                        <input type="text" class="form-control form-control-sm bg-light"
                               value="{{ $customer->occupations->Description ?? '-' }}" readonly>
                    </div>
                </div>
            </div>

            {{-- ================= CONTACT DETAILS ================= --}}
            <div class="mb-4">
                <h6 class="section-title">Contact Information</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small  text-muted">Phone</label>
                        <input type="text" class="form-control form-control-sm bg-light"
                               value="{{ $customer->ThirdParty->Phone ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small  text-muted">Email</label>
                        <input type="text" class="form-control form-control-sm bg-light"
                               value="{{ $customer->ThirdParty->Email ?? '-' }}" readonly>
                    </div>

                    <div class="col-12">
                        <label class="form-label small  text-muted">Physical Address</label>
                        <textarea class="form-control form-control-sm bg-light" rows="2" readonly>
{{ $customer->ThirdParty->PhysicalAddress ?? '-' }}
                        </textarea>
                    </div>
                </div>
            </div>

            {{-- ================= REFERRAL ================= --}}
            <div class="mb-4">
                <h6 class="section-title">Referral Information</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small  text-muted">Referred By</label>
                        <input type="text" class="form-control form-control-sm bg-light"
                               value="{{ $customer->referrals->referredByEmployee->Name ?? '-' }}" readonly>
                    </div>
                </div>
            </div>

            {{-- ================= ACTION ================= --}}
            <div class="d-flex justify-content-end pt-3 border-top">
                <a href="{{ route('bancassurance.customers.index') }}"
                   class="btn btn-sm btn-outline-secondary px-4">
                    <i class="bi bi-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>

        {{-- ================= FOOTER ================= --}}
        <div class="card-footer bg-light small text-muted rounded-bottom-4">
            <div class="row">
                <div class="col-md-6">
                    <strong>Created By:</strong> {{ $customer->createdByUser->Name ?? '-' }}
                    <span class="ms-2">
                        ({{ \Carbon\Carbon::parse($customer->CreatedOn)->format('d M Y') }})
                    </span>
                </div>
                <div class="col-md-6 text-md-end mt-2 mt-md-0">
                    <strong>Modified By:</strong> {{ $customer->modifiedByUser->Name ?? '-' }}
                    <span class="ms-2">
                        ({{ \Carbon\Carbon::parse($customer->ModifiedOn)->format('d M Y') }})
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
