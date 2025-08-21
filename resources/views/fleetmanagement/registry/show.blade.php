@extends('layouts.app')
@section('title', 'Vehicle Details')
@section('content')

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>{{ $vehicle->RegistrationNo }} Information</h4>
        <a href="{{ route('vehicle-registry.index') }}" class="btn btn-secondary">Back to Vehicle List</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>Registration Number:</strong> {{ $vehicle->RegistrationNo }}</div>
                <div class="col-md-3"><strong>Make:</strong> {{ $vehicle->brand->BrandName ?? 'N/A' }}</div>
                <div class="col-md-3"><strong>Model:</strong> {{ $vehicle->model->ModelName ?? 'N/A' }}</div>
                <div class="col-md-3"><strong>Type:</strong> {{ $vehicle->vehicleType->Description ?? 'N/A' }}</div>
            </div>

            <div class="row mt-3">
                <div class="col-md-3"><strong>Color:</strong> {{ $vehicle->Color ?? 'N/A' }}</div>
                <div class="col-md-3"><strong>Year:</strong> {{ $vehicle->Year ?? 'N/A' }}</div>
                <div class="col-md-3"><strong>Chassis No:</strong> {{ $vehicle->ChassisNo }}</div>
                <div class="col-md-3"><strong>Engine No:</strong> {{ $vehicle->EngineNo ?? 'N/A' }}</div>
            </div>

            <div class="mt-4">
                <a href="{{ route('vehicle-registry.edit', $vehicle->Id) }}" class="btn btn-warning">Edit</a>
                <a href="{{ route('vehicle-registry.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection
