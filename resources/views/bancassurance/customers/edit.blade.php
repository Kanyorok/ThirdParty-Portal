@extends('layouts.app')
@section('title', 'Edit Customer Profile')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">✏️ Edit Customer Profile & KYC</h4>

        <form method="POST" action="{{ route('bancassurance.customers.update', $customer->Id) }}"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="FullName" class="form-control"
                           value="{{ old('FullName', $customer->FullName) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Referred By</label>
                    <select name="ReferralID" class="form-select">
                        <option value="">--Select a referral--</option>
                        @foreach ($referrals as $referral)
                            <option
                                value="{{ $referral->Id }}" {{ $customer->ReferralID == $referral->Id ? 'selected' : '' }}>
                                {{ $referral->ClientName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">National ID</label>
                    <input type="text" name="NationalID" class="form-control"
                           value="{{ old('NationalID', $customer->NationalID) }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">KRA PIN</label>
                    <input type="text" name="KRAPIN" class="form-control"
                           value="{{ old('KRAPIN', $customer->KRAPIN) }}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="DateOfBirth" class="form-control"
                           value="{{ old('DateOfBirth', \Carbon\Carbon::parse($customer->DateOfBirth)->format('Y-m-d')) }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Gender</label>
                    <select name="Gender" class="form-select">
                        <option value="">--Select Gender--</option>
                        @foreach ($genders as $gender)
                            <option value="{{ $gender->ID }}" {{ $customer->Gender == $gender->ID ? 'selected' : '' }}>
                                {{ $gender->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Marital Status</label>
                    <select name="MaritalStatus" class="form-select">
                        <option value="">--Select Status--</option>
                        @foreach ($maritalstatus as $status)
                            <option
                                value="{{ $status->ID }}" {{ $customer->MaritalStatus == $status->ID ? 'selected' : '' }}>
                                {{ $status->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Occupation</label>
                    <select name="Occupation" class="form-select">
                        <option value="">--Select Occupation--</option>
                        @foreach ($occupations as $occupation)
                            <option
                                value="{{ $occupation->ID }}" {{ $customer->Occupation == $occupation->ID ? 'selected' : '' }}>
                                {{ $occupation->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="text" name="PhoneNumber" class="form-control"
                           value="{{ old('PhoneNumber', $customer->PhoneNumber) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="Email" class="form-control" value="{{ old('Email', $customer->Email) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Address</label>
                    <input type="text" name="Address" class="form-control"
                           value="{{ old('Address', $customer->Address) }}">
                </div>
            </div>

            <div class="text-end">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </div>
        </form>
    </div>
@endsection
