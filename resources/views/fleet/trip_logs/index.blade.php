@extends('layouts.app')
@section('title', 'Trip Logs')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>📝 Trips List</h4>
        <a href="{{ route('fleet.trip_logs.create') }}" class="btn btn-primary">➕ Log New Trip</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Trip No</th>
                    <th>Vehicle</th>
                    <th>Driver</th>
                    <th>Driver Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Start Location</th>
                    <th>End Location</th>
                    <th>Purpose</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tripLogs as $index => $trip)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $trip->TripNo }}</td>
                        <td>{{ $trip->vehicle->RegistrationNo ?? 'N/A' }}</td>
                        <td>
                            @if($trip->driverType->Description === 'Permanent')
                                {{ $trip->driverPermanent->FullName ?? 'N/A' }}
                            @else
                                {{ $trip->driverContracted->FullName ?? 'N/A' }}
                            @endif
                        </td>
                        <td>{{ $trip->driverType->Description ?? 'N/A' }}</td>
                        <td>{{ $trip->TripStartDate ? \Carbon\Carbon::parse($trip->TripStartDate)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $trip->TripEndDate ? \Carbon\Carbon::parse($trip->TripEndDate)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $trip->StartLocation ?? '-' }}</td>
                        <td>{{ $trip->EndLocation ?? '-' }}</td>
                        <td>{{ $trip->Purpose ?? '-' }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('fleet.trip_logs.show', $trip->Id) }}" class="btn btn-sm btn-info">👁️Details</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="text-center text-muted">No trip logs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>


</div>
@endsection
