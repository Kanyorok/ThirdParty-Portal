@extends('layouts.app')
@section('title', 'Log New Trip')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">📆 Log New Trip</h4>

    <form method="POST" action="{{ route('fleet.trip_logs.store') }}">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label for="VehicleID" class="form-label">Select Vehicle</label>
                <select name="VehicleID" class="form-select" required>
                    <option value="">-- Select Vehicle --</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->VehicleID }}">{{ $vehicle->RegistrationNumber }} - {{ $vehicle->Make }} {{ $vehicle->Model }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="DriverType" class="form-label">Driver Type</label>
                <select name="DriverType" class="form-select" required>
                    <option value="">-- Select Driver Type --</option>
                    <option value="Permanent">Permanent</option>
                    <option value="Contracted">Contracted</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="DriverID" class="form-label">Select Driver</label>
                <select name="DriverID" class="form-select" required>
                    <option value="">-- Select Driver --</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->ID }}">{{ $driver->FullName }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="TripDate" class="form-label">Trip Date</label>
                <input type="date" name="TripDate" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label for="StartTime" class="form-label">Start Time</label>
                <input type="time" name="StartTime" class="form-control">
            </div>

            <div class="col-md-6">
                <label for="EndTime" class="form-label">End Time</label>
                <input type="time" name="EndTime" class="form-control">
            </div>

            <div class="col-md-6">
                <label for="StartLocation" class="form-label">Start Location</label>
                <input type="text" name="StartLocation" class="form-control">
            </div>

            <div class="col-md-6">
                <label for="EndLocation" class="form-label">End Location</label>
                <input type="text" name="EndLocation" class="form-control">
            </div>

            <div class="col-md-6">
                <label for="DistanceCovered" class="form-label">Distance Covered (km)</label>
                <input type="number" step="0.01" name="DistanceCovered" class="form-control">
            </div>

            <div class="col-md-12">
                <label for="Purpose" class="form-label">Purpose</label>
                <input type="text" name="Purpose" class="form-control" maxlength="255">
            </div>

            <div class="col-md-12">
                <label for="Notes" class="form-label">Notes</label>
                <textarea name="Notes" class="form-control" rows="3"></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button class="btn btn-success" type="submit">💾 Save Trip</button>
        </div>
    </form>
</div>
@endsection
