@extends('layouts.app')
@section('title', 'Contracted Driver Details')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">👤 Contracted Driver Profile</h4>
        <a href="{{ route('fleet.contracted_drivers.index') }}" class="btn btn-secondary">← Back to List</a>
    </div>

    <div class="row g-3">
        <div class="col-md-4"><strong>Full Name:</strong> {{ $driver->FullName }}</div>
        <div class="col-md-4"><strong>ID Number:</strong> {{ $driver->NationalID }}</div>
        <div class="col-md-4"><strong>Phone:</strong> {{ $driver->Phone }}</div>
        <div class="col-md-4"><strong>License Number:</strong> {{ $driver->LicenseNumber }}</div>
        <div class="col-md-4"><strong>Expiry Date:</strong> {{ $driver->LicenseExpiryDate }}</div>
        <div class="col-md-4"><strong>License Category:</strong> {{ $driver->LicenseCategory }}</div>
        <div class="col-md-4"><strong>Contract Start:</strong> {{ $driver->ContractStartDate }}</div>
        <div class="col-md-4"><strong>Contract End:</strong> {{ $driver->ContractEndDate }}</div>
        <div class="col-md-4"><strong>Is Active:</strong> {{ $driver->IsActive ? 'Yes' : 'No' }}</div>
    </div>

    <hr class="my-4">

    <div class="d-flex flex-wrap gap-2">
<a href="{{ route('fleet.contracted_driver_assignments.create', ['driverId' => $driver->ID]) }}" class="btn btn-success">
    🚚 Assign Vehicle
</a>

<a href="{{ route('fleet.contracted_driver_assignments.index', ['driverId' => $driver->ID]) }}" class="btn btn-info">
    📜 View Assignments
</a>

<a href="{{ route('fleet.contracted_driver_licenses.index', ['driverId' => $driver->ID]) }}" class="btn btn-warning">
    🪪 License Tracker
</a>
        <form action="{{ route('fleet.contracted_drivers.deactivate', $driver->ID) }}" method="POST" onsubmit="return confirm('Deactivate this contracted driver?');">
            @csrf
            @method('PUT')
            <button type="submit" class="btn btn-danger">Deactivate</button>
        </form>
    </div>
</div>
@endsection
