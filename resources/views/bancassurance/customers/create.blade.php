@extends('layouts.app')
@section('title', 'New Customer Profile')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i> New Customer Profile</h5>
            <a href="{{ route('bancassurance.customers.index') }}" class="btn btn-light btn-sm fw-bold">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="card-body p-4">
            <form method="POST" action="{{ route('bancassurance.customers.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Customer Selection --}}
                <h6 class="fw-bold text-secondary mb-3 border-bottom pb-2">
                    <i class="fas fa-address-book me-2 text-success"></i> Customer Information
                </h6>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="ThirdPartyId" class="form-label fw-semibold">Customer <span class="text-danger">*</span></label>
                        <select name="ThirdPartyId" id="ThirdPartyId" class="form-select shadow-sm" required>
                            <option value="">-- Select Customer --</option>
                            @foreach ($ThirdPartyIds as $ThirdPartyId)
                                <option value="{{ $ThirdPartyId->Id }}">
                                    {{ $ThirdPartyId->ThirdPartyName }} — {{ $ThirdPartyId->Phone }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="ReferralID" class="form-label fw-semibold">Client Referral Name</label>
                        <select name="ReferralID" id="ReferralID" class="form-select shadow-sm">
                            <option value="">-- Select Referral --</option>
                            @foreach ($referrals as $referral)
                                <option value="{{ $referral->Id }}">
                                    {{ $referral->ClientName }} — {{ $referral->ClientPhone }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Personal Information --}}
                <h6 class="fw-bold text-secondary mb-3 border-bottom pb-2">
                    <i class="fas fa-id-card me-2 text-success"></i> Personal Information
                </h6>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label for="DateOfBirth" class="form-label fw-semibold">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" name="DateOfBirth" id="DateOfBirth" class="form-control shadow-sm" required>
                    </div>

                    <div class="col-md-3">
                        <label for="Gender" class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                        <select name="Gender" id="Gender" class="form-select shadow-sm" required>
                            <option value="">-- Select Gender --</option>
                            @foreach ($genders as $gender)
                                <option value="{{ $gender->ID }}">{{ $gender->Description }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="MaritalStatus" class="form-label fw-semibold">Marital Status <span class="text-danger">*</span></label>
                        <select name="MaritalStatus" id="MaritalStatus" class="form-select shadow-sm" required>
                            <option value="">-- Select Status --</option>
                            @foreach ($maritalstatus as $status)
                                <option value="{{ $status->ID }}">{{ $status->Description }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="Occupation" class="form-label fw-semibold">Employment Status</label>
                        <select name="Occupation" id="Occupation" class="form-select shadow-sm">
                            <option value="">-- Select Occupation --</option>
                            @foreach ($occupations as $occupation)
                                <option value="{{ $occupation->ID }}">{{ $occupation->Description }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="text-end mt-4">
                    <button class="btn btn-success px-4 py-2 rounded-pill shadow-sm fw-semibold"
                        type="submit"
                        onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin me-2\'></i>Submitting...'; this.form.submit();">
                        <i class="fas fa-save me-2"></i> Save Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
