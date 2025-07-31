@extends('layouts.app')
@section('title', 'Edit Vehicle')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">🛠️ Edit Vehicle - {{ $vehicle->RegistrationNumber }}</h4>

    <form action="{{ route('fleet.vehicles.update', $vehicle->VehicleID) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Registration Number</label>
                <input type="text" name="RegistrationNumber" class="form-control" value="{{ $vehicle->RegistrationNumber }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Vehicle Type</label>
                <select name="VehicleTypeID" class="form-select" required>
                    @foreach ($vehicleTypes as $type)
                        <option value="{{ $type->ID }}" {{ $vehicle->VehicleTypeID == $type->ID ? 'selected' : '' }}>
                            {{ $type->Name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Fuel Type</label>
                <select name="FuelTypeID" class="form-select" required>
                    @foreach ($fuelTypes as $fuel)
                        <option value="{{ $fuel->ID }}" {{ $vehicle->FuelTypeID == $fuel->ID ? 'selected' : '' }}>
                            {{ $fuel->Name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Make</label>
                <input type="text" name="Make" class="form-control" value="{{ $vehicle->Make }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Model</label>
                <input type="text" name="Model" class="form-control" value="{{ $vehicle->Model }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Year</label>
                <input type="number" name="YearOfManufacture" class="form-control" value="{{ $vehicle->YearOfManufacture }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Chassis Number</label>
                <input type="text" name="ChassisNumber" class="form-control" value="{{ $vehicle->ChassisNumber }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Engine Number</label>
                <input type="text" name="EngineNumber" class="form-control" value="{{ $vehicle->EngineNumber }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Capacity</label>
                <input type="text" name="Capacity" class="form-control" value="{{ $vehicle->Capacity }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Odometer Reading</label>
                <input type="number" step="0.01" name="OdometerReading" class="form-control" value="{{ $vehicle->OdometerReading }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="Status" class="form-select">
                    <option value="Active" {{ $vehicle->Status == 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Under Maintenance" {{ $vehicle->Status == 'Under Maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                    <option value="Retired" {{ $vehicle->Status == 'Retired' ? 'selected' : '' }}>Retired</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Assigned Branch</label>
                <select name="AssignedBranchID" class="form-select">
                    <option value="">-- None --</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->ID }}" {{ $vehicle->AssignedBranchID == $branch->ID ? 'selected' : '' }}>
                            {{ $branch->Name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <button type="submit" class="btn btn-success">💾 Update Vehicle</button>

            <form action="{{ route('fleet.vehicles.deactivate', $vehicle->VehicleID) }}" method="POST" onsubmit="return confirm('Are you sure you want to deregister this vehicle?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">🗑️ Deregister Vehicle</button>
            </form>
        </div>
    </form>
</div>
@endsection
