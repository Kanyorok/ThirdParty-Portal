@extends('layouts.app')
@section('title', 'Edit Vehicle Assignment')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">✏️ Edit Vehicle Assignment - {{ $assignment->Id }}</h4>

        <form id="assignmentForm" action="{{ route('fleet.assignments.update', $assignment) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                {{-- Trip --}}
                <div class="col-md-6">
                    <label class="form-label">Trip<span class="text-danger">*</span></label>
                    <select name="TripNo" id="TripNo" class="form-select" required>
                        <option value="">-- Select Trip --</option>
                        @foreach ($fleetTrips as $trip)
                            <option value="{{ $trip->Id }}"
                                {{ $assignment->TripNo == $trip->Id ? 'selected' : '' }}
                                data-vehicle-type="{{ $trip->VehicleType }}">
                                {{ $trip->TripNo }} ({{ $trip->StartLocation }} → {{ $trip->EndLocation }})
                            </option>
                        @endforeach
                    </select>
                    {{-- Hidden field to store TripStartDate --}}
                    <input type="hidden" id="TripStartDate" name="TripStartDate" 
                           value="{{ $assignment->trip->TripStartDate ?? '' }}">
                </div>

                {{-- Vehicle Type --}}
                <div class="col-md-6">
                    <label class="form-label">Vehicle Type</label>
                    <input type="text" name="VehicleTypeDisplay" id="VehicleTypeDisplay"
                           class="form-control" value="{{ $assignment->fleetVehicleType->Description ?? 'N/A' }}" readonly>
                    <input type="hidden" name="VehicleType" id="VehicleType" value="{{ $assignment->VehicleType }}">
                </div>

                {{-- Vehicle --}}
                <div class="col-md-6">
                    <label class="form-label">Vehicle<span class="text-danger">*</span></label>
                    <select name="VehicleID" id="VehicleID" class="form-select" required>
                        <option value="">-- Select Vehicle --</option>
                        {{-- Vehicles will be populated by JavaScript based on selected trip --}}
                    </select>
                </div>

                {{-- Driver --}}
                <div class="col-md-6">
                    <label class="form-label">Driver</label>
                    <input type="hidden" name="DriverID" id="DriverID" value="{{ $assignment->DriverID }}">
                    <input type="text" id="DriverName" class="form-control"
                           value="{{ $assignment->driver->FullName ?? 'No driver assigned' }}" readonly>
                </div>

                {{-- Last Inspection Date --}}
                <div class="col-md-6">
                    <label class="form-label">Last Inspection Date</label>
                    <input type="text" name="LastInspectionDate" id="LastInspectionDate"
                           class="form-control" value="{{ $assignment->LastInspectionDate ?? 'N/A' }}" readonly>
                </div>

                {{-- Assignment Date --}}
                <div class="col-md-4">
                    <label class="form-label">Assignment Date<span class="text-danger">*</span></label>
                    <input type="date" name="AssignmentDate" class="form-control"
                           value="{{ \Carbon\Carbon::parse($assignment->AssignmentDate)->format('Y-m-d') }}" required>
                </div>

                {{-- Purpose --}}
                <div class="col-md-8">
                    <label class="form-label">Purpose<span class="text-danger">*</span></label>
                    <input type="text" name="Purpose" class="form-control"
                           value="{{ $assignment->Purpose }}"
                           placeholder="e.g., Delivery, Staff Transport, Branch Transfer" required>
                </div>

                {{-- Notes --}}
                <div class="col-md-12">
                    <label class="form-label">Notes</label>
                    <textarea name="Notes" class="form-control" rows="2"
                              placeholder="Additional remarks...">{{ $assignment->Notes }}</textarea>
                </div>

                {{-- Assigned By (Read-only) --}}
                <div class="col-md-6">
                    <label class="form-label">Assigned By<span class="text-danger">*</span></label>
                    
                    <!-- Hidden field for form submission -->
                    <input type="hidden" name="AssignedBy" id="AssignedBy" 
                           value="{{ $assignment->AssignedBy }}">
                    
                    <!-- Read-only display field -->
                    <input type="text" class="form-control" 
                           value="{{ $assignment->assigner->FirstName ?? '' }} {{ $assignment->assigner->LastName ?? '' }}"
                           readonly
                           placeholder="Original assigner">
                    
                    <small class="text-muted">Assigned by cannot be changed</small>
                </div>
            </div>

            <div class="mt-4">
                <button id="submitBtn" class="btn btn-primary" type="submit">💾 Update Assignment</button>
                <a href="{{ route('fleet.assignments.index') }}" class="btn btn-secondary">⬅ Back</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tripSelect = document.getElementById('TripNo');
        const vehicleTypeDisplay = document.getElementById('VehicleTypeDisplay');
        const vehicleTypeHidden = document.getElementById('VehicleType');
        const vehicleSelect = document.getElementById('VehicleID');
        const driverIdInput = document.getElementById('DriverID');
        const driverNameInput = document.getElementById('DriverName');
        const inspectionDateInput = document.getElementById('LastInspectionDate');
        const tripStartDateInput = document.getElementById('TripStartDate');
        const submitBtn = document.getElementById('submitBtn');
        
        const assignmentVehicleId = "{{ $assignment->VehicleID }}";
        const assignmentVehicleType = "{{ $assignment->VehicleType }}";

        // Function to populate vehicle type display
        function updateVehicleTypeDisplay(vehicleTypeId) {
            const vehicleTypeOptions = @json($vehicleTypes);
            vehicleTypeDisplay.value = vehicleTypeOptions[vehicleTypeId] || 'N/A';
            vehicleTypeHidden.value = vehicleTypeId;
        }

        // Function to load vehicles by vehicle type
        function loadVehiclesByType(vehicleTypeId, selectedVehicleId = '') {
            if (!vehicleTypeId) {
                vehicleSelect.innerHTML = '<option value="">-- Select Vehicle --</option>';
                return;
            }

            // Get all vehicles and filter by vehicle type
            const allVehicles = @json($fleetVehicles);
            const filteredVehicles = allVehicles.filter(vehicle => vehicle.VehicleType == vehicleTypeId);
            
            vehicleSelect.innerHTML = '<option value="">-- Select Vehicle --</option>';
            
            if (filteredVehicles.length > 0) {
                filteredVehicles.forEach(vehicle => {
                    const selected = vehicle.Id == selectedVehicleId ? 'selected' : '';
                    vehicleSelect.innerHTML += `<option value="${vehicle.Id}" ${selected}>${vehicle.RegistrationNo}</option>`;
                });
            } else {
                vehicleSelect.innerHTML += '<option value="" disabled>No vehicles available for this type</option>';
            }
        }

        // Function to load vehicle data
        async function loadVehicleData(vehicleId) {
            if (!vehicleId) {
                driverIdInput.value = '';
                driverNameInput.value = '';
                inspectionDateInput.value = '';
                submitBtn.disabled = false;
                return;
            }

            try {
                // Fetch last inspection date
                const inspectionResponse = await fetch(`/fleet/assignments/get-inspection/${vehicleId}`);
                const inspectionData = await inspectionResponse.json();
                
                const lastInspectionDate = inspectionData.lastInspectionDate || null;
                const tripDate = tripStartDateInput.value;
                
                inspectionDateInput.value = lastInspectionDate || 'No inspection found';

                // Check if inspection is valid for the trip
                if (lastInspectionDate && tripDate && new Date(lastInspectionDate) < new Date(tripDate)) {
                    let existingError = document.querySelector('.inspection-error');
                    
                    if (!existingError) {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = "alert alert-danger mt-3 inspection-error";
                        errorDiv.innerHTML = `
                            ❌ Vehicle cannot be assigned. <br>
                            Last inspection was on <b>${lastInspectionDate}</b><br>
                            👉 Please inspect first. 
                            <a href="{{ route('fleet.vehicle_inspection.create') }}?vehicle=${vehicleId}" class="btn btn-sm btn-warning ms-2">Create New Inspection</a>
                        `;
                        
                        // Insert error above the form
                        document.getElementById('assignmentForm').prepend(errorDiv);
                    }
                    
                    // Reset vehicle selection
                    vehicleSelect.value = '';
                    inspectionDateInput.value = '';
                    submitBtn.disabled = true;
                } else {
                    // Remove any existing error
                    const existingError = document.querySelector('.inspection-error');
                    if (existingError) {
                        existingError.remove();
                    }
                    submitBtn.disabled = false;
                }

                // Fetch driver assignment
                const driverResponse = await fetch(`/fleet/assignments/get-driver/${vehicleId}`);
                const driverData = await driverResponse.json();
                
                driverIdInput.value = driverData.driverId || '';
                driverNameInput.value = driverData.driverName || 'No driver assigned';
                
            } catch (error) {
                console.error('Error loading vehicle data:', error);
                alert('Error loading vehicle details. Please try again.');
            }
        }

        // Trip change event
        tripSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const vehicleTypeId = selectedOption.getAttribute('data-vehicle-type') || '';
            const tripId = this.value;
            
            if (!tripId) {
                updateVehicleTypeDisplay('');
                loadVehiclesByType('');
                tripStartDateInput.value = '';
                return;
            }

            // Update vehicle type display
            updateVehicleTypeDisplay(vehicleTypeId);
            
            // Load vehicles for this vehicle type
            loadVehiclesByType(vehicleTypeId, assignmentVehicleId);
            
            // Fetch trip start date
            fetch(`/fleet/assignments/get-vehicles/${tripId}`)
                .then(res => res.json())
                .then(data => {
                    tripStartDateInput.value = data.tripDate || '';
                    // If a vehicle is already selected, check its inspection
                    if (vehicleSelect.value) {
                        loadVehicleData(vehicleSelect.value);
                    }
                })
                .catch(error => {
                    console.error('Error fetching trip data:', error);
                });
        });

        // Vehicle change event
        vehicleSelect.addEventListener('change', function() {
            loadVehicleData(this.value);
        });

        // Initialize form on page load
        function initializeForm() {
            // Set the vehicle type display based on assignment
            updateVehicleTypeDisplay(assignmentVehicleType);
            
            // Load vehicles for the current vehicle type
            loadVehiclesByType(assignmentVehicleType, assignmentVehicleId);
            
            // Load vehicle data for the assigned vehicle
            if (assignmentVehicleId) {
                loadVehicleData(assignmentVehicleId);
            }
            
            // Set trip start date if available
            const selectedTrip = tripSelect.options[tripSelect.selectedIndex];
            if (selectedTrip && selectedTrip.value) {
                fetch(`/fleet/assignments/get-vehicles/${selectedTrip.value}`)
                    .then(res => res.json())
                    .then(data => {
                        tripStartDateInput.value = data.tripDate || '';
                    })
                    .catch(error => {
                        console.error('Error fetching initial trip data:', error);
                    });
            }
        }

        // Call initialization
        initializeForm();

        // Clear errors when form is reset
        document.getElementById('assignmentForm').addEventListener('reset', function() {
            const existingError = document.querySelector('.inspection-error');
            if (existingError) {
                existingError.remove();
            }
            submitBtn.disabled = false;
        });
    });
</script>
@endpush