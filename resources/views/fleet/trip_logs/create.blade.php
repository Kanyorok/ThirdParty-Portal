@extends('layouts.app')
@section('title', 'Log New Trip')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">📆 Log New Trip</h4>

        <form method="POST" action="{{ route('fleet.trip_logs.store') }}">
            @csrf


            <div class="row g-3">

                <div class="col-md-6">
                    <label for="TripStartDate" class="form-label">Trip Start Date</label>
                    <input type="date" name="TripStartDate" class="form-control"
                           value="{{ old('TripStartDate') }}" required>
                </div>
                <div class="col-md-6">
                    <label for="TripEndDate" class="form-label">Trip End Date</label>
                    <input type="date" name="TripEndDate" class="form-control"
                           value="{{ old('TripEndDate') }}" required>
                </div>

                <div class="col-md-4">
                    <label for="VehicleID" class="form-label">Select Vehicle</label>
                    <select name="VehicleID" id="VehicleID" class="form-select" required>
                        <option value="">-- Select Vehicle --</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->Id }}">{{ $vehicle->RegistrationNo }}</option>
                        @endforeach
                    </select>
                </div>


                {{-- Driver Type --}}
                <div class="col-md-4">
                    <label for="DriverType" class="form-label">Driver Type</label>
                    <select name="DriverType" id="DriverType" class="form-select" required>
                        <option value="">-- Select Driver Type --</option>
                        @foreach($driverTypes as $type)
                            <option value="{{ $type->ID }}"
                                {{ old('DriverType') == $type->ID ? 'selected' : '' }}>
                                {{ $type->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Driver --}}
                <div class="col-md-4">
                    <label for="DriverID" class="form-label">Select Driver</label>
                    <select name="DriverID" id="DriverID" class="form-select" required>
                        <option value="">-- Select Driver --</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->Id }}" data-type="Permanent"
                                {{ old('DriverID') == $driver->Id ? 'selected' : '' }}>
                                {{ $driver->FullName }}
                            </option>
                        @endforeach
                        @foreach($contractedDrivers as $cDriver)
                            <option value="{{ $cDriver->Id }}" data-type="Contracted"
                                {{ old('DriverID') == $cDriver->Id ? 'selected' : '' }}>
                                {{ $cDriver->FullName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Dates & Times --}}

                <div class="col-md-6">
                    <label for="StartTime" class="form-label">Start Time</label>
                    <input type="time" name="StartTime" class="form-control"
                           value="{{ old('StartTime') }}">
                </div>
                <div class="col-md-6">
                    <label for="EndTime" class="form-label">End Time</label>
                    <input type="time" name="EndTime" class="form-control"
                           value="{{ old('EndTime') }}">
                </div>

                {{-- Locations --}}
                <div class="col-md-6">
                    <label for="StartLocation" class="form-label">Start Location</label>
                    <input type="text" name="StartLocation" class="form-control"
                           value="{{ old('StartLocation') }}">
                </div>
                <div class="col-md-6">
                    <label for="EndLocation" class="form-label">End Location</label>
                    <input type="text" name="EndLocation" class="form-control"
                           value="{{ old('EndLocation') }}">
                </div>

                {{-- Route & Distance --}}
                <div class="col-md-6">
                    <label for="Route" class="form-label">Route</label>
                    <input type="text" name="Route" class="form-control" maxlength="255"
                           value="{{ old('Route') }}">
                </div>
                <div class="col-md-6">
                    <label for="DistanceCovered" class="form-label">Distance Covered (km)</label>
                    <input type="number" step="0.01" name="DistanceCovered" class="form-control"
                           value="{{ old('DistanceCovered') }}">
                </div>

                {{-- Purpose & Notes --}}
                <div class="col-md-12">
                    <label for="Purpose" class="form-label">Purpose</label>
                    <input type="text" name="Purpose" class="form-control" maxlength="255"
                           value="{{ old('Purpose') }}" required>
                </div>

                <div class="col-md-12">
                    <label for="Notes" class="form-label">Notes</label>
                    <textarea name="Notes" class="form-control" rows="3">{{ old('Notes') }}</textarea>
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
        const startDateInput = document.querySelector('[name="TripStartDate"]');
        const endDateInput = document.querySelector('[name="TripEndDate"]');
        const vehicleSelect = document.getElementById('VehicleID');
        const driverTypeSelect = document.getElementById('DriverType');
        const driverSelect = document.getElementById('DriverID');

        async function loadAvailableVehicles() {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;

            if (!startDate || !endDate) return;

            const response = await fetch(
                "{{ route('fleet.vehicles.available') }}?start_date=" + startDate + "&end_date=" + endDate
            );

            const vehicles = await response.json();

            // Clear old options
            vehicleSelect.innerHTML = '<option value="">-- Select Vehicle --</option>';

            // Populate new options
            vehicles.forEach(vehicle => {
                const option = document.createElement('option');
                option.value = vehicle.Id;
                option.textContent = `${vehicle.RegistrationNo}`;
                vehicleSelect.appendChild(option);
            });
        }

        async function loadAvailableDrivers() {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;
            const driverTypeId = driverTypeSelect.value;

            // Only proceed if both dates are selected and a driver type is chosen
            if (!startDate || !endDate || !driverTypeId) {
                driverSelect.innerHTML = '<option value="">-- Select Driver --</option>';
                return;
            }

            let url;
            if (driverTypeId == '{{ $driverTypes->firstWhere("Description", "Permanent")->ID }}') {
                url = "{{ route('fleet.drivers.available') }}?start_date=" + startDate + "&end_date=" + endDate;
            } else if (driverTypeId == '{{ $driverTypes->firstWhere("Description", "Contracted")->ID }}') {
                url = "{{ route('fleet.contracted_drivers.available') }}?start_date=" + startDate + "&end_date=" + endDate;
            } else {
                driverSelect.innerHTML = '<option value="">-- Select Driver --</option>';
                return;
            }

            const response = await fetch(url);
            const drivers = await response.json();

            driverSelect.innerHTML = '<option value="">-- Select Driver --</option>';
            drivers.forEach(driver => {
                const option = document.createElement('option');
                option.value = driver.Id;
                option.textContent = driver.FullName;
                driverSelect.appendChild(option);
            });
        }

        startDateInput.addEventListener('change', () => {
            loadAvailableVehicles();
            loadAvailableDrivers();
        });

        endDateInput.addEventListener('change', () => {
            loadAvailableVehicles();
            loadAvailableDrivers();
        });

        driverTypeSelect.addEventListener('change', loadAvailableDrivers);
    </script>
@endsection
