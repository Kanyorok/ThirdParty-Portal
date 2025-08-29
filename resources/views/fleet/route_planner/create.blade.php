@extends('layouts.app')

@section('title', 'Route Planner')

@section('head')

    <style>
        #map {
            height: 180px;
        }

    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-4 col-lg-3 col-12">
            <div class="card">
                <div class="card-header"><h5>Plan Details</h5></div>
                <form method="POST" action="{{ route('fleet.route_planner.store') }}" class="card-body">
                    @csrf
                    <div class="mb-3">
                        <label for="TripName" class="form-label">Trip Name</label>
                        <input type="text" name="TripName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="VehicleID" class="form-label">Vehicle</label>
                        <select name="VehicleID" class="form-control" required>
                            <option selected disabled>-- Select Vehicle --</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->Id }}">
                                    {{ $vehicle->RegistrationNo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Waypoints (in order)</label>
                        <div id="waypoints-container">
                            <div class="input-group mb-2 waypoint-group">
                                <input type="text" name="Waypoints[]" class="form-control"
                                       placeholder="Enter location or coordinates" required>
                                <button type="button" class="btn btn-danger remove-waypoint"><i
                                        class="fas fa-trash"></i></button>
                            </div>
                        </div>
                        <button type="button" id="add-waypoint" class="btn btn-outline-primary btn-sm">➕ Add Waypoint
                        </button>
                    </div>
                    <div class="mt-4">
                        <button class="btn btn-success" type="submit">Save Route</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-sm-8 col-lg-9 col-12">
            <div class="card">
                <div class="card-body">
                    <div id="map" style="height: 50vh;"></div>
                </div>
            </div>
        </div>

    </div>
@endsection
@section('scripts')
    {{--- <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>--}}
    <script src='https://unpkg.com/maplibre-gl@latest/dist/maplibre-gl.js'></script>
    <link href='https://unpkg.com/maplibre-gl@latest/dist/maplibre-gl.css' rel='stylesheet'/>
    <script>
        var map = new maplibregl.Map({
            container: 'map',
            style: 'https://demotiles.maplibre.org/style.json', // stylesheet location
            center: [36.8219, -1.2921], // starting position [lng, lat]
            zoom: 1 // starting zoom
        });

        /*   const mainMap = L.map('map').setView([-1.2921, 36.8219], 10);

           const tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
               maxZoom: 19,
               attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
           }).addTo(mainMap);


           /*  // Inialize map after DOM is ready
             let map = L.map('map', {
                 zoomControl: true,
                 attributionControl: true,
                 scrollWheelZoom: true
             }).setView([-1.2921, 36.8219], 10);

             // Add OpenStreetMap tiles
             L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                 maxZoom: 19,
                 attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
             }).addTo(map);

             // Force map to resize after initialization
             setTimeout(function() {
                 map.invalidateSize();
             }, 100);*/


        /* let map = L.map('mainBodyContent', {
             zoomControl: true,
             attributionControl: true,
             scrollWheelZoom: true
         }).setView([-1.2921, 36.8219], 10);

         // Add OpenStreetMap tiles
         L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
             maxZoom: 19,
             attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
         }).addTo(map);*/

        /*  function drawRoute() {
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
                  window.routePolyline = L.polyline(coords, {color: 'green', weight: 5}).addTo(map);
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
          });*/
    </script>
@endsection
