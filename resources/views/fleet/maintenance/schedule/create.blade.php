@extends('layouts.app')
@section('title', 'Schedule Maintenance')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <form method="POST"
              action="{{ isset($schedule) ? route('fleet.maintenance_schedule.update', $schedule->Id) : route('fleet.maintenance_schedule.store') }}">
            @csrf
            @if(isset($schedule))
                @method('PUT')
            @endif

            <div class="row g-3">
                <!-- Vehicle -->
                <div class="col-md-6">
                    <label for="VehicleID" class="form-label">Select Vehicle</label>
                    <select name="VehicleID" id="VehicleID" class="form-select" required>
                        <option value="">-- Select Vehicle --</option>
                        @foreach($vehicles as $vehicle)
                            <option
                                value="{{ $vehicle->Id }}" {{ (isset($schedule) && $schedule->VehicleID == $vehicle->Id) ? 'selected' : '' }}>
                                {{ $vehicle->RegistrationNo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Maintenance Type -->
                <div class="col-md-6">
                    <label for="MaintenanceType" class="form-label">Select Maintenance Type</label>
                    <select name="MaintenanceType" id="MaintenanceType" class="form-select" required>
                        <option value="">-- Select Maintenance Type --</option>
                        @foreach($maintenanceType as $type)
                            <option
                                value="{{ $type->ID }}" {{ (isset($schedule) && $schedule->MaintenanceType == $type->ID) ? 'selected' : '' }}>
                                {{ $type->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Scheduled Date -->
                <div class="col-md-6">
                    <label for="ScheduledDate" class="form-label">Scheduled Date</label>
                    <input type="date" name="ScheduledDate" id="ScheduledDate" class="form-control" required
                           value="{{ $schedule->ScheduledDate ?? '' }}">
                </div>

                <!-- Scheduled Mileage -->
                <div class="col-md-6">
                    <label for="ScheduledMileage" class="form-label">Scheduled Mileage (km)</label>
                    <input type="number" name="ScheduledMileage" id="ScheduledMileage" class="form-control"
                           placeholder="Enter mileage" value="{{ $schedule->ScheduledMileage ?? '' }}">
                </div>

                <!-- Location -->
                <div class="col-md-6">
                    <label for="Location" class="form-label">Location</label>
                    <input type="text" name="Location" id="Location" class="form-control"
                           value="{{ $schedule->Location ?? '' }}">
                </div>

                <!-- Notes -->
                <div class="col-md-12">
                    <label for="Notes" class="form-label">Additional Notes</label>
                    <textarea name="Notes" id="Notes" class="form-control"
                              rows="3">{{ $schedule->Notes ?? '' }}</textarea>
                </div>

                <!-- Maintenance Status (read-only when editing) -->
                @if(isset($schedule))
                    <div class="col-md-6">
                        <label class="form-label">Maintenance Status</label>
                        <input type="text" class="form-control" value="{{ $schedule->MaintenanceStatus }}" readonly>
                    </div>
                @endif

                <!-- Active Status -->
                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="Status" id="Status" value="1"
                            {{ isset($schedule) && $schedule->Status ? 'checked' : '' }}>
                        <label class="form-check-label" for="Status">Active</label>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-4">
                <button class="btn btn-success" type="submit">💾 Save Schedule</button>
                <a href="{{ route('fleet.maintenance_schedule.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
