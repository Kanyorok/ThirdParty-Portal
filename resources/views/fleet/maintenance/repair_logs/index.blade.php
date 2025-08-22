@extends('layouts.app')
@section('title', 'Repair Logs')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="mb-0">🛠️ Repair & Maintenance Logs</h4>
        <a href="{{ route('fleet.repair_logs.create') }}" class="btn btn-primary">➕ Log Repair</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Vehicle</th>
                    <th>Type</th>
                    <th>Repair Date</th>
                    <th>Vendor</th>
                    <th>Cost</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($repairs as $repair)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $repair->vehicle->RegistrationNumber ?? '-' }}</td>
                        <td>{{ $repair->RepairType }}</td>
                        <td>{{ $repair->RepairDate }}</td>
                        <td>{{ $repair->Vendor ?? '-' }}</td>
                        <td>KES {{ number_format($repair->Cost, 2) }}</td>
                        <td>{{ $repair->Description }}</td>
                        <td>
    <a href="{{ route('fleet.repair_logs.show', $repair->ID) }}" class="btn btn-sm btn-info">View</a>
    <a href="{{ route('fleet.repair_logs.edit', $repair->ID) }}" class="btn btn-sm btn-warning">Edit</a>
</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No repair logs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
