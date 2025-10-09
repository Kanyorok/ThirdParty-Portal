@extends('layouts.app')
@section('title', 'Contracted Driver Vehicle Assignments')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">🚚 Contracted Driver Assignment History</h4>

        <div class="table-responsive">
            <table id="assignmentTable" class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Vehicle</th>
                    <th>Driver</th>
                    <th>Assigned On</th>
                    <th>Unassigned On</th>
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
@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            @if(!$assignments->isEmpty())
            $('#assignmentTable').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true,
                language: {
                    emptyTable: ""
                }
            });
            @endif
        });
    </script>
@endsection


