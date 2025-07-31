@extends('layouts.app')
@section('title', 'Service Alerts & Reminders')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">🔔 Service Alerts & Reminders</h4>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Vehicle</th>
                    <th>Alert Type</th>
                    <th>Description</th>
                    <th>Mileage</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Acknowledge</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($alerts as $alert)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $alert->vehicle->RegistrationNumber ?? '-' }}</td>
                        <td>{{ $alert->AlertType }}</td>
                        <td>{{ $alert->Description }}</td>
                        <td>{{ $alert->TriggerMileage ?? '-' }}</td>
                        <td>{{ $alert->TriggerDate ?? '-' }}</td>
                        <td>
                            @if ($alert->IsAcknowledged)
                                <span class="badge bg-success">Acknowledged</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </td>
                        <td>
                            @if (!$alert->IsAcknowledged)
                                <form action="{{ route('fleet.alerts.acknowledge', $alert->ID) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button class="btn btn-sm btn-primary" onclick="return confirm('Acknowledge this alert?')">Acknowledge</button>
                                </form>
                            @else
                                {{ \Carbon\Carbon::parse($alert->AcknowledgedOn)->format('Y-m-d') }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No service alerts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
