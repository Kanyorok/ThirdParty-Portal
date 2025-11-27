@extends('layouts.app')
@section('title', 'Trip Log Details')

@section('content')
<div class="d-flex gap-4">
    {{-- Parent Trip --}}
    <div class="card shadow rounded-4 p-4" style="min-width: 300px; max-width: 350px;">
        <h5 class="mb-3">📋 Parent Trip Details</h5>

        {{-- Status Badge --}}
        <div class="mb-3">
            @php
                $statusColor = match($parentTrip->statusDetail->Description ?? '') {
                    'Scheduled' => 'bg-warning',
                    'Approved' => 'bg-success',
                    'Rejected' => 'bg-danger',
                    'Ongoing' => 'bg-info',
                    'Completed' => 'bg-primary',
                    'Cancelled' => 'bg-secondary',
                    default => 'bg-secondary'
                };
            @endphp
            <span class="badge {{ $statusColor }} text-white p-2">
                Status: {{ $parentTrip->statusDetail->Description ?? 'N/A' }}
            </span>
        </div>

        <ul class="list-group list-group-flush">
            <li class="list-group-item"><strong>Trip No:</strong> {{ $parentTrip->TripNo }}</li>
            <li class="list-group-item"><strong>Trip Type:</strong> {{ $parentTrip->parentTripType->Description ?? 'N/A' }}</li>
            <li class="list-group-item"><strong>Trip Code:</strong> {{ $parentTrip->TripCode ?? 'N/A' }}</li>
            <li class="list-group-item"><strong>Vehicle Type:</strong> {{ $parentTrip->parentVehicleType->Description ?? 'N/A' }}</li>
            <li class="list-group-item"><strong>Load Type:</strong> {{ $parentTrip->parentLoadType->Description ?? 'N/A' }}</li>
            <li class="list-group-item"><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($parentTrip->TripStartDate)->format('d M Y') }}</li>
            <li class="list-group-item"><strong>End Date:</strong> {{ \Carbon\Carbon::parse($parentTrip->TripEndDate)->format('d M Y') }}</li>
            <li class="list-group-item"><strong>Start Time:</strong> {{ \Carbon\Carbon::parse($parentTrip->StartTime)->format('H:i A') }}</li>
            <li class="list-group-item"><strong>End Time:</strong> {{ \Carbon\Carbon::parse($parentTrip->EndTime)->format('H:i A') }}</li>
            <li class="list-group-item"><strong>Start Location:</strong> {{ $parentTrip->StartLocation }}</li>
            <li class="list-group-item"><strong>End Location:</strong> {{ $parentTrip->EndLocation }}</li>
            <li class="list-group-item"><strong>Distance Covered:</strong> {{ $parentTrip->DistanceCovered }} km</li>
            <li class="list-group-item"><strong>Purpose:</strong> {{ $parentTrip->Purpose ?? 'N/A' }}</li>
            <li class="list-group-item"><strong>Notes:</strong> {{ $parentTrip->Notes ?? 'N/A' }}</li>
        </ul>

        {{-- Parent Trip Actions --}}
        <div class="mt-3 d-flex justify-content-between">
            <a href="{{ route('fleet.trip_logs.edit', $parentTrip->Id) }}" class="btn btn-sm btn-warning">✏ Edit</a>

            <form action="{{ route('fleet.trip_logs.destroy', $parentTrip->Id) }}" method="POST"
                  onsubmit="return confirm('Deleting this parent will also delete ALL its child trips. Continue?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">🗑 Delete</button>
            </form>
        </div>
    </div>

        {{-- Child Trips --}}
        <div class="flex-grow-1 card shadow rounded-4 p-4">
            <h5 class="mb-3">🚌 Child Trips</h5>
            @if ($childTrips->isEmpty())
                <div class="alert alert-info" role="alert">
                    This trip has no child trips logged.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>Trip No</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Start Location</th>
                            <th>End Location</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($childTrips as $child)
                            <tr>
                                <td>{{ $child->TripNo }}</td>
                                <td>{{ \Carbon\Carbon::parse($child->TripStartDate)->format('d M Y') }}</td>                               <td>{{ \Carbon\Carbon::parse($child->TripEndDate)->format('d M Y') }}</td>                               <td>{{ $child->StartLocation }}</td>
                                <td>{{ $child->EndLocation }}</td>
                                <td>{{ $child->Purpose ?? 'N/A' }}</td>
                                <td>
                                    @php
                                        $childStatusColor = match($child->statusDetail->Description ?? '') {
                                            'Scheduled' => 'warning',
                                            'Approved' => 'success',
                                            'Rejected' => 'danger',
                                            'Ongoing' => 'info',
                                            'Completed' => 'primary',
                                            'Cancelled' => 'secondary',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $childStatusColor }}">
                                        {{ $child->statusDetail->Description ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>{{ $child->Notes ?? 'N/A' }}</td>
                                <td class="d-flex gap-2">
                                    <a href="{{ route('fleet.trip_logs.edit', $child->Id) }}"
                                       class="btn btn-sm btn-warning">✏ Edit</a>
                                    <form action="{{ route('fleet.trip_logs.destroy', $child->Id) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this child trip?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">🗑 Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
            </div>
            @endif
        </div>
    </div>

{{-- Approve/Reject Buttons Section --}}
@if(($parentTrip->statusDetail->Description ?? '') === 'Scheduled')
<div class="row mt-4">
    <div class="col-md-8">
        <a href="{{ route('fleet.trip_logs.index') }}" class="btn btn-secondary">⬅ Back to Trips</a>
    </div>
    <div class="col-md-4 text-end">
        <div class="d-flex gap-2 justify-content-end">
            <form action="{{ route('fleet.trip_logs.approve', $parentTrip->Id) }}" method="POST"
                  onsubmit="return confirm('Approve this trip and all child trips?');">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-lg btn-success">
                    <i class="fas fa-check-circle"></i> Approve Trip
                </button>
            </form>
            <form action="{{ route('fleet.trip_logs.reject', $parentTrip->Id) }}" method="POST"
                  onsubmit="return confirm('Reject this trip and all child trips?');">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-lg btn-danger">
                    <i class="fas fa-times-circle"></i> Reject Trip
                </button>
            </form>
        </div>
    </div>
</div>
@else
<div class="d-flex justify-content-between mt-4">
    <a href="{{ route('fleet.trip_logs.index') }}" class="btn btn-secondary">⬅ Back to Trips</a>
    <div class="text-muted">
        <small>Approval actions available only for "Scheduled" trips. Current status: <strong>{{ $parentTrip->statusDetail->Description ?? 'Unknown' }}</strong></small>
    </div>
</div>
@endif
@endsection
