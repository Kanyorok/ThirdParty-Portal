<div>
    <!-- Life is available only in the present moment. - Thich Nhat Hanh -->
</div>
@extends('layouts.app')

@section('title', 'Attendance Devices & Channels')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Devices & Channels</h2>
        <a class="btn btn-primary" href="{{ route('hr.attendance.devices.create') }}">+ Add Device</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Channel</th>
                            <th>Allowed IPs</th>
                            <th>Allowed Locations</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devices as $device)
                            <tr>
                                <td>{{ $device->DeviceCode }}</td>
                                <td>{{ $device->Name }}</td>
                                <td>{{ $device->Channel }}</td>
                                <td>{{ $device->AllowedIPs ?? '-' }}</td>
                                <td>{{ $device->AllowedLocations ?? '-' }}</td>
                                <td>{{ $device->IsActive ? 'Active' : 'Inactive' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.attendance.devices.edit', $device->Id) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">No devices configured.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $devices->links() }}
        </div>
    </div>
</div>
@endsection
