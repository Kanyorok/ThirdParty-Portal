@extends('layouts.app')
@section('title', 'Register New Vehicle')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">🚘 Register New Vehicle</h4>

    <form action="{{ route('fleet.vehicles.store') }}" method="POST">
        @csrf

        <div class="row g-3">
            <div class="col-md-4">
                <label for="RegistrationNumber" class="form-label">Registration Number</label>
                <input type="text" class="form-control" name="RegistrationNumber" required>
            </div>

            <div class="col-md-4">
                <label for="VehicleTypeID" class="form-label">Vehicle Type</label>
                <select class="form-select" name="VehicleTypeID" required>
                    <option value="">Select Type</option>
                    @foreach ($vehicleTypes as $type)
                        <option value="{{ $type->ID }}">{{ $type->Description }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label for="FuelTypeID" class="form-label">Fuel Type</label>
                <select class="form-select" name="FuelTypeID" >
                    <option value="">Select Fuel</option>
                    @foreach ($fuelTypes as $fuel)
                        <option value="{{ $fuel->ID }}">{{ $fuel->Description }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label for="make">Vehicle Make</label>
                        <select class="form-control" id="make" name="Make" required>
                            <option value="">-- Select Make --</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->Id }}" {{ old('Make') == $brand->Id ? 'selected' : '' }}>
                                    {{ $brand->BrandName }}
                                </option>
                            @endforeach
                        </select>
            </div>

            <div class="col-md-4">
                <label for="model">Vehicle Model</label>
                        <select class="form-control" id="model" name="Model" required>
                            <option value="">-- Select Model --</option>
                            @foreach($fleetModels as $model)
                                <option value="{{ $model->Id }}" {{ old('Model') == $model->Id ? 'selected' : '' }}>
                                    {{ $model->ModelName }}
                                </option>
                            @endforeach
                        </select>
            </div>

            <div class="col-md-4">
                <label for="YearOfManufacture" class="form-label">Year</label>
                <input type="number" class="form-control" name="YearOfManufacture">
            </div>

            <div class="col-md-4">
                <label for="ChassisNumber" class="form-label">Chassis Number</label>
                <input type="text" class="form-control" name="ChassisNumber">
            </div>

            <div class="col-md-4">
                <label for="EngineNumber" class="form-label">Engine Number</label>
                <input type="text" class="form-control" name="EngineNumber">
            </div>

            <div class="col-md-4">
                <label for="Capacity" class="form-label">Capacity</label>
                <input type="text" class="form-control" name="Capacity">
            </div>

            <div class="col-md-4">
                <label for="OdometerReading" class="form-label">Odometer Reading</label>
                <input type="number" step="0.01" class="form-control" name="OdometerReading">
            </div>

            <div class="col-md-4">
                <label for="Status" class="form-label">Status</label>
                <select class="form-select" name="Status">
                    <option value="Active">Active</option>
                    <option value="Under Maintenance">Under Maintenance</option>
                    <option value="Retired">Retired</option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="AssignedBranchID" class="form-label">Assign to Branch</label>
                <select class="form-select" name="AssignedBranchID">
                    <option value="">-- None --</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->ID }}">{{ $branch->Name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-4">
            <button class="btn btn-success" type="submit">💾 Register Vehicle</button>
        </div>
    </form>
</div>
@endsection
