@extends('layouts.app')
@section('title', 'Edit Maintenance Schedule')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">✏️ Edit Maintenance Schedule</h4>

    <form method="POST" action="{{ route('fleet.maintenance_schedule.update', $schedule->ID) }}">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label for="VehicleID" class="form-label">Vehicle</label>
                <select name="VehicleID" class="form-select" required>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->VehicleID }}" {{ $vehicle->VehicleID == $schedule->VehicleID ? 'selected' : '' }}>
                            {{ $vehicle->RegistrationNumber }} - {{ $vehicle->Make }} {{ $vehicle->Model }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="MaintenanceType" class="form-label">Maintenance Type</label>
                <select name="MaintenanceType" class="form-select" required>
                    <option value="Routine" {{ $schedule->MaintenanceType == 'Routine' ? 'selected' : '' }}>Routine</option>
                    <option value="Inspection" {{ $schedule->MaintenanceType == 'Inspection' ? 'selected' : '' }}>Inspection</option>
                    <option value="Emergency" {{ $schedule->MaintenanceType == 'Emergency' ? 'selected' : '' }}>Emergency</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="ScheduledDate" class="form-label">Scheduled Date</label>
                <input type="date" name="ScheduledDate" class="form-control" value="{{ $schedule->ScheduledDate }}" required>
            </div>

            <div class="col-md-12">
                <label for="Notes" class="form-label">Notes</label>
                <textarea name="Notes" class="form-control" rows="3">{{ $schedule->Notes }}</textarea>
            </div>
        </div>

        <div class="mt-4">
            <button class="btn btn-success" type="submit">💾 Update Schedule</button>
            <a href="{{ route('fleet.maintenance_schedule.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>
@endsection
