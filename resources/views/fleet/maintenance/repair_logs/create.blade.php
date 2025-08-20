@extends('layouts.app')
@section('title', 'Log Repair Entry')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">🔧 Log Repair Entry</h4>

    <form method="POST" action="{{ route('fleet.repair_logs.store') }}">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label for="VehicleID" class="form-label">Vehicle</label>
                <select name="VehicleID" class="form-select" required>
                    <option value="">-- Select Vehicle --</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->VehicleID }}">{{ $vehicle->RegistrationNumber }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="RepairType" class="form-label">Repair Type</label>
                <select name="RepairType" class="form-select" required>
                    <option value="">-- Select Type --</option>
                    <option value="Normal">Normal</option>
                    <option value="Emergency">Emergency</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="RepairDate" class="form-label">Repair Date</label>
                <input type="date" name="RepairDate" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label for="ScheduleID" class="form-label">Linked Maintenance Schedule (optional)</label>
                <select name="ScheduleID" class="form-select">
                    <option value="">-- None --</option>
                    @foreach($schedules as $schedule)
                        <option value="{{ $schedule->ID }}">
                            {{ $schedule->vehicle->RegistrationNumber ?? 'Unknown' }} | {{ $schedule->ScheduledDate }} - {{ $schedule->MaintenanceType }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="Vendor" class="form-label">Vendor</label>
                <input type="text" name="Vendor" class="form-control">
            </div>

            <div class="col-md-6">
                <label for="Cost" class="form-label">Cost (KES)</label>
                <input type="number" step="0.01" name="Cost" class="form-control">
            </div>

            <div class="col-md-12">
                <label for="Description" class="form-label">Description</label>
                <textarea name="Description" class="form-control" rows="3"></textarea>
            </div>

            <div class="col-md-12">
                <label for="Notes" class="form-label">Notes</label>
                <textarea name="Notes" class="form-control" rows="2"></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-success">💾 Save Repair Log</button>
        </div>
    </form>
</div>
@endsection
