@extends('layouts.app')
@section('title', 'Customer Profile View')

@section('content')
    <div class="container mt-5" style="max-width: 800px;">
        <div class="card shadow border-0 rounded-4">
            <div class="card-body p-4">

                {{-- Header --}}
                <h4 class="mb-4"> Customer Profile profile</h4>

                {{-- Customer Info --}}
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Full Name</label>
                        <input type="text" class="form-control bg-light" value="{{ $customer->FullName ?? '-' }}"
                               readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Referred By</label>
                        <input type="text" class="form-control bg-light"
                               value="{{ $customer->referrals->ClientName ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">National ID</label>
                        <input type="text" class="form-control bg-light" value="{{ $customer->NationalID ?? '-' }}"
                               readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">KRA PIN</label>
                        <input type="text" class="form-control bg-light" value="{{ $customer->KRAPIN ?? '-' }}"
                               readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Date of Birth</label>
                        <input type="text" class="form-control bg-light"
                               value="{{ $customer->DateOfBirth ? \Carbon\Carbon::parse($customer->DateOfBirth)->format('d/m/Y') : '-' }}"
                               readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Gender</label>
                        <input type="text" class="form-control bg-light"
                               value="{{ $customer->genders->Description ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Marital Status</label>
                        <input type="text" class="form-control bg-light"
                               value="{{ $customer->maritalstatus->Description ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Occupation</label>
                        <input type="text" class="form-control bg-light"
                               value="{{ $customer->occupations->Description ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Phone</label>
                        <input type="text" class="form-control bg-light" value="{{ $customer->PhoneNumber ?? '-' }}"
                               readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Email</label>
                        <input type="text" class="form-control bg-light" value="{{ $customer->Email ?? '-' }}" readonly>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold text-muted">Address</label>
                        <textarea class="form-control bg-light" rows="2"
                                  readonly>{{ $customer->Address ?? '-' }}</textarea>
                    </div>

                    {{-- Footer --}}
                    <div class="text-end mt-4">
                        <a href="{{ route('bancassurance.customers.check') }}"
                           class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fas fa-arrow-left me-2"></i> Back to List
                        </a>
                    </div>

                    {{-- Footer Info --}}
                    <div class="card-footer small text-muted bg-light border-0 rounded-bottom-3">
                        <div class="d-flex flex-wrap justify-content-between">
                            <div><strong>Created By:</strong> {{ $customer->createdByUser->Name }}
                                <span class="ms-3"><strong>Created On:</strong>
                        {{ \Carbon\Carbon::parse($customer->CreatedOn)->format('d/m/Y') }}</span>
                            </div>
                            <div>
                                <strong>Modified By:</strong> {{$customer->modifiedByUser->Name }}
                                <span class="ms-3"><strong>Modified On:</strong>
                        {{ \Carbon\Carbon::parse($customer->ModifiedOn)->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
@endsection
