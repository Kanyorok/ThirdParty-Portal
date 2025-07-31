@extends('layouts.app')
@section('title', 'Edit Driver')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">🧑‍✈️ Edit Driver Details</h4>

    <form action="{{ route('fleet.drivers.update', $driver->DriverID) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label for="FullName" class="form-label">Full Name</label>
                <input type="text" name="FullName" class="form-control" value="{{ old('FullName', $driver->FullName) }}" required>
            </div>

            <div class="col-md-6">
                <label for="StaffNumber" class="form-label">Staff Number (optional)</label>
                <input type="text" name="StaffNumber" class="form-control" value="{{ old('StaffNumber', $driver->StaffNumber) }}">
            </div>

            <div class="col-md-6">
                <label for="NationalID" class="form-label">National ID</label>
                <input type="text" name="NationalID" class="form-control" value="{{ old('NationalID', $driver->NationalID) }}" required>
            </div>

            <div class="col-md-6">
                <label for="Phone" class="form-label">Phone</label>
                <input type="text" name="Phone" class="form-control" value="{{ old('Phone', $driver->Phone) }}" required>
            </div>

            <div class="col-md-6">
                <label for="LicenseNumber" class="form-label">License Number</label>
                <input type="text" name="LicenseNumber" class="form-control" value="{{ old('LicenseNumber', $driver->LicenseNumber) }}" required>
            </div>

            <div class="col-md-6">
                <label for="LicenseExpiryDate" class="form-label">License Expiry Date</label>
                <input type="date" name="LicenseExpiryDate" class="form-control" value="{{ old('LicenseExpiryDate', $driver->LicenseExpiryDate) }}" required>
            </div>

            <div class="col-md-6">
                <label for="LicenseCategory" class="form-label">License Category</label>
                <input type="text" name="LicenseCategory" class="form-control" value="{{ old('LicenseCategory', $driver->LicenseCategory) }}">
            </div>

            <div class="col-md-6">
                <label for="EmploymentType" class="form-label">Employment Type</label>
                <select name="EmploymentType" class="form-select" required>
                    <option value="">Select Type</option>
                    <option value="Permanent" {{ old('EmploymentType', $driver->EmploymentType) == 'Permanent' ? 'selected' : '' }}>Permanent</option>
                    <option value="Temporary" {{ old('EmploymentType', $driver->EmploymentType) == 'Temporary' ? 'selected' : '' }}>Temporary</option>
                    <option value="Contract" {{ old('EmploymentType', $driver->EmploymentType) == 'Contract' ? 'selected' : '' }}>Contract</option>
                </select>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-success">💾 Update Driver</button>
        </div>
    </form>
</div>
@endsection
