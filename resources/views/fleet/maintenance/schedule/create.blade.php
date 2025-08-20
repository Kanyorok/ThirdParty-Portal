@extends('layouts.app')
@section('title', 'Schedule Maintenance')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">🛠️ Schedule Maintenance</h4>

    <form method="POST" action="{{ route('fleet.maintenance_schedule.store') }}">
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
                <label for="MaintenanceType" class="form-label">Maintenance Type</label>
                <select name="MaintenanceType" class="form-select" required>
                    <option value="">-- Select Type --</option>
                    <option value="Routine">Routine</option>
                    <option value="Inspection">Inspection</option>
                    <option value="Emergency">Emergency</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="ScheduledDate" class="form-label">Scheduled Date</label>
                <input type="date" name="ScheduledDate" class="form-control" required>
            </div>

            <div class="col-md-12">
                <label for="Notes" class="form-label">Additional Notes</label>
                <textarea name="Notes" class="form-control" rows="3"></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button class="btn btn-success" type="submit">💾 Save Schedule</button>
            <a href="{{ route('fleet.maintenance_schedule.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
