@extends('layouts.app')
@section('title', 'Register Contracted Driver')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">🧾 Register Contracted Driver</h4>

    <form action="{{ route('fleet.contracted_drivers.store') }}" method="POST">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="FullName" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">National ID</label>
                <input type="text" name="NationalID" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="Phone" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label">License Number</label>
                <input type="text" name="LicenseNumber" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label">License Expiry Date</label>
                <input type="date" name="LicenseExpiryDate" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label">License Category</label>
                <input type="text" name="LicenseCategory" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label">Contracted From</label>
                <input type="text" name="ContractedFrom" class="form-control" placeholder="Company or Source">
            </div>

            <div class="col-md-6">
                <label class="form-label">Remarks</label>
                <input type="text" name="Remarks" class="form-control">
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-success">💾 Save</button>
        </div>
    </form>
</div>
@endsection
