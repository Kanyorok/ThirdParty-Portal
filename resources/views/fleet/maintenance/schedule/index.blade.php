@extends('layouts.app')
@section('title', 'Maintenance Schedule')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="mb-0">🔧 Maintenance Schedule</h4>
        <a href="{{ route('fleet.maintenance_schedule.create') }}" class="btn btn-primary">➕ Schedule Maintenance</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Vehicle</th>
                    <th>Type</th>
                    <th>Scheduled Date</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($schedules as $schedule)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $schedule->vehicle->RegistrationNumber ?? '-' }}</td>
                        <td>{{ $schedule->MaintenanceType }}</td>
                        <td>{{ $schedule->ScheduledDate }}</td>
                        <td>{{ $schedule->Status }}</td>
                        <td>{{ $schedule->Notes }}</td>
                        <td>
                            <a href="{{ route('fleet.maintenance_schedule.edit', $schedule->ID) }}" class="btn btn-sm btn-warning">Edit</a>
                            @if ($schedule->Status !== 'Cancelled')
                                <form action="{{ route('fleet.maintenance_schedule.cancel', $schedule->ID) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Cancel this schedule?')">Cancel</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No scheduled maintenance found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection