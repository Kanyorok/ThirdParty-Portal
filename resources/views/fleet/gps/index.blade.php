@extends('layouts.app')
@section('title', 'Live Tracking')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">Fleet</a></li>
@endsection
@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <style>
        #map {
            width: 100%;
            height: 75vh;
            position: relative;
        }

        .floating-card {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            padding: 20px;
            max-width: 300px;
            max-height: calc(75vh - 40px);
            overflow-y: auto;
        }

        .floating-card h5 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div id="map">
                <div class="floating-card">
                    <h5>Tracking</h5>
                    <div class="card-content">
                        <p class="mb-2"><strong>Total Vehicles:</strong> <span id="vehicle-count">0</span></p>
                        <p class="mb-2"><strong>Active:</strong> <span id="active-count">0</span></p>
                        <hr>
                        <small class="text-muted">Last updated: <span id="last-update">--</span></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://rawcdn.githack.com/bbecquet/Leaflet.RotatedMarker/master/leaflet.rotatedMarker.js"></script>
    <script>
        $(function () {
            window.map = L.map('map').setView([-0.023559, 37.906193], 7);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(window.map);

            window.vehicleMarkers = {};

            fetchVehicleLocations();


            setInterval(fetchVehicleLocations, 15000);//15 seconds
        });

        function fetchVehicleLocations() {
            $.ajax({
                url: window.getDocumentUrl(),
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    updateVehicleMarkers(response.data);
                    updateStats(response.data);
                    $('#last-update').text(new Date(response.timestamp).toLocaleString());
                },
                error: function (xhr, status, error) {
                    nError('loading, vehicles and location failed');
                }
            });
        }

        function updateVehicleMarkers(vehicles) {
            vehicles.forEach(function (vehicle) {
                const position = [vehicle.Latitude, vehicle.Longitude];
                const popupContent = `
                            <strong>${vehicle.RegistrationNumber}</strong><br>
                            Status: ${vehicle.Status}<br>
                            Speed: ${vehicle.Speed} km/h<br>
                            Updated: ${new Date(vehicle.LastUpdated).toLocaleTimeString()}
                        `;
                if (window.vehicleMarkers[vehicle.VehicleID]) {
                    const adjustedDirection = (vehicle.Direction - 180 + 360) % 360;
                    window.vehicleMarkers[vehicle.VehicleID]
                        .setLatLng(position)
                        .setRotationAngle(adjustedDirection)
                        .getPopup()
                        .setContent(popupContent);
                } else {
                    const carIcon = L.icon({
                        iconUrl: '{{ asset('assets/img/fleet/car.png') }}',
                        iconSize: [50, 50],
                        iconAnchor: [25, 25],
                        popupAnchor: [0, -20]
                    });
                    const adjustedDirection = (vehicle.Direction - 180 + 360) % 360;
                    window.vehicleMarkers[vehicle.VehicleID] = L.marker(position, {
                        icon: carIcon, rotationAngle: adjustedDirection, rotationOrigin: 'center center'
                    }).addTo(window.map).bindPopup(popupContent);
                }
            });
        }

        function updateStats(vehicles) {
            const totalVehicles = vehicles.length;
            const activeVehicles = vehicles.filter(v => v.Status === 'Moving').length;

            $('#vehicle-count').text(totalVehicles);
            $('#active-count').text(activeVehicles);
        }


    </script>
@endsection
