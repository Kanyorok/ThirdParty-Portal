@extends('layouts.app')
@section('title', 'Schedule Inspection')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">📝 Schedule Inspection</h4>

    <form action="{{ route('fleet.inspection_schedule.store') }}" method="POST">
        @csrf

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="VehicleID" class="form-label">Vehicle</label>
                <select name="VehicleID" id="VehicleID" class="form-select" required>
                    <option value="">-- Select Vehicle --</option>
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->VehicleID }}">{{ $vehicle->RegistrationNumber }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="InspectionType" class="form-label">Inspection Type</label>
                <input type="text" name="InspectionType" id="InspectionType" class="form-control" required placeholder="e.g., Roadworthiness">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="InspectionDate" class="form-label">Inspection Date</label>
                <input type="date" name="InspectionDate" id="InspectionDate" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label for="ExpiryDate" class="form-label">Expiry Date</label>
                <input type="date" name="ExpiryDate" id="ExpiryDate" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="Status" class="form-label">Status</label>
                <select name="Status" id="Status" class="form-select" required>
                    <option value="">-- Select Status --</option>
                    <option value="Scheduled">Scheduled</option>
                    <option value="Completed">Completed</option>
                    <option value="Pending">Pending</option>
                    <option value="Failed">Failed</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="Remarks" class="form-label">Remarks</label>
                <input type="text" name="Remarks" id="Remarks" class="form-control" placeholder="Optional notes">
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-success">✅ Save Schedule</button>
        </div>
    </form>
</div>
@endsection
