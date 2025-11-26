@extends('layouts.app')
@section('title', 'Fleet Inspection Schedule')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
    <div class="card p-4 shadow rounded-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>🧾 Schedule List</h4>
            <a href="{{ route('fleet.inspection_schedule.create') }}" class="btn btn-primary">➕ Schedule Inspection</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table id="inspectionTable" class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Inspection No</th>
                    <th>Vehicle</th>
                    <th>Inspection Type</th>
                    <th>Inspection Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($schedules as $schedule)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $schedule->InspectionNo ?? '-' }}</td>
                        <td>{{ $schedule->vehicle->RegistrationNo ?? '-' }}</td>
                        <td>{{ $schedule->InspectionType }}</td>
                        <td>{{ \Carbon\Carbon::parse($schedule->InspectionDate)->format('d/m/Y') }}</td>
                        <td>{{ $schedule->DueDate ? \Carbon\Carbon::parse($schedule->DueDate)->format('d/m/Y') : '-' }}</td>

                        @php
                            $statusColor = match(strtolower($schedule->inspectionStatus->Description ?? '')) {
                                'completed' => 'success',
                                'pending' => 'warning',
                                'overdue' => 'danger',
                                default => 'secondary',
                            };
                        @endphp

                        <td>
                            <span class="badge bg-{{ $statusColor }}">
                                {{ $schedule->inspectionStatus->Description ?? 'N/A' }}
                            </span>
                        </td>

                        <td>{{ $schedule->Remarks ?? '-' }}</td>

                        <td>
                            <a href="{{ route('fleet.inspection_schedule.show', $schedule->Id) }}"
                               class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('fleet.inspection_schedule.edit', $schedule->Id) }}"
                               class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('fleet.inspection_schedule.destroy', $schedule->Id) }}" method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete this schedule?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No inspection records found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @section('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script>
            $(document).ready(function () {
                @if(!$schedules->isEmpty())
                $('#inspectionTable').DataTable({
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
@endsection
