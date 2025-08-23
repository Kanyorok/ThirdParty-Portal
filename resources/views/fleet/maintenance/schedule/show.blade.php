@extends('layouts.app')
@section('title', 'Schedule Maintenance Details')

@section('content')
    <div class="card p-4 shadow rounded-4">


        <div class="row g-3">
            <div class="col-md-6">
                <strong>Schedule ID:</strong>
                <p>{{ $schedule->ScheduleID }}</p>
            </div>

            <div class="col-md-6">
                <strong>Vehicle:</strong>
                <p>{{ $schedule->vehicle->RegistrationNo ?? 'N/A' }}</p>
            </div>

            <div class="col-md-6">
                <strong>Maintenance Type:</strong>
                <p>{{ $schedule->maintenanceType->Description ?? 'N/A' }}</p>
            </div>

            <div class="col-md-6">
                <strong>Scheduled Date:</strong>
                <p>{{ \Carbon\Carbon::parse($schedule->ScheduledDate)->format('d/m/Y') }}</p>
            </div>

            <div class="col-md-6">
                <strong>Scheduled Mileage:</strong>
                <p>{{ $schedule->ScheduledMileage ?? 'N/A' }}</p>
            </div>

            <div class="col-md-6">
                <strong>Location:</strong>
                <p>{{ $schedule->Location }}</p>
            </div>

            <div class="col-md-12">
                <strong>Notes:</strong>
                <p>{{ $schedule->Notes ?? 'None' }}</p>
            </div>

            <div class="col-md-6">
                <strong>Status:</strong>
                <p>
                    @if($schedule->Status)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="mt-4">
            <a href="{{ route('fleet.maintenance_schedule.index') }}" class="btn btn-secondary">⬅ Back</a>
            <a href="{{ route('fleet.maintenance_schedule.edit', $schedule->ScheduleID) }}" class="btn btn-warning">✏
                Edit</a>
        </div>
    </div>
@endsection
