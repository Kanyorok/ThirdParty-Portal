@extends('layouts.app')
@section('title', 'Vehicle Details')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">🚘 Vehicle Details - {{ $vehicle->RegistrationNo }}</h4>

    <table class="table table-striped">
        <tr><th>Registration No:</th><td>{{ $vehicle->RegistrationNo }}</td></tr>
        <tr><th>Make:</th><td>{{ $vehicle->brand->BrandName }}</td></tr>
        <tr><th>Model:</th><td>{{ $vehicle->model->ModelName }}</td></tr>
        <tr><th>Type:</th><td>{{ $vehicle->vehicleType->Description }}</td></tr>
        <tr><th>Fuel:</th><td>{{ $vehicle->fuelType->Description }}</td></tr>
        <tr><th>Year:</th><td>{{ $vehicle->YearOfManufacture }}</td></tr>
        <tr><th>Chassis No:</th><td>{{ $vehicle->ChassisNo }}</td></tr>
        <tr><th>Engine No:</th><td>{{ $vehicle->EngineNo }}</td></tr>
        <tr><th>Capacity:</th><td>{{ $vehicle->Capacity }}</td></tr>
        <tr><th>Odometer:</th><td>{{ $vehicle->OdometerReading }}</td></tr>
        <tr><th>Status:</th><td>{{ $vehicle->status->Description }}</td></tr>
        <tr><th>Branch:</th><td>{{ $vehicle->branch->Name ?? '-' }}</td></tr>
        <p><strong>Is Active?:</strong>
                @if($vehicle->IsActive)
                    <span class="badge bg-success">Yes</span>
                @else
                    <span class="badge bg-danger">No</span>
                @endif
            </p>
    </table>

    <div class="mt-3">
        <a href="{{ route('fleet.vehicles.index') }}" class="btn btn-secondary">⬅ Back</a>
        <a href="{{ route('fleet.vehicles.edit', $vehicle->Id) }}" class="btn btn-warning">✏ Edit</a>
    </div>
</div>
@endsection
