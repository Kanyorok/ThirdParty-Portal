@extends('layouts.app')
@section('title', 'Edit Contracted Driver')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">📝 Edit Contracted Driver</h4>

    <form action="{{ route('fleet.contracted_drivers.update', $driver->ID) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="FullName" class="form-control" value="{{ $driver->FullName }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">National ID</label>
                <input type="text" name="NationalID" class="form-control" value="{{ $driver->NationalID }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="Phone" class="form-control" value="{{ $driver->Phone }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">License Number</label>
                <input type="text" name="LicenseNumber" class="form-control" value="{{ $driver->LicenseNumber }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">License Expiry Date</label>
                <input type="date" name="LicenseExpiryDate" class="form-control" value="{{ $driver->LicenseExpiryDate }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">License Category</label>
                <input type="text" name="LicenseCategory" class="form-control" value="{{ $driver->LicenseCategory }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Contracted From</label>
                <input type="text" name="ContractedFrom" class="form-control" value="{{ $driver->ContractedFrom }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Remarks</label>
                <input type="text" name="Remarks" class="form-control" value="{{ $driver->Remarks }}">
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">✅ Update</button>
        </div>
    </form>
</div>
@endsection
