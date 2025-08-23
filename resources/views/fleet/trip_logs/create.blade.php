@extends('layouts.app')
@section('title', 'Log New Trip')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">📆 Log New Trip</h4>

        <form method="POST" action="{{ route('fleet.trip_logs.store') }}">
            @csrf

            <div class="row g-3">
                {{-- First row: Vehicle, Driver Type, Driver --}}
                <div class="col-md-4">
                    <label for="VehicleID" class="form-label">Select Vehicle</label>
                    <select name="VehicleID" class="form-select" required>
                        <option value="">-- Select Vehicle --</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->Id }}">{{ $vehicle->RegistrationNo }}
                                - {{ $vehicle->Make }} {{ $vehicle->Model }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="DriverType" class="form-label">Driver Type</label>
                    <select name="DriverType" id="DriverType" class="form-select" required>
                        <option value="">-- Select Driver Type --</option>
                        @foreach($driverTypes as $type)
                            <option value="{{ $type->ID }}">{{ $type->Description }}</option> {{-- ID, not text --}}
                        @endforeach
                    </select>

                </div>

                <div class="col-md-4">
                    <label for="DriverID" class="form-label">Select Driver</label>
                    <select name="DriverID" id="DriverID" class="form-select" required>
                        <option value="">-- Select Driver --</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->Id }}" data-type="Permanent">{{ $driver->FullName }}</option>
                        @endforeach
                        @foreach($contractedDrivers as $cDriver)
                            <option value="{{ $cDriver->Id }}" data-type="Contracted">{{ $cDriver->FullName }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Next four rows, 2 columns each --}}
                <div class="col-md-6">
                    <label for="TripStartDate" class="form-label">Trip Start Date</label>
                    <input type="date" name="TripStartDate" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="TripEndDate" class="form-label">Trip End Date</label>
                    <input type="date" name="TripEndDate" class="form-control" required>
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
                    <label for="Route" class="form-label">Route</label>
                    <input type="text" name="Route" class="form-control" maxlength="255">
                </div>
                <div class="col-md-6">
                    <label for="DistanceCovered" class="form-label">Distance Covered (km)</label>
                    <input type="number" step="0.01" name="DistanceCovered" class="form-control">
                </div>

                {{-- Remaining 2 fields: Purpose, Notes --}}
                <div class="col-md-12">
                    <label for="Purpose" class="form-label">Purpose</label>
                    <input type="text" name="Purpose" class="form-control" maxlength="255" required>
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

@section('scripts')
    <script>
        const driverTypeSelect = document.getElementById('DriverType');
        const driverSelect = document.getElementById('DriverID');

        function filterDrivers() {
            const selectedType = driverTypeSelect.options[driverTypeSelect.selectedIndex].text;

            for (let option of driverSelect.options) {
                if (!option.value) continue;
                option.style.display = option.dataset.type === selectedType ? 'block' : 'none';
            }

            driverSelect.value = '';
        }

        driverTypeSelect.addEventListener('change', filterDrivers);
        window.addEventListener('DOMContentLoaded', filterDrivers);
    </script>
@endsection
