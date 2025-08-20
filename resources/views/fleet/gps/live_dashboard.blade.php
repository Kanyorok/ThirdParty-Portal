@extends('layouts.app')
@section('title', 'Live GPS Tracking')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-3">📡 Live Location Dashboard</h4>
    <div id="map" style="height: 500px;" class="mb-4 rounded-4 border"></div>

    <h5>Vehicle Coordinates</h5>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Vehicle</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Last Updated</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vehicleLocations as $v)
                <tr>
                    <td>{{ $v['RegistrationNumber'] }}</td>
                    <td>{{ $v['Latitude'] }}</td>
                    <td>{{ $v['Longitude'] }}</td>
                    <td>{{ $v['LastUpdated'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Leaflet.js --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
    const vehicleLocations = @json($vehicleLocations);
    const map = L.map('map').setView([-1.28, 36.82], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    vehicleLocations.forEach(v => {
        const marker = L.marker([v.Latitude, v.Longitude])
            .addTo(map)
            .bindPopup(`<strong>${v.RegistrationNumber}</strong><br>Updated: ${v.LastUpdated}`);
    });
</script>
@endsection
