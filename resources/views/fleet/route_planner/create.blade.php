@extends('layouts.app')

@section('title', 'Route Planner')

@section('head')
    <link href='https://unpkg.com/maplibre-gl@latest/dist/maplibre-gl.css' rel='stylesheet'/>
    <style>
        #map {
            height: 180px;
        }
        .waypoint-group .input-group-text {
            min-width: 40px;
            justify-content: center;
        }
        #child-trips-table {
            margin-bottom: 0;
        }
        #child-trips-table thead th {
            background-color: #e9ecef;
            border-bottom: 2px solid #dee2e6;
            font-size: 0.85rem;
            padding: 8px;
        }
        #child-trips-table tbody td {
            padding: 8px;
            font-size: 0.85rem;
        }
        #no-child-trips {
            display: none;
        }
        .card-header.bg-info {
            background-color: #0dcaf0 !important;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-4 col-lg-3 col-12">
            <div class="card">
                <div class="card-header"><h5>Plan Details</h5></div>
                <form method="POST" action="{{ route('fleet.route_planner.store') }}" class="card-body" id="route-form">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="TripNo" class="form-label">Trip Number *</label>
                        <select name="TripNo" id="TripNo" class="form-control" required>
                            <option value="" selected disabled>-- Select Trip --</option>
                            @foreach($trips as $data)
                                @php
                                    $trip = $data['trip'];
                                    $vehicle = $data['vehicle'];
                                    $startLocation = $trip->StartLocation ?? 'Not set';
                                    $endLocation = $trip->EndLocation ?? 'Not set';
                                    $hasChildren = $data['has_children'];
                                    $childTrips = $data['child_trips'];
                                @endphp
                                
                                @if($trip && $vehicle)
                                    <option value="{{ $trip->TripNo }}" 
                                            data-vehicle-id="{{ $vehicle->Id }}"
                                            data-vehicle-registration="{{ $vehicle->RegistrationNo ?? 'N/A' }}"
                                            data-has-children="{{ $hasChildren ? 'true' : 'false' }}"
                                            data-child-trips="{{ $hasChildren ? htmlspecialchars(json_encode($childTrips), ENT_QUOTES, 'UTF-8') : '[]' }}"
                                            data-start-location="{{ $startLocation }}"
                                            data-end-location="{{ $endLocation }}">
                                        {{ $trip->TripNo }} ({{ $startLocation }} → {{ $endLocation }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <div class="form-text">Select a trip to auto-populate the vehicle</div>
                    </div>
                    
                    <!-- Simplified Child Trips Container -->
                    <div class="mb-3" id="child-trips-container" style="display: none;">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white py-2">
                                <h6 class="mb-0"><i class="fas fa-sitemap"></i> Child Trip Details</h6>
                            </div>
                            <div class="card-body p-2">
                                <table class="table table-sm mb-0" id="child-trips-table">
                                    <thead>
                                        <tr>
                                            <th>Trip No</th>
                                            <th>Start Location</th>
                                            <th>End Location</th>
                                        </tr>
                                    </thead>
                                    <tbody id="child-trips-body">
                                        <!-- Will be populated by JavaScript -->
                                    </tbody>
                                </table>
                                <div id="no-child-trips" class="text-center text-muted py-2">
                                    No child trips found
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Assigned Vehicle (Auto-populated)</label>
                        <div class="input-group">
                            <input type="text" id="VehicleDisplay" class="form-control" readonly placeholder="Vehicle will auto-populate">
                            <input type="hidden" name="VehicleID" id="VehicleID">
                            <span class="input-group-text">
                                <i class="fas fa-truck"></i>
                            </span>
                        </div>
                        <div class="form-text mt-1">
                            <small>Selected vehicle from trip assignment</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Waypoints (in order) *</label>
                        <div id="waypoints-container">
                            <div class="input-group mb-2 waypoint-group">
                                <span class="input-group-text">1</span>
                                <input type="text" name="Waypoints[]" class="form-control waypoint-input"
                                       placeholder="Enter location or coordinates" required>
                                <button type="button" class="btn btn-danger remove-waypoint" disabled>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="input-group mb-2 waypoint-group">
                                <span class="input-group-text">2</span>
                                <input type="text" name="Waypoints[]" class="form-control waypoint-input"
                                       placeholder="Enter location or coordinates" required>
                                <button type="button" class="btn btn-danger remove-waypoint">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" id="add-waypoint" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Waypoint
                        </button>
                        <div class="form-text">Add at least 2 waypoints for a route</div>
                    </div>
                    
                    <div class="mt-4 d-flex gap-2">
                        <button class="btn btn-success" type="submit">
                            <i class="fas fa-save"></i> Save Route Plan
                        </button>
                        <a href="{{ route('fleet.route_planner.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-sm-8 col-lg-9 col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Route Map Preview</h5>
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 60vh;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src='https://unpkg.com/maplibre-gl@latest/dist/maplibre-gl.js'></script>
    <script>
        // Initialize map
        var map = new maplibregl.Map({
            container: 'map',
            style: 'https://demotiles.maplibre.org/style.json',
            center: [36.8219, -1.2921],
            zoom: 1
        });

        // Add navigation controls
        map.addControl(new maplibregl.NavigationControl());

        document.addEventListener('DOMContentLoaded', function() {
            // Trip selection handler
            const tripSelect = document.getElementById('TripNo');
            const vehicleDisplay = document.getElementById('VehicleDisplay');
            const vehicleIdInput = document.getElementById('VehicleID');
            const childTripsContainer = document.getElementById('child-trips-container');
            const childTripsBody = document.getElementById('child-trips-body');
            const noChildTrips = document.getElementById('no-child-trips');
            const childTripsTable = document.getElementById('child-trips-table');
            
            tripSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    const vehicleId = selectedOption.getAttribute('data-vehicle-id');
                    const vehicleRegistration = selectedOption.getAttribute('data-vehicle-registration');
                    const hasChildren = selectedOption.getAttribute('data-has-children') === 'true';
                    
                    // Update vehicle information
                    vehicleIdInput.value = vehicleId;
                    vehicleDisplay.value = vehicleRegistration;
                    
                    // Show/hide child trips info
                    if (hasChildren) {
                        try {
                            const childTrips = JSON.parse(selectedOption.getAttribute('data-child-trips'));
                            
                            if (childTrips.length > 0) {
                                let html = '';
                                childTrips.forEach(child => {
                                    html += `
                                        <tr>
                                            <td>${child.TripNo || 'N/A'}</td>
                                            <td>${child.StartLocation || 'N/A'}</td>
                                            <td>${child.EndLocation || 'N/A'}</td>
                                        </tr>
                                    `;
                                });
                                childTripsBody.innerHTML = html;
                                noChildTrips.style.display = 'none';
                                childTripsTable.style.display = 'table';
                                childTripsContainer.style.display = 'block';
                            } else {
                                childTripsBody.innerHTML = '';
                                noChildTrips.style.display = 'block';
                                childTripsTable.style.display = 'none';
                                childTripsContainer.style.display = 'block';
                            }
                        } catch (e) {
                            console.error('Error parsing child trips:', e);
                            childTripsContainer.style.display = 'none';
                        }
                    } else {
                        childTripsContainer.style.display = 'none';
                        childTripsBody.innerHTML = '';
                    }
                    
                    // Focus on first waypoint
                    document.querySelector('.waypoint-input').focus();
                } else {
                    // Reset if no trip selected
                    vehicleIdInput.value = '';
                    vehicleDisplay.value = '';
                    childTripsContainer.style.display = 'none';
                    childTripsBody.innerHTML = '';
                }
            });
            
            // Waypoints functionality
            const waypointsContainer = document.getElementById('waypoints-container');
            const addWaypointBtn = document.getElementById('add-waypoint');
            
            // Add waypoint button
            addWaypointBtn.addEventListener('click', function() {
                const waypointGroups = waypointsContainer.querySelectorAll('.waypoint-group');
                const newIndex = waypointGroups.length + 1;
                
                const newGroup = document.createElement('div');
                newGroup.className = 'input-group mb-2 waypoint-group';
                newGroup.innerHTML = `
                    <span class="input-group-text">${newIndex}</span>
                    <input type="text" name="Waypoints[]" class="form-control waypoint-input" 
                           placeholder="Enter location or coordinates" required>
                    <button type="button" class="btn btn-danger remove-waypoint">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                
                waypointsContainer.appendChild(newGroup);
                
                // Enable remove button on previous first waypoint if there are more than 2
                if (waypointGroups.length >= 2) {
                    waypointGroups[0].querySelector('.remove-waypoint').disabled = false;
                }
            });
            
            // Remove waypoint
            waypointsContainer.addEventListener('click', function(e) {
                if (e.target.closest('.remove-waypoint')) {
                    const group = e.target.closest('.waypoint-group');
                    const groups = waypointsContainer.querySelectorAll('.waypoint-group');
                    
                    if (groups.length > 2) {
                        group.remove();
                        renumberWaypoints();
                        
                        // Disable remove on first if only 2 waypoints left
                        if (waypointsContainer.querySelectorAll('.waypoint-group').length === 2) {
                            waypointsContainer.querySelector('.waypoint-group:first-child .remove-waypoint').disabled = true;
                        }
                    } else {
                        alert('A route must have at least 2 waypoints.');
                    }
                }
            });
            
            // Renumber waypoints
            function renumberWaypoints() {
                const groups = waypointsContainer.querySelectorAll('.waypoint-group');
                groups.forEach((group, index) => {
                    group.querySelector('.input-group-text').textContent = index + 1;
                });
            }
            
            // Form validation
            document.getElementById('route-form').addEventListener('submit', function(e) {
                // Validate at least 2 waypoints
                const waypointInputs = document.querySelectorAll('input[name="Waypoints[]"]');
                const validWaypoints = Array.from(waypointInputs).filter(input => input.value.trim() !== '');
                
                if (validWaypoints.length < 2) {
                    e.preventDefault();
                    alert('Please add at least 2 valid waypoints for the route.');
                    return false;
                }
                
                // Validate trip selection
                if (!tripSelect.value) {
                    e.preventDefault();
                    alert('Please select a trip number.');
                    return false;
                }
                
                // Validate vehicle is populated
                if (!vehicleIdInput.value) {
                    e.preventDefault();
                    alert('Vehicle information is not properly populated. Please select a valid trip.');
                    return false;
                }
                
                return true;
            });
            
            // Disable remove button on first waypoint initially (since we need at least 2)
            waypointsContainer.querySelector('.waypoint-group:first-child .remove-waypoint').disabled = true;
        });
    </script>
@endsection