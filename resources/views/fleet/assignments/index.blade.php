@extends('layouts.app')
@section('title', 'Vehicle Assignment History')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">📜 Vehicle Assignment History</h4>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>TripNo</th>
                    <th>VehicleType</th>
                    <th>VehicleNo</th>
                    <th>AssignedTo</th>
                    <th>Branch</th>
                    <th>Date</th>
                    <th>Purpose</th>
                    <th>Notes</th>
                    <th>Assigned By</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($assignments as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->vehicle->RegistrationNo }}</td>
                        <td>{{ $item->user->name ?? '-' }}</td>
                        <td>{{ $item->branch->Name ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->AssignmentDate)->format('d-M-Y') }}</td>
                        <td>{{ $item->Purpose }}</td>
                        <td>{{ $item->Notes }}</td>
                        <td>{{ optional($item->assignedBy)->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No assignment history found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
