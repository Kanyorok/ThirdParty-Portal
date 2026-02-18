@extends('layouts.app')

@section('title', 'Organization Profile')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Organization Profile</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('hr.config.org.update') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Organization Name *</label>
                        <input type="text" name="BankName" class="form-control" value="{{ old('BankName', $profile->BankName ?? '') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Short Name</label>
                        <input type="text" name="ShortName" class="form-control" value="{{ old('ShortName', $profile->ShortName ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Registration No.</label>
                        <input type="text" name="BankRegNumber" class="form-control" value="{{ old('BankRegNumber', $profile->BankRegNumber ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Employer Tax PIN</label>
                        <input type="text" name="EmployerTaxPIN" class="form-control" value="{{ old('EmployerTaxPIN', $profile->EmployerTaxPIN ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bank Code</label>
                        <input type="text" name="BankCode" class="form-control" value="{{ old('BankCode', $profile->BankCode ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Swift Code</label>
                        <input type="text" name="SwiftCode" class="form-control" value="{{ old('SwiftCode', $profile->SwiftCode ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Clearing Code</label>
                        <input type="text" name="ClearingCode" class="form-control" value="{{ old('ClearingCode', $profile->ClearingCode ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Address 1</label>
                        <input type="text" name="Address1" class="form-control" value="{{ old('Address1', $profile->Address1 ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Address 2</label>
                        <input type="text" name="Address2" class="form-control" value="{{ old('Address2', $profile->Address2 ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Zip / Postal Code</label>
                        <input type="text" name="ZipCode" class="form-control" value="{{ old('ZipCode', $profile->ZipCode ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="Phone1" class="form-control" value="{{ old('Phone1', $profile->Phone1 ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="EmailID" class="form-control" value="{{ old('EmailID', $profile->EmailID ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Website</label>
                        <input type="text" name="Website" class="form-control" value="{{ old('Website', $profile->Website ?? '') }}">
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Save Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
