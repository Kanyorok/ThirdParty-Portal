@extends('layouts.app')
@section('title', 'Maintenance Schedule')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection
@section('content')
    <div class="card p-4 shadow rounded-4">
        <div class="d-flex justify-content-between mb-3">
            <h4 class="mb-0">Schedules List</h4>
            <a href="{{ route('fleet.maintenance_schedule.create') }}" class="btn btn-primary">➕ Schedule
                Maintenance</a>
        </div>

        <div class="table-responsive">
            <div class="table-responsive">
                <table id="scheduleTable" class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Schedule ID</th>
                        <th>Vehicle</th>
                        <th>Maintenance Type</th>
                        <th>Scheduled Date</th>
                        <th>Scheduled Mileage</th>
                        <th>Maintenance Status</th>
                        <th>Active Status</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($schedules as $schedule)
                        @php
                            $statusDesc = optional($schedule->maintenanceStatus)->Description ?? 'Scheduled';
                            $maintenanceType = $schedule->maintenanceType;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $schedule->ScheduleID ?? '-' }}</td>
                            <td>{{ $schedule->vehicle->RegistrationNo ?? '-' }}</td>

                            <!-- Maintenance Type as badge -->
                            <td>
                                @if($maintenanceType)
                                    <span class="badge
                                    @if($maintenanceType->CodeID === 'Preventive') bg-info text-dark
                                    @elseif($maintenanceType->CodeID === 'Repair') bg-danger
                                    @elseif($maintenanceType->CodeID === 'Inspection') bg-primary
                                    @else bg-secondary
                                    @endif">
                                    {{ $maintenanceType->Description }}
                                </span>
                                @else
                                    -
                                @endif
                            </td>

                            <td>{{ $schedule->ScheduledDate ? \Carbon\Carbon::parse($schedule->ScheduledDate)->format('d M Y') : '-' }}</td>
                            <td>{{ $schedule->ScheduledMileage ?? '-' }}</td>

                            <!-- Maintenance Status -->
                            <td>
                            <span class="badge
                                @if($statusDesc === 'Scheduled') bg-primary
                                @elseif($statusDesc === 'Acknowledged') bg-warning
                                @elseif($statusDesc === 'Completed') bg-success
                                @else bg-secondary
                                @endif">
                                {{ $statusDesc }}
                            </span>
                            </td>

                            <!-- Active Status -->
                            <td>
                            <span class="badge {{ $schedule->Status ? 'bg-success' : 'bg-secondary' }}">
                                {{ $schedule->Status ? 'Active' : 'Inactive' }}
                            </span>
                            </td>

                            <td>{{ $schedule->Notes ?? '-' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('fleet.maintenance_schedule.show', $schedule->Id) }}"
                                       class="btn btn-sm btn-secondary" title="Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('fleet.maintenance_schedule.edit', $schedule->Id) }}"
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @if ($schedule->Status)
                                        <form action="{{ route('fleet.maintenance_schedule.cancel', $schedule->Id) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete this schedule?')"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled title="Inactive">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">No scheduled maintenance found.</td>
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
            @if(!$schedules->isEmpty())
            $('#scheduleTable').DataTable({
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