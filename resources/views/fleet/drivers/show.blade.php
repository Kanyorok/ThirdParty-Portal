@extends('layouts.app')
@section('title', 'Driver Details')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">Driver Details</h4>

    <div class="row g-3">

        <div class="col-md-3"><strong>Full Name:</strong> {{ $driver->FullName }}</div>
        <div class="col-md-3"><strong>Staff Member:</strong> {{ $driver->driver?->FullName }}</div>
        <div class="col-md-3"><strong>National ID:</strong> {{ $driver->NationalID }}</div>
        <div class="col-md-3"><strong>Phone:</strong> {{ $driver->Phone }}</div>

        <div class="col-md-3"><strong>License Number:</strong> {{ $driver->LicenseNumber }}</div>
        <div class="col-md-3"><strong>License Expiry:</strong> {{ $driver->LicenseExpiryDate }}</div>
        <div class="col-md-3"><strong>Employment Type:</strong> {{ $driver->employmentType?->Description }}</div>

        <div class="col-md-12"><strong>Notes:</strong> {{ $driver->Notes }}</div>

        <div class="col-md-3 mt-2">
            <strong>Status:</strong> 
            @if($driver->IsActive)
                <span class="badge bg-success">Active</span>
            @else
                <span class="badge bg-danger">Inactive</span>
            @endif
        </div>

    </div>

    <div class="mt-4">
        <a href="{{ route('fleet.drivers.edit', $driver->Id) }}" class="btn btn-warning">Edit</a>
        <a href="{{ route('fleet.drivers.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>
@endsection
