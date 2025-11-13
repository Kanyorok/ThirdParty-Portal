@extends('layouts.app')
@section('title', 'Trip Logs')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        .badge-status {
            font-size: 0.75em;
            padding: 0.35em 0.65em;
        }
    </style>
@endsection

@section('content')
    <div class="card p-4 shadow rounded-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>📝 Trips List</h4>
            <a href="{{ route('fleet.trip_logs.create') }}" class="btn btn-primary">➕ Log New Trip</a>
        </div>

    <div class="table-responsive">
        <table id="tripTable" class="table table-bordered table-striped align-middle">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Trip No</th>
                <th>Trip Type</th>
                <th>Vehicle Type</th>
                <th>Load Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Child Trips</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($tripLogs as $trip)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $trip->TripNo }}</strong>
                        @if($trip->ParentTripID)
                            <br><small class="text-muted">Child Trip</small>
                        @endif
                    </td>
                    <td>{{ $trip->parentTripType->Description ?? '—' }}</td>
                    <td>{{ $trip->parentVehicleType->Description ?? '—' }}</td>
                    <td>{{ $trip->parentLoadType->Description ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($trip->TripStartDate)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($trip->TripEndDate)->format('d/m/Y') }}</td>
                    <td>
                        @php
                            $statusDesc = $trip->statusDetail->Description ?? 'Unknown';
                            $statusClass = match(strtolower($statusDesc)) {
                                'scheduled' => 'bg-primary',
                                'in progress' => 'bg-warning',
                                'completed' => 'bg-success',
                                'rejected' => 'bg-danger',
                                'approved' => 'bg-success',
                                default => 'bg-info'
                            };
                        @endphp
                        <span class="badge badge-status {{ $statusClass }}">
                            {{ $statusDesc }}
                        </span>
                    </td>
                    <td>
                        @php $childCount = $trip->childTrips->count(); @endphp
                        @if($childCount > 0)
                            <span class="badge bg-primary" title="{{ $childCount }} child trip(s)">
                                 {{ $childCount }}
                            </span>
                        @else
                            <span class="badge bg-secondary">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('fleet.trip_logs.show', $trip->Id) }}" 
                        class="btn btn-sm btn-success me-1" 
                        data-bs-toggle="tooltip" 
                        data-bs-placement="top" 
                        title="View Trip Details">
                            👁️
                        </a>

                        @if(!$trip->ParentTripID)
                            <a href="{{ route('fleet.trip_logs.create', ['parentTripId' => $trip->Id]) }}" 
                            class="btn btn-sm btn-primary" 
                            data-bs-toggle="tooltip" 
                            data-bs-placement="top" 
                            title="Add Child Trips">
                                ➕
                            </a>
                        @endif
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center text-muted">No trip logs found.</td>
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
        @if(!$tripLogs->isEmpty())
        $('#tripTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                emptyTable: "No trip logs available",
                search: "Search trips:",
                lengthMenu: "Show _MENU_ trips per page",
                info: "Showing _START_ to _END_ of _TOTAL_ trips",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            columnDefs: [
                { orderable: false, targets: [0, 8, 9] } // Disable sorting on #, Child Trips, and Actions columns
            ],
            order: [[1, 'desc']] // Default sort by Trip No descending
        });
        @endif
    });
</script>
@endsection