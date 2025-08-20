@extends('layouts.app')
@section('title', 'New Customer Profile')

@section('content')
<div class="container mt-4">
    <form method="POST" action="{{ route('bancassurance.customers.store') }}"enctype="multipart/form-data">
        @csrf

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="FullName" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Referred By</label>     
                <select name="ReferralID" class="form-select">
                <option value="">--Select a status--</option>
              @foreach ($referrals as $referral)
                <option value="{{ $referral->Id }}">
                  {{ $referral->ClientName }}
                </option>
              @endforeach
            </select>
          </div>
            <div class="col-md-3">
                <label class="form-label">National ID <span class="text-danger">*</span></label>
                <input type="text" name="NationalID" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">KRA PIN <span class="text-danger">*</span></label>
                <input type="text" name="KRAPIN" class="form-control" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Date of Birth </label>
                <input type="date" name="DateOfBirth" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Gender</label>
                <select name="Gender" class="form-select">
                <option value="">--Select a status--</option>
              @foreach ($genders as $gender)
                <option value="{{ $gender->ID }}">
                  {{ $gender->Description }}
                </option>
              @endforeach
            </select>
          </div>
            <div class="col-md-3">
                <label class="form-label">Marital Status</label>
                <select name="MaritalStatus" class="form-select">
                <option value="">--Select a status--</option>
              @foreach ($maritalstatus as $status)
                <option value="{{ $status->ID }}">
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
                <input type="email" name="Email" class="form-control" required>
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
@endsection
