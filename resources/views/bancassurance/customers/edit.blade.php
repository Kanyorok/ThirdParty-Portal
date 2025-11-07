@extends('layouts.app')

@section('title', 'Edit Customer Profile')

@section('content')
<div class="container my-4" style="max-width: 900px;">
    <form method="POST" action="{{ route('bancassurance.customers.update', $customer->Id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card shadow border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-normal">
                    <i class="bi bi-person-badge-fill me-2"></i> Edit Customer Profile
                </h5>
                <a href="{{ route('bancassurance.customers.index') }}" class="btn btn-light btn-sm rounded-pill px-3 fw-semibold">
                    <i class="bi bi-arrow-left-circle me-1"></i> Back
                </a>
            </div>

            <div class="card-body p-4">

                <section class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="ThirdPartyId" class="form-label fw-medium text-muted">
                                Customer <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-sm rounded-pill shadow-sm"
                                   value="{{ $customer->thirdParty->ThirdPartyName }} — {{ $customer->thirdParty->Phone }}" disabled>
                            <input type="hidden" name="ThirdPartyId" value="{{ $customer->ThirdPartyId }}">
                        </div>

                        <div class="col-md-6">
                            <label for="ReferralID" class="form-label fw-medium text-muted">
                                Client Referral Name
                            </label>
                            <select name="ReferralID" id="ReferralID" class="form-select form-select-sm rounded-pill shadow-sm">
                                <option value="">-- Select a referral --</option>
                                @foreach ($referrals as $referral)
                                    <option value="{{ $referral->Id }}" {{ old('ReferralID', $customer->ReferralID) == $referral->Id ? 'selected' : '' }}>
                                        {{ $referral->ClientName }} — {{ $referral->ClientPhone }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </section>

                <section>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="DateOfBirth" class="form-label fw-medium text-muted">
                                Date of Birth <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="DateOfBirth" id="DateOfBirth"
                                class="form-control form-control-sm rounded-pill shadow-sm"
                                value="{{ old('DateOfBirth', $customer->DateOfBirth ? \Carbon\Carbon::parse($customer->DateOfBirth)->format('Y-m-d') : '') }}"
                                required>
                        </div>

                        <div class="col-md-3">
                            <label for="Gender" class="form-label fw-medium text-muted">
                                Gender <span class="text-danger">*</span>
                            </label>
                            <select name="Gender" id="Gender" class="form-select form-select-sm rounded-pill shadow-sm" required>
                                <option value="">-- Select Gender --</option>
                                @foreach ($genders as $gender)
                                    <option value="{{ $gender->ID }}" {{ old('Gender', $customer->Gender) == $gender->ID ? 'selected' : '' }}>
                                        {{ $gender->Description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="MaritalStatus" class="form-label fw-medium text-muted">
                                Marital Status <span class="text-danger">*</span>
                            </label>
                            <select name="MaritalStatus" id="MaritalStatus" class="form-select form-select-sm rounded-pill shadow-sm" required>
                                <option value="">-- Select Status --</option>
                                @foreach ($maritalstatus as $status)
                                    <option value="{{ $status->ID }}" {{ old('MaritalStatus', $customer->MaritalStatus) == $status->ID ? 'selected' : '' }}>
                                        {{ $status->Description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="Occupation" class="form-label fw-medium text-muted">
                                Employment Status
                            </label>
                            <select name="Occupation" id="Occupation" class="form-select form-select-sm rounded-pill shadow-sm">
                                <option value="">-- Select Occupation --</option>
                                @foreach ($occupations as $occupation)
                                    <option value="{{ $occupation->ID }}" {{ old('Occupation', $customer->Occupation) == $occupation->ID ? 'selected' : '' }}>
                                        {{ $occupation->Description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </section>

            </div>

            <div class="card-footer d-flex justify-content-between align-items-center bg-light py-3 px-4">
                <a href="{{ route('bancassurance.customers.index') }}"
                    class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left-circle me-2"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-semibold"
                    onclick="this.disabled=true; this.innerHTML='<i class=\'bi bi-arrow-repeat me-2 spin\'></i> Updating...'; this.form.submit();">
                    <i class="bi bi-save me-2"></i> Update Profile
                </button>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .spin { animation: spin 1s linear infinite; }
    @keyframes spin { 100% { transform: rotate(360deg); } }
</style>
@endpush
@endsection
