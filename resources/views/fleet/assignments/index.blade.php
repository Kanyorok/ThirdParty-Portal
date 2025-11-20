@extends('layouts.app')
@section('title', 'Vehicle Assignment History')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"> Fleet Assignments List</h4>
            <a href="{{ route('fleet.assignments.create') }}" class="btn btn-primary">
                + New Assignment
            </a>
        </div>

        <h4 class="mb-4">📜 Vehicle Assignment History</h4>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>TripNo</th>
                    <th>VehicleType</th>
                    <th>VehicleNo</th>
                    <th>Assigned To (Driver)</th>
                    <th>Last Inspection Date</th>
                    <th>Assignment Date</th>
                    <th>Purpose</th>
                    <th>Notes</th>
                    <th>Assigned By</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($assignments as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->trip->TripNo ?? 'N/A' }}</td>
                        <td>{{ $item->fleetVehicleType->Description ?? 'N/A' }}</td>
                        <td>{{ $item->vehicle->RegistrationNo ?? 'N/A' }}</td>
                        <td>{{ $item->driver->FullName ?? 'N/A' }}</td>
                        <td>{{ $item->LastInspectionDate ? \Carbon\Carbon::parse($item->LastInspectionDate)->format('d/m/Y') : 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->AssignmentDate)->format('d/m/Y') }}</td>
                        <td>{{ $item->Purpose }}</td>
                        <td>{{ $item->Notes }}</td>
                        <td>
                            {{ $item->assigner ? $item->assigner->LastName . ' ' . $item->assigner->FirstName : 'N/A' }}
                        </td>
                        <td>
                            <a href="{{ route('fleet.assignments.show', $item->Id) }}" class="btn btn-sm btn-info mb-1">Details</a>
                            <a href="{{ route('fleet.assignments.edit', $item->Id) }}"
                               class="btn btn-sm btn-warning mb-1">Edit</a>
                            <form action="{{ route('fleet.assignments.destroy', $item->Id) }}" method="POST"
                                  class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger mb-1"
                                        onclick="return confirm('Are you sure you want to delete this assignment?')">
                                    Delete
                                </button>
                            </form>
                        </td>

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
