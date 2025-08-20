@extends('layouts.app')
@section('title', 'Contracted Driver Vehicle Assignments')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">🚚 Contracted Driver Assignment History</h4>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Vehicle</th>
                    <th>Driver</th>
                    <th>Assignment Date</th>
                    <th>Unassignment Date</th>
                    <th>Purpose</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($assignments as $assignment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $assignment->vehicle->RegistrationNumber ?? '-' }}</td>
                        <td>{{ $assignment->driver->FullName ?? '-' }}</td>
                        <td>{{ $assignment->AssignmentDate }}</td>
                        <td>{{ $assignment->UnassignmentDate ?? '-' }}</td>
                        <td>{{ $assignment->Purpose }}</td>
                        <td>{{ $assignment->Notes }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No assignment records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
