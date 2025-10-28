@extends('layouts.app')
@section('title', 'Trip Logs')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
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
                    <th>Child Trips</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($tripLogs as $trip)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $trip->TripNo }}</td>
                        <td>{{ $trip->parentTripType->Description ?? '—' }}</td>
                        <td>{{ $trip->parentVehicleType->Description ?? '—' }}</td>
                        <td>{{ $trip->parentLoadType->Description ?? '—' }}</td>
                        <td>{{ \Carbon\Carbon::parse($trip->TripStartDate)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($trip->TripEndDate)->format('d/m/Y') }}</td>
                        <td>
                            @php $childCount = $trip->childTrips->count(); @endphp
                            <span class="badge bg-{{ $childCount > 0 ? 'primary' : 'secondary' }}">
                            {{ $childCount }}
                        </span>
                        </td>
                        <td>
                            <a href="{{ route('fleet.trip_logs.show', $trip->Id) }}" class="btn btn-sm btn-info">👁️
                                View</a>
                        </td>
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
                    emptyTable: "No trip logs available"
                }
            });
            @endif
        });
    </script>
@endsection
