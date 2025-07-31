@extends('layouts.app')
@section('title', 'Fleet Vehicles')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">🚘 Fleet Vehicle List</h4>
        <a href="{{ route('fleet.vehicles.create') }}" class="btn btn-primary">
            + Register New Vehicle
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Reg. Number</th>
                    <th>Make & Model</th>
                    <th>Type</th>
                    <th>Fuel</th>
                    <th>Capacity</th>
                    <th>Status</th>
                    <th>Branch</th>
                    <th>Odometer</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($vehicles as $vehicle)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $vehicle->RegistrationNumber }}</td>
                        <td>{{ $vehicle->Make }} {{ $vehicle->Model }}</td>
                        <td>{{ $vehicle->VehicleTypeName ?? '-' }}</td>
                        <td>{{ $vehicle->FuelTypeName ?? '-' }}</td>
                        <td>{{ $vehicle->Capacity }}</td>
                        <td>{{ $vehicle->Status }}</td>
                        <td>{{ $vehicle->Name ?? '-' }}</td>
                        <td>{{ number_format($vehicle->OdometerReading, 2) }} km</td>
                        <td>
    <a href="{{ route('fleet.vehicles.edit', $vehicle->VehicleID) }}" class="btn btn-sm btn-warning mb-1">
        Edit
    </a>

    <a href="{{ route('fleet.assignments.create', ['vehicle_id' => $vehicle->VehicleID]) }}" class="btn btn-sm btn-info mb-1">
        Assign
    </a>

    <a href="{{ route('fleet.documents.index', ['vehicle_id' => $vehicle->VehicleID]) }}" class="btn btn-sm btn-secondary mb-1">
       Documents
    </a>
</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">No vehicles registered.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
