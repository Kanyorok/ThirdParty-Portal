@extends('layouts.app')
@section('title', 'Movement History Viewer')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">📍 Movement History Viewer</h4>

    <form method="GET" action="{{ route('fleet.gps.movement_history') }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="vehicle_id" class="form-label">Select Vehicle</label>
            <select name="vehicle_id" id="vehicle_id" class="form-select" required>
                <option value="">-- Choose Vehicle --</option>
                @foreach($vehicles as $v)
                    <option value="{{ $v->VehicleID }}" {{ $vehicleId == $v->VehicleID ? 'selected' : '' }}>
                        {{ $v->RegistrationNumber }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}" required>
        </div>
        <div class="col-md-3">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}" required>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100" type="submit">🔍 View</button>
        </div>
    </form>

    @if($movementLogs && count($movementLogs))
        <h5 class="mt-3 mb-2">Tracking for: {{ $selectedVehicle->RegistrationNumber }}</h5>
        <div id="map" style="height: 500px;" class="mb-4 border rounded-4"></div>

        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movementLogs as $index => $log)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $log['Latitude'] }}</td>
                        <td>{{ $log['Longitude'] }}</td>
                        <td>{{ $log['Timestamp'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- Leaflet.js --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

@if(!empty($movementLogs))
<script>
    const logs = @json($movementLogs);
    const map = L.map('map').setView([logs[0].Latitude, logs[0].Longitude], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const latlngs = logs.map(l => [l.Latitude, l.Longitude]);

    // Draw polyline
    const path = L.polyline(latlngs, { color: 'blue' }).addTo(map);
    map.fitBounds(path.getBounds());

    // Add markers
    logs.forEach((l, i) => {
        L.marker([l.Latitude, l.Longitude])
            .addTo(map)
            .bindPopup(`<strong>Log #${i + 1}</strong><br>${l.Timestamp}`);
    });
</script>
@endif
@endsection
