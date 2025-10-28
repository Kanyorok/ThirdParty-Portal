@extends('layouts.app')
@section('title', 'View Vehicle Assignment')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">📄 Vehicle Assignment Details</h4>

        <div class="row g-4">
            {{-- Trip --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">Trip</label>
                <p class="form-control-plaintext text-muted mb-0">
                    {{ $assignment->trip?->TripNo }}
                    ({{ $assignment->trip?->StartLocation }} → {{ $assignment->trip?->EndLocation }})
                </p>
            </div>

            {{-- Vehicle Type --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">Vehicle Type</label>
                <p class="form-control-plaintext text-muted mb-0">
                    {{ $assignment->fleetVehicleType?->Description ?? 'N/A' }}
                </p>
            </div>

            {{-- Vehicle --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">Vehicle</label>
                <p class="form-control-plaintext text-muted mb-0">
                    {{ $assignment->vehicle?->RegistrationNo ?? 'N/A' }}
                </p>
            </div>

            {{-- Driver --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">Driver</label>
                <p class="form-control-plaintext text-muted mb-0">
                    {{ $assignment->driver?->FullName ?? 'No driver assigned' }}
                </p>
            </div>

            {{-- Last Inspection Date --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">Last Inspection Date</label>
                <p class="form-control-plaintext text-muted mb-0">
                    {{ $assignment->LastInspectionDate ? \Carbon\Carbon::parse($assignment->LastInspectionDate)->format('d M Y') : 'N/A' }}
                </p>
            </div>

            {{-- Assignment Date --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">Assignment Date</label>
                <p class="form-control-plaintext text-muted mb-0">
                    {{ $assignment->AssignmentDate ? \Carbon\Carbon::parse($assignment->AssignmentDate)->format('d M Y') : 'N/A' }}
                </p>
            </div>

            {{-- Purpose --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">Purpose</label>
                <p class="form-control-plaintext text-muted mb-0">
                    {{ $assignment->Purpose ?? '-' }}
                </p>
            </div>

            {{-- Notes --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">Notes</label>
                <p class="form-control-plaintext text-muted mb-0">
                    {{ $assignment->Notes ?? '-' }}
                </p>
            </div>

            {{-- Assigned By --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">Assigned By</label>
                <p class="form-control-plaintext text-muted mb-0">
                    {{ $assignment->assigner?->FullName ?? 'N/A' }}
                </p>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <a href="{{ route('fleet.assignments.edit', $assignment->Id) }}" class="btn btn-warning">✏️ Edit</a>
            <a href="{{ route('fleet.assignments.index') }}" class="btn btn-secondary">⬅ Back to List</a>
        </div>
    </div>
@endsection
