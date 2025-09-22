@extends('layouts.app')
@section('title', 'New Customer Profile')

@section('content')
    <div class="container mt-4">
        <form method="POST" action="{{ route('bancassurance.customers.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="FullName" id="FullName" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Referred By</label>
                    <select name="ReferralID" id="ReferralID" class="form-select">
                        <option value="">--Select a referral--</option>
                        @foreach ($referrals as $referral)
                            <option value="{{ $referral->Id }}"
                                    data-clientname="{{ $referral->ClientName }}"
                                    data-clientidnumber="{{ $referral->ClientIDNumber }}"
                                    data-clientemail="{{ $referral->ClientEmail }}"
                            >
                                {{ $referral->ClientName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">National ID / Passport No <span class="text-danger">*</span></label>
                    <input type="text" name="NationalID" id="NationalID" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">KRA PIN <span class="text-danger">*</span></label>
                    <input type="text" name="KRAPIN" class="form-control" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                    <input type="date" name="DateOfBirth" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                    <select name="Gender" class="form-select" required>
                        <option value="">--Select a status--</option>
                        @foreach ($genders as $gender)
                            <option value="{{ $gender->ID }}">
                                {{ $gender->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Marital Status <span class="text-danger">*</span></label>
                    <select name="MaritalStatus" class="form-select" required>
                        <option value="">--Select a status--</option>
                        @foreach ($maritalstatus as $status)
                            <option value="{{ $status->ID }}">
                                {{ $status->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Employment Status </label>
                    <select name="Occupation" class="form-select">
                        <option value="">--Select Occupation--</option>
                        @foreach ($occupations as $occupation)
                            <option value="{{ $occupation->ID }}">
                                {{ $occupation->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                    <input type="text" name="PhoneNumber" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="Email" id="Email" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Address</label>
                    <input type="text" name="Address" class="form-control">
                </div>
            </div>

            <div class="text-end">
                <button class="btn btn-success" type="submit">
                    <i class="fas fa-save"></i> Save Profile
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const referralSelect = document.getElementById('ReferralID');
            const fullNameInput = document.getElementById('FullName');
            const nationalIdInput = document.getElementById('NationalID');
            const emailInput = document.getElementById('Email');

            referralSelect.addEventListener('change', function () {
                const selected = referralSelect.options[referralSelect.selectedIndex];
                const clientName = selected.getAttribute('data-clientname') || '';
                const clientIdNumber = selected.getAttribute('data-clientidnumber') || '';
                const clientEmail = selected.getAttribute('data-clientemail') || '';

                if (clientName) fullNameInput.value = clientName;
                if (clientIdNumber) nationalIdInput.value = clientIdNumber;
                if (clientEmail) emailInput.value = clientEmail;
            });
        });
    </script>
@endsection
