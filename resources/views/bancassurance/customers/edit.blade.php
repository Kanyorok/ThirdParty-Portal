@extends('layouts.app')
@section('title', 'Edit Customer Profile')

@section('content')
    <div class="container mt-4">
        <form method="POST" action="{{ route('bancassurance.customers.update', $customer->Id) }}"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Customer Selection --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="ThirdPartyId" class="form-label">Customer <span class="text-danger">*</span></label>
                    <input type="text" class="form-control"
                           value="{{ $customer->thirdParty->ThirdPartyName }} — {{ $customer->thirdParty->Phone }}"
                           disabled>
                    <input type="hidden" name="ThirdPartyId" value="{{ $customer->ThirdPartyId }}">
                </div>

                <div class="col-md-6">
                    <label for="ReferralID" class="form-label">Client Referral Name</label>
                    <select name="ReferralID" id="ReferralID" class="form-select">
                        <option value="">-- Select a referral --</option>
                        @foreach ($referrals as $referral)
                            <option value="{{ $referral->Id }}"
                                {{ old('ReferralID', $customer->ReferralID) == $referral->Id ? 'selected' : '' }}>
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
                    <input type="date" name="DateOfBirth" id="DateOfBirth" class="form-control"
                           value="{{ old('DateOfBirth', $customer->DateOfBirth ? \Carbon\Carbon::parse($customer->DateOfBirth)->format('Y-m-d') : '') }}"
                           required>
                </div>

                <div class="col-md-3">
                    <label for="Gender" class="form-label">Gender <span class="text-danger">*</span></label>
                    <select name="Gender" id="Gender" class="form-select" required>
                        <option value="">-- Select Gender --</option>
                        @foreach ($genders as $gender)
                            <option
                                value="{{ $gender->ID }}" {{ old('Gender', $customer->Gender) == $gender->ID ? 'selected' : '' }}>
                                {{ $gender->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="MaritalStatus" class="form-label">Marital Status <span
                            class="text-danger">*</span></label>
                    <select name="MaritalStatus" id="MaritalStatus" class="form-select" required>
                        <option value="">-- Select Status --</option>
                        @foreach ($maritalstatus as $status)
                            <option
                                value="{{ $status->ID }}" {{ old('MaritalStatus', $customer->MaritalStatus) == $status->ID ? 'selected' : '' }}>
                                {{ $status->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="Occupation" class="form-label">Employment Status</label>
                    <select name="Occupation" id="Occupation" class="form-select">
                        <option value="">-- Select Occupation --</option>
                        @foreach ($occupations as $occupation)
                            <option
                                value="{{ $occupation->ID }}" {{ old('Occupation', $customer->Occupation) == $occupation->ID ? 'selected' : '' }}>
                                {{ $occupation->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Submit --}}
            <div class="text-end">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </div>
        </form>
    </div>
@endsection
