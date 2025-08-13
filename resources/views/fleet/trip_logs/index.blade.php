@extends('layouts.app')
@section('title', 'Trip Logs')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">📘 Trip Log Entries</h4>
        <a href="{{ route('fleet.trip_logs.create') }}" class="btn btn-primary">➕ New Trip Log</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Vehicle</th>
                    <th>Driver Type</th>
                    <th>Driver</th>
                    <th>Trip Date</th>
                    <th>Distance</th>
                    <th>Purpose</th>
                    <th>From</th>
                    <th>To</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tripLogs as $log)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $log->vehicle->RegistrationNumber ?? '-' }}</td>
                        <td>{{ $log->DriverType }}</td>
                        <td>{{ $log->DriverName ?? '-' }}</td>
                        <td>{{ $log->TripDate }}</td>
                        <td>{{ $log->DistanceCovered }} km</td>
                        <td>{{ $log->Purpose }}</td>
                        <td>{{ $log->StartLocation }}</td>
                        <td>{{ $log->EndLocation }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No trip logs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
