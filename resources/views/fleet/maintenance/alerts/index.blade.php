@extends('layouts.app')
@section('title', 'Service Alerts & Reminders')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">🔔 Service Alerts & Reminders</h4>

        <div class="table-responsive">
            <div class="table-responsive">
                <table id="alertsTable" class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Vehicle</th>
                        <th>Maintenance Type</th>
                        <th>Description</th>
                        <th>Mileage</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Acknowledge</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($schedules as $schedule)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $schedule->vehicle->RegistrationNo ?? '-' }}</td>
                            <td>{{ $schedule->maintenanceType->Description ?? 'General Service' }}</td>
                            <td>{{ $schedule->Notes ?? '-' }}</td>
                            <td>{{ $schedule->ScheduledMileage ?? '-' }}</td>
                            <td>{{ $schedule->ScheduledDate ? \Carbon\Carbon::parse($schedule->ScheduledDate)->format('d M Y') : '-' }}</td>
                            <td>
                                @if ($schedule->alert && $schedule->alert->IsAcknowledged)
                                    <span class="badge bg-success">Acknowledged</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @endif
                            </td>
                            <td>
                                @if (!$schedule->alert || !$schedule->alert->IsAcknowledged)
                                    <form action="{{ route('fleet.alerts.acknowledge', $schedule->Id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <button class="btn btn-sm btn-primary"
                                                onclick="return confirm('Acknowledge this schedule?')">
                                            Acknowledge
                                        </button>
                                    </form>
                                @else
                                    {{ $schedule->alert->AcknowledgedOn ? \Carbon\Carbon::parse($schedule->alert->AcknowledgedOn)->format('Y-m-d') : '-' }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No upcoming maintenance schedules.</td>
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
                    $('#alertsTable').DataTable({
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

