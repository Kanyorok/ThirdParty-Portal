@extends('layouts.app')
@section('title', 'Edit Maintenance Schedule')

@section('content')
<div class="card p-4 shadow rounded-4">


    <form method="POST" action="{{ route('fleet.maintenance_schedule.update', $schedule->Id) }}">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <!-- Vehicle -->
            <div class="col-md-6">
                <label for="VehicleID" class="form-label">Vehicle</label>
                <select name="VehicleID" id="VehicleID" class="form-select" required>
                    <option value="">-- Select Vehicle --</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->Id }}" 
                            {{ old('VehicleID', $schedule->VehicleID ?? '') == $vehicle->Id ? 'selected' : '' }}>
                            {{ $vehicle->RegistrationNo }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Maintenance Type -->
            <div class="col-md-6">
                <label for="MaintenanceType" class="form-label">Maintenance Type</label>
                <select name="MaintenanceType" id="MaintenanceType" class="form-select" required>
                    <option value="">-- Select Maintenance Type --</option>
                    @foreach($maintenanceType as $type)
                        <option value="{{ $type->ID }}" {{ old('MaintenanceType', $schedule->MaintenanceType) == $type->ID ? 'selected' : '' }}>
                            {{ $type->Description }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Scheduled Date -->
            <div class="col-md-6">
                <label for="ScheduledDate" class="form-label">Scheduled Date</label>
                <input type="date" name="ScheduledDate" id="ScheduledDate" class="form-control"
                       value="{{ old('ScheduledDate', \Carbon\Carbon::parse($schedule->ScheduledDate)->format('Y-m-d')) }}" required>
            </div>

            <!-- Scheduled Mileage -->
            <div class="col-md-6">
                <label for="ScheduledMileage" class="form-label">Scheduled Mileage</label>
                <input type="number" name="ScheduledMileage" id="ScheduledMileage" class="form-control"
                       value="{{ old('ScheduledMileage', $schedule->ScheduledMileage) }}">
            </div>

            <!-- Location -->
            <div class="col-md-6">
                <label for="Location" class="form-label">Location</label>
                <input type="text" name="Location" id="Location" class="form-control"
                       value="{{ old('Location', $schedule->Location) }}" required>
            </div>

            <!-- Notes -->
            <div class="col-md-12">
                <label for="Notes" class="form-label">Notes</label>
                <textarea name="Notes" id="Notes" class="form-control" rows="3">{{ old('Notes', $schedule->Notes) }}</textarea>
            </div>

            <!-- Status Checkbox -->
            <div class="col-md-6">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" name="Status" id="Status" value="1"
                           {{ old('Status', $schedule->Status) ? 'checked' : '' }}>
                    <label class="form-check-label" for="Status">Active</label>
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-success" type="submit">💾 Update Schedule</button>
            <a href="{{ route('fleet.maintenance_schedule.index') }}" class="btn btn-secondary">⬅ Back</a>
        </div>
    </form>
</div>
@endsection
