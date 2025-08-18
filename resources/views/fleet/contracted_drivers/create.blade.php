@extends('layouts.app')
@section('title', 'Contracted Driver Registration')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">🧾 Register New Contracted Driver</h4>

    <form action="{{ route('fleet.contracted_drivers.store') }}" method="POST">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="FullName" class="form-control @error('FullName') is-invalid @enderror" value="{{ old('FullName') }}" required>
                @error('FullName')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">National ID</label>
                <input type="text" name="NationalID" class="form-control @error('NationalID') is-invalid @enderror" value="{{ old('NationalID') }}">
                @error('NationalID')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="Phone" class="form-control @error('Phone') is-invalid @enderror" value="{{ old('Phone') }}">
                @error('Phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Company Name</label>
                <input type="text" name="CompanyName" class="form-control @error('CompanyName') is-invalid @enderror" value="{{ old('CompanyName') }}">
                @error('CompanyName')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Contract Start Date</label>
                <input type="date" name="ContractStartDate" class="form-control @error('ContractStartDate') is-invalid @enderror" value="{{ old('ContractStartDate') }}">
                @error('ContractStartDate')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Contract End Date</label>
                <input type="date" name="ContractEndDate" class="form-control @error('ContractEndDate') is-invalid @enderror" value="{{ old('ContractEndDate') }}">
                @error('ContractEndDate')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">License Number</label>
                <input type="text" name="LicenseNumber" class="form-control @error('LicenseNumber') is-invalid @enderror" value="{{ old('LicenseNumber') }}">
                @error('LicenseNumber')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Notes</label>
                <textarea name="Notes" class="form-control @error('Notes') is-invalid @enderror" rows="2">{{ old('Notes') }}</textarea>
                @error('Notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-success">💾 Save</button>
        </div>
    </form>
</div>
@endsection
