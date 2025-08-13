@extends('layouts.app')
@section('title', 'GPS & Telematics Devices')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">📡 GPS & Telematics Integration</h4>

    <a href="{{ route('fleet.telematics.create') }}" class="btn btn-success mb-3">
        ➕ Add Telematics Device
    </a>

    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Vehicle</th>
                <th>Device ID</th>
                <th>Install Date</th>
                <th>Provider</th>
                <th>Status</th>
                <th>Renewal Date</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($devices as $index => $device)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $device->vehicle->RegistrationNumber }}</td>
                    <td>{{ $device->DeviceID }}</td>
                    <td>{{ $device->InstallDate }}</td>
                    <td>{{ $device->ProviderName }}</td>
                    <td>
                        @if($device->SubscriptionStatus == 'Active')
                            <span class="badge bg-success">Active</span>
                        @elseif($device->SubscriptionStatus == 'Expired')
                            <span class="badge bg-danger">Expired</span>
                        @else
                            <span class="badge bg-warning text-dark">{{ $device->SubscriptionStatus }}</span>
                        @endif
                    </td>
                    <td>{{ $device->RenewalDate }}</td>
                    <td>{{ $device->Notes }}</td>
                    <td>
                        <a href="{{ route('fleet.telematics.show', $device->ID) }}" class="btn btn-sm btn-secondary">View</a>
                        <a href="{{ route('fleet.telematics.edit', $device->ID) }}" class="btn btn-sm btn-primary">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
