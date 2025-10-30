@extends('layouts.app')
@section('title', 'New Vehicle Assignment')

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
    <h4 class="mb-4">🚘 New Vehicle Assignment</h4>

    <form id="assignmentForm" action="{{ route('fleet.assignments.store') }}" method="POST">
        @csrf

        <div class="row g-3">
            {{-- Trip --}}
            <div class="col-md-6">
                <label class="form-label">Trip</label>
                <select name="TripNo" id="TripNo" class="form-select" required>
                    <option value="">-- Select Trip --</option>
                    @foreach ($fleetTrips as $trip)
                        <option value="{{ $trip->Id }}">
                            {{ $trip->TripNo }} ({{ $trip->StartLocation }} → {{ $trip->EndLocation }})
                        </option>
                    @endforeach
                </select>
                {{-- Hidden field to store TripStartDate --}}
                <input type="hidden" id="TripStartDate" name="TripStartDate">
            </div>

            {{-- Vehicle Type (auto from trip) --}}
            <div class="col-md-6">
                <label class="form-label">Vehicle Type</label>
                <input type="text" name="VehicleType" id="VehicleType" class="form-control" readonly>
            </div>

            {{-- Vehicle (filtered by VehicleType) --}}
            <div class="col-md-6">
                <label class="form-label">Vehicle</label>
                <select name="VehicleID" id="VehicleID" class="form-select" required>
                    <option value="">-- Select Vehicle --</option>
                </select>
            </div>

            {{-- Driver --}}
            <div class="col-md-6">
                <label class="form-label">Driver</label>
                <input type="hidden" name="DriverID" id="DriverID">
                <input type="text" id="DriverName" class="form-control" readonly>
            </div>

            {{-- Last Inspection Date (auto from vehicle) --}}
            <div class="col-md-6">
                <label class="form-label">Last Inspection Date</label>
                <input type="text" name="LastInspectionDate" id="LastInspectionDate" class="form-control" readonly>
            </div>

            {{-- Assignment Date --}}
            <div class="col-md-4">
                <label class="form-label">Assignment Date</label>
                <input type="date" name="AssignmentDate" class="form-control" required>
            </div>

            {{-- Purpose --}}
            <div class="col-md-8">
                <label class="form-label">Purpose</label>
                <input type="text" name="Purpose" class="form-control"
                       placeholder="e.g., Delivery, Staff Transport, Branch Transfer">
            </div>

            {{-- Notes --}}
            <div class="col-md-12">
                <label class="form-label">Notes</label>
                <textarea name="Notes" class="form-control" rows="2" placeholder="Additional remarks..."></textarea>
            </div>

            {{-- Assigned By --}}
            <div class="col-md-6">
                <label class="form-label">Assigned By</label>
                <select name="AssignedBy" class="form-select" required>
                    <option value="">-- Select Employee --</option>
                    @foreach ($assigners as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-4">
            <button id="submitBtn" class="btn btn-primary" type="submit">✅ Assign Vehicle</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('TripNo').addEventListener('change', function() {
    let Id = this.value;
    if (!Id) return;

    // Fetch vehicles for selected trip
    fetch(`/fleet/assignments/get-vehicles/${Id}`)
        .then(res => res.json())
        .then(data => {
            // Set vehicle type
            document.getElementById('VehicleType').value = data.fleetVehicleType;

            // Store trip start date
            document.getElementById('TripStartDate').value = data.tripDate;

            // Populate vehicles
            let vehicleSelect = document.getElementById('VehicleID');
            vehicleSelect.innerHTML = '<option value="">-- Select Vehicle --</option>';
            data.vehicles.forEach(v => {
                vehicleSelect.innerHTML += `<option value="${v.Id}">${v.RegistrationNo}</option>`;
            });
        });
});

document.getElementById('VehicleID').addEventListener('change', function() {
    let Id = this.value;
    if (!Id) return;

    fetch(`/fleet/assignments/get-inspection/${Id}`)
        .then(res => res.json())
        .then(data => {
            let lastInspectionDate = data.lastInspectionDate ?? null;
            let tripDate = document.getElementById('TripStartDate').value;

            document.getElementById('LastInspectionDate').value = lastInspectionDate ?? 'N/A';

            if (lastInspectionDate && tripDate && new Date(lastInspectionDate) < new Date(tripDate)) {
                // Show error message with link
                let inspectionLink = "{{ route('fleet.vehicle_inspection.create') }}?vehicle=" + Id;
                let errorDiv = document.createElement('div');
                errorDiv.className = "alert alert-danger mt-3";
                errorDiv.innerHTML = `
                    ❌ Vehicle cannot be assigned. <br>
                    Last inspection was on (<b>${lastInspectionDate}</b>) 
                    👉 Please inspect first. <a href="${inspectionLink}" class="btn btn-sm btn-warning">Create New Inspection</a>
                `;

                // Insert error above the form
                let form = document.getElementById('assignmentForm');
                form.prepend(errorDiv);

                // Reset vehicle selection
                this.value = '';
                document.getElementById('LastInspectionDate').value = '';

                // Block submission
                document.getElementById('submitBtn').disabled = true;
            } else {
                document.getElementById('submitBtn').disabled = false;
            }
        });

            fetch(`/fleet/assignments/get-driver/${Id}`)
            
            
                .then(res => res.json())
                .then(data => {
                    document.getElementById('DriverID').value = data.driverId ?? '';
                    document.getElementById('DriverName').value = data.driverName ?? 'No driver assigned';
                });
        });
    </script>
@endpush
