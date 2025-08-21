@extends('layouts.app')
@section('title', 'View Inspection Schedule')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Inspection Schedule Details</h4>
        <a href="{{ route('fleet.inspection_schedule.index') }}" class="btn btn-secondary">⬅ Back to List</a>
    </div>

    <dl class="row">
        <dt class="col-sm-3">Inspection No</dt>
        <dd class="col-sm-9">{{ $schedule->InspectionNo ?? '-' }}</dd>

        <dt class="col-sm-3">Vehicle</dt>
        <dd class="col-sm-9">{{ $schedule->vehicle->RegistrationNo ?? '-' }}</dd>

        <dt class="col-sm-3">Inspection Type</dt>
        <dd class="col-sm-9">{{ $schedule->InspectionType ?? '-' }}</dd>

        <dt class="col-sm-3">Inspection Date</dt>
        <dd class="col-sm-9">{{ $schedule->InspectionDate ? \Carbon\Carbon::parse($schedule->InspectionDate)->format('d/m/Y') : '-' }}</dd>

        <dt class="col-sm-3">Due Date</dt>
        <dd class="col-sm-9">{{ $schedule->DueDate ? \Carbon\Carbon::parse($schedule->DueDate)->format('d/m/Y') : '-' }}</dd>

        <dt class="col-sm-3">Status</dt>
        <dd class="col-sm-9">
            @php
                $statusColor = match(strtolower($schedule->inspectionStatus->Description ?? '')) {
                    'completed' => 'success',
                    'pending' => 'warning',
                    'overdue' => 'danger',
                    default => 'secondary',
                };
            @endphp
            <span class="badge bg-{{ $statusColor }}">
                {{ $schedule->inspectionStatus->Description ?? 'N/A' }}
            </span>
        </dd>

        <dt class="col-sm-3">Remarks</dt>
        <dd class="col-sm-9">{{ $schedule->Remarks ?? '-' }}</dd>

    </dl>

    <div class="mt-3">
        <a href="{{ route('fleet.inspection_schedule.edit', $schedule->Id) }}" class="btn btn-warning">Edit</a>
        <form action="{{ route('fleet.inspection_schedule.destroy', $schedule->Id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this schedule?')">
            @csrf @method('DELETE')
            <button class="btn btn-danger">Delete</button>
        </form>
    </div>
</div>
@endsection
