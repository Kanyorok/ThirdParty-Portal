@extends('layouts.app')
@section('title', 'Log Fuel Entry')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">⛽ Log New Fuel Entry</h4>

    <form method="POST" action="{{ route('fleet.fuel_logs.store') }}">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label for="VehicleID" class="form-label">Vehicle</label>
                <select name="VehicleID" class="form-select" required>
                    <option value="">-- Select Vehicle --</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->VehicleID }}">{{ $vehicle->RegistrationNumber }} - {{ $vehicle->Make }} {{ $vehicle->Model }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="TripID" class="form-label">Related Trip (optional)</label>
                <select name="TripID" class="form-select">
                    <option value="">-- Select Trip --</option>
                    @foreach($trips as $trip)
                        <option value="{{ $trip->TripID }}">Trip #{{ $trip->TripID }} | {{ $trip->TripDate }} | {{ $trip->Purpose }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label for="LogDate" class="form-label">Log Date</label>
                <input type="date" name="LogDate" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label for="OdometerStart" class="form-label">Odometer Start</label>
                <input type="number" name="OdometerStart" class="form-control">
            </div>

            <div class="col-md-4">
                <label for="OdometerEnd" class="form-label">Odometer End</label>
                <input type="number" name="OdometerEnd" class="form-control">
            </div>

            <div class="col-md-3">
                <label for="FuelAmount" class="form-label">Fuel Amount</label>
                <input type="number" step="0.01" name="FuelAmount" class="form-control" required>
            </div>

            <div class="col-md-3">
                <label for="FuelUnit" class="form-label">Unit</label>
                <select name="FuelUnit" class="form-select" required>
                    <option value="Litres">Litres</option>
                    <option value="Gallons">Gallons</option>
                </select>
            </div>

<div class="col-md-6">
    <label for="FuelTypeID" class="form-label">Fuel Type</label>
    <select name="FuelTypeID" class="form-select" required>
        <option value="">-- Select Fuel Type --</option>
        @foreach($fuelTypes as $type)
            <option value="{{ $type->ID }}">{{ $type->Name }}</option>
        @endforeach
    </select>
</div>

            <div class="col-md-3">
                <label for="Vendor" class="form-label">Vendor (Optional)</label>
                <input type="text" name="Vendor" class="form-control">
            </div>

            <div class="col-md-12">
                <label for="Notes" class="form-label">Notes</label>
                <textarea name="Notes" class="form-control" rows="3"></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-success">💾 Save Fuel Log</button>
        </div>
    </form>
</div>
@endsection
