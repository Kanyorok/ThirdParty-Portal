@extends('layouts.app')
@section('title', 'Trip Log Details')

@section('content')
<div class="d-flex gap-4">
    {{-- Parent Trip --}}
    <div class="card shadow rounded-4 p-4" style="min-width: 300px; max-width: 350px;">
        <h5 class="mb-3">📋 Parent Trip Details</h5>
        <ul class="list-group list-group-flush">
            <li class="list-group-item"><strong>Trip No:</strong> {{ $parentTrip->TripNo }}</li>
            <li class="list-group-item"><strong>Trip Type:</strong> {{ $parentTrip->parentTripType->Description ?? 'N/A' }}</li>
            <li class="list-group-item"><strong>Trip Code:</strong> {{ $parentTrip->TripCode ?? 'N/A' }}</li>
            <li class="list-group-item"><strong>Vehicle Type:</strong> {{ $parentTrip->parentVehicleType->Description ?? 'N/A' }}</li>
            <li class="list-group-item"><strong>Load Type:</strong> {{ $parentTrip->parentLoadType->Description ?? 'N/A' }}</li>
            <li class="list-group-item"><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($parentTrip->TripStartDate)->format('d/m/Y') }}</li>
            <li class="list-group-item"><strong>End Date:</strong> {{ \Carbon\Carbon::parse($parentTrip->TripEndDate)->format('d/m/Y') }}</li>
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
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($childTrips as $child)
                            <tr>
                                <td>{{ $child->TripNo }}</td>
                                <td>{{ \Carbon\Carbon::parse($child->TripStartDate)->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($child->TripEndDate)->format('d/m/Y') }}</td>
                                <td>{{ $child->StartLocation }}</td>
                                <td>{{ $child->EndLocation }}</td>
                                <td>{{ $child->Purpose ?? 'N/A' }}</td>
                                <td>{{ $child->Notes ?? 'N/A' }}</td>
                                <td class="d-flex gap-2">
                                    <a href="{{ route('fleet.trip_logs.edit', $child->Id) }}" class="btn btn-sm btn-warning">✏ Edit</a>
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

<div class="d-flex justify-content-between mt-4">
    <a href="{{ route('fleet.trip_logs.index') }}" class="btn btn-secondary">⬅ Back</a>
</div>
@endsection
