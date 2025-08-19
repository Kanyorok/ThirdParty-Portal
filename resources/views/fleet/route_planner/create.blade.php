@extends('layouts.app')
@section('title', 'Plan Route')

@section('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<style>
#map-wrapper {
width: 100%;
height: 400px;
position: relative;
overflow: hidden;
border-radius: 0.5rem;
}

#map {
width: 100%;
height: 100%;
}


</style>
@endsection

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">🗺️ Plan and Optimize Route</h4>

    <form method="POST" action="{{ route('fleet.route_planner.store') }}">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label for="TripName" class="form-label">Trip Name</label>
                <input type="text" name="TripName" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label for="VehicleID" class="form-label">Vehicle</label>
                <select name="VehicleID" class="form-select" required>
                    <option value="">-- Select Vehicle --</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->Id }}">
                            {{ $vehicle->RegistrationNo }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-12">
                <label class="form-label">Waypoints (in order)</label>
                <div id="waypoints-container">
                    <div class="input-group mb-2 waypoint-group">
                        <input type="text" name="Waypoints[]" class="form-control" placeholder="Enter location or coordinates" required>
                        <button type="button" class="btn btn-danger remove-waypoint">🗑️</button>
                    </div>
                </div>
                <button type="button" id="add-waypoint" class="btn btn-outline-primary btn-sm">➕ Add Waypoint</button>
            </div>

            <div class="col-md-12">
            <label class="form-label">Map Preview</label>
            <div id="map-wrapper" class="shadow-sm border rounded-4">
            <div id="map"></div>
            </div>
            </div>


        <div class="mt-4">
            <button class="btn btn-success" type="submit">🚀 Save Route</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Initialize map inside card
        let map = L.map('map', { zoomControl: true }).setView([-1.2921, 36.8219], 10); // Default: Nairobi

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
        }).addTo(map);

        function drawRoute() {
            const waypoints = document.querySelectorAll('input[name="Waypoints[]"]');
            let coords = [];

            waypoints.forEach(input => {
                const value = input.value.trim();
                if (value.includes(',')) {
                    const [lat, lng] = value.split(',').map(parseFloat);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        coords.push([lat, lng]);
                    }
                }
            });

            if (coords.length >= 2) {
                if (window.routePolyline) {
                    map.removeLayer(window.routePolyline);
                }

                // Draw bold green polyline
                window.routePolyline = L.polyline(coords, { color: 'green', weight: 5 }).addTo(map);
                map.fitBounds(window.routePolyline.getBounds());
            }
        }

        // Event listeners
        document.getElementById('waypoints-container').addEventListener('input', drawRoute);

        document.getElementById('add-waypoint').addEventListener('click', () => {
            const container = document.getElementById('waypoints-container');
            const div = document.createElement('div');
            div.classList.add('input-group', 'mb-2', 'waypoint-group');
            div.innerHTML = `
                <input type="text" name="Waypoints[]" class="form-control" placeholder="Enter location or coordinates" required>
                <button type="button" class="btn btn-danger remove-waypoint">🗑️</button>
            `;
            container.appendChild(div);
        });

        document.getElementById('waypoints-container').addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-waypoint')) {
                e.target.closest('.waypoint-group').remove();
                drawRoute();
            }
        });
    </script>
@endsection
