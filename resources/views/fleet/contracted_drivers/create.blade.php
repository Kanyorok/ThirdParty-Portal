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
                <label for="Company" class="form-label">Company Name</label>
                <select name="Company" class="form-select">
                    <option value="">Select Company</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->Id }}" {{ old('Company') == $company->Id ? 'selected' : '' }}>
                            {{ $company->SupplierName }}
                        </option>
                    @endforeach
                </select>
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
            <!-- Active Checkbox -->
            <div class="col-md-12 mt-3">
                <div class="form-check">
                    <input type="hidden" name="IsActive" value="0">
                    <input type="checkbox" name="IsActive" class="form-check-input" id="IsActive" value="1" {{ old('IsActive', 1) ? 'checked' : '' }}>
                    <label class="form-check-label" for="IsActive">Active</label>
                </div>
                @error('IsActive')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>


        

        <div class="mt-4">
            <button type="submit" class="btn btn-success">💾 Save</button>
        </div>
    </form>
</div>
@endsection
