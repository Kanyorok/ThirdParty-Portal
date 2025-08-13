@extends('layouts.app')
@section('title', 'Register New Driver')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">🧑‍✈️ Register New Driver</h4>

    <form action="{{ route('fleet.drivers.store') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label for="FullName" class="form-label">Full Name</label>
                <input type="text" name="FullName" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label for="StaffNumber" class="form-label">Staff Number</label>
                <input type="text" name="StaffNumber" class="form-control">
            </div>

            <div class="col-md-6">
                <label for="NationalID" class="form-label">National ID</label>
                <input type="text" name="NationalID" class="form-control">
            </div>

            <div class="col-md-6">
                <label for="Phone" class="form-label">Phone Number</label>
                <input type="text" name="Phone" class="form-control">
            </div>

            <div class="col-md-6">
                <label for="Email" class="form-label">Email</label>
                <input type="email" name="Email" class="form-control">
            </div>

            <div class="col-md-6">
                <label for="LicenseNumber" class="form-label">Driver’s License Number</label>
                <input type="text" name="LicenseNumber" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label for="LicenseExpiryDate" class="form-label">License Expiry</label>
                <input type="date" name="LicenseExpiryDate" class="form-control">
            </div>

            <div class="col-md-6">
                <label for="LicenseCategory" class="form-label">License Category</label>
                <input type="text" name="LicenseCategory" class="form-control">
            </div>

            <div class="col-md-6">
                <label for="EmploymentType" class="form-label">Employment Type</label>
                <select name="EmploymentType" class="form-select">
                    <option value="">Select Type</option>
                    <option value="Permanent">Permanent</option>
                    <option value="Contract">Contract</option>
                    <option value="Hired">Hired</option>
                </select>
            </div>

            <div class="col-md-12">
                <label for="Notes" class="form-label">Notes</label>
                <textarea name="Notes" class="form-control" rows="2"></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button class="btn btn-success" type="submit">💾 Save Driver</button>
            <a href="{{ route('fleet.drivers.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
