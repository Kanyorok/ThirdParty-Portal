@extends('layouts.app')
@section('title', 'New Customer Profile')

@section('content')
<div class="container mt-4">
    <form method="POST" action="{{ route('bancassurance.customers.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- Customer Selection --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="ThirdPartyId" class="form-label">Customer <span class="text-danger">*</span></label>
                <select name="ThirdPartyId" id="ThirdPartyId" class="form-select" required>
                    <option value="">-- Select Customer --</option>
                    @foreach ($ThirdPartyIds as $ThirdPartyId)
                        <option value="{{ $ThirdPartyId->Id }}">
                            {{ $ThirdPartyId->ThirdPartyName }} — {{ $ThirdPartyId->Phone }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="ReferralID" class="form-label">Client Referral Name</label>
                <select name="ReferralID" id="ReferralID" class="form-select">
                    <option value="">-- Select a referral --</option>
                    @foreach ($referrals as $referral)
                        <option value="{{ $referral->Id }}">
                            {{ $referral->ClientName }} — {{ $referral->ClientPhone }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Personal Information --}}
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="DateOfBirth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                <input type="date" name="DateOfBirth" id="DateOfBirth" class="form-control" required>
            </div>

            <div class="col-md-3">
                <label for="Gender" class="form-label">Gender <span class="text-danger">*</span></label>
                <select name="Gender" id="Gender" class="form-select" required>
                    <option value="">-- Select Gender --</option>
                    @foreach ($genders as $gender)
                        <option value="{{ $gender->ID }}">{{ $gender->Description }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="MaritalStatus" class="form-label">Marital Status <span class="text-danger">*</span></label>
                <select name="MaritalStatus" id="MaritalStatus" class="form-select" required>
                    <option value="">-- Select Status --</option>
                    @foreach ($maritalstatus as $status)
                        <option value="{{ $status->ID }}">{{ $status->Description }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="Occupation" class="form-label">Employment Status</label>
                <select name="Occupation" id="Occupation" class="form-select">
                    <option value="">-- Select Occupation --</option>
                    @foreach ($occupations as $occupation)
                        <option value="{{ $occupation->ID }}">{{ $occupation->Description }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Submit --}}
        <div class="text-end">
            <button class="btn btn-success" type="submit" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                <i class="fas fa-save"></i> Save Profile
            </button>
        </div>
    </form>
</div>
@endsection
