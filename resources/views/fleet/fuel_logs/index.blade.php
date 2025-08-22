@extends('layouts.app')
@section('title', 'Fuel Logs')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <div class="d-flex justify-content-between mb-3">
            <h4 class="mb-0">⛽ Fuel Log Entries</h4>
            <a href="{{ route('fleet.fuel_logs.create') }}" class="btn btn-primary">➕ New Fuel Log</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Vehicle</th>
                    <th>Trip ID</th>
                    <th>Fuel</th>
                    <th>Odometer Start</th>
                    <th>Odometer End</th>
                    <th>Efficiency (Km/L)</th>
                    <th>Vendor</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($fuelLogs as $log)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $log->LogDate }}</td>
                        <td>{{ $log->vehicle->RegistrationNumber ?? '-' }}</td>
                        <td>{{ $log->TripID ?? '-' }}</td>
                        <td>{{ $log->FuelAmount }} {{ $log->FuelUnit }} ({{ $log->FuelType }})</td>
                        <td>{{ $log->OdometerStart ?? '-' }}</td>
                        <td>{{ $log->OdometerEnd ?? '-' }}</td>
                        <td>{{ $log->Efficiency ?? '-' }}</td>
                        <td>{{ $log->Vendor ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No fuel logs found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
