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

        <form action="{{ route('fleet.assignments.update', $assignment) }}" method="POST">

            @csrf
            @method('PUT')

            <div class="row g-3">
                {{-- Trip --}}
                <div class="col-md-6">
                    <label class="form-label">Trip</label>
                    <select name="TripNo" id="TripNo" class="form-select" required>
                        <option value="">-- Select Trip --</option>
                        @foreach ($fleetTrips as $trip)
                            <option value="{{ $trip->Id }}"
                                {{ $assignment->TripNo == $trip->Id ? 'selected' : '' }}>
                                {{ $trip->TripNo }} ({{ $trip->StartLocation }} → {{ $trip->EndLocation }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Vehicle Type --}}
                <div class="col-md-6">
                    <label class="form-label">Vehicle Type</label>
                    <input type="text" name="VehicleType" id="VehicleType"
                           class="form-control" value="{{ $assignment->VehicleType }}" readonly>
                </div>

                {{-- Vehicle --}}
                <div class="col-md-6">
                    <label class="form-label">Vehicle</label>
                    <select name="VehicleID" id="VehicleID" class="form-select" required>
                        <option value="">-- Select Vehicle --</option>
                        @foreach ($fleetVehicles as $vehicle)
                            <option value="{{ $vehicle->Id }}"
                                {{ $assignment->VehicleID == $vehicle->Id ? 'selected' : '' }}>
                                {{ $vehicle->RegistrationNo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Driver --}}
                <div class="col-md-6">
                    <label class="form-label">Driver</label>
                    <input type="hidden" name="DriverID" id="DriverID" value="{{ $assignment->DriverID }}">
                    <input type="text" id="DriverName" class="form-control"
                           value="{{ $assignment->driver->Name ?? 'No driver assigned' }}" readonly>
                </div>

                {{-- Last Inspection Date --}}
                <div class="col-md-6">
                    <label class="form-label">Last Inspection Date</label>
                    <input type="text" name="LastInspectionDate" id="LastInspectionDate"
                           class="form-control" value="{{ $assignment->LastInspectionDate ?? 'N/A' }}" readonly>
                </div>

                {{-- Assignment Date --}}
                <div class="col-md-4">
                    <label class="form-label">Assignment Date</label>
                    <input type="date" name="AssignmentDate" class="form-control"
                           value="{{ \Carbon\Carbon::parse($assignment->AssignmentDate)->format('Y-m-d') }}" required>
                </div>

                {{-- Purpose --}}
                <div class="col-md-8">
                    <label class="form-label">Purpose</label>
                    <input type="text" name="Purpose" class="form-control"
                           value="{{ $assignment->Purpose }}"
                           placeholder="e.g., Delivery, Staff Transport, Branch Transfer">
                </div>

                {{-- Notes --}}
                <div class="col-md-12">
                    <label class="form-label">Notes</label>
                    <textarea name="Notes" class="form-control" rows="2"
                              placeholder="Additional remarks...">{{ $assignment->Notes }}</textarea>
                </div>

                {{-- Assigned By --}}
                <div class="col-md-6">
                    <label class="form-label">Assigned By</label>
                    <select name="AssignedBy" class="form-select" required>
                        <option value="">-- Select Employee --</option>
                        @foreach ($assigners as $id => $name)
                            <option value="{{ $id }}" {{ $assignment->AssignedBy == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary" type="submit">💾 Update Assignment</button>
                <a href="{{ route('fleet.assignments.index') }}" class="btn btn-secondary">⬅ Back</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function loadVehicleData(vehicleId) {
            if (!vehicleId) return;

            fetch(`/fleet/assignments/get-inspection/${vehicleId}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('LastInspectionDate').value = data.lastInspectionDate ?? 'N/A';
                });

            fetch(`/fleet/assignments/get-driver/${vehicleId}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('DriverID').value = data.driverId ?? '';
                    document.getElementById('DriverName').value = data.driverName ?? 'No driver assigned';
                });
        }

        document.getElementById('TripNo').addEventListener('change', function () {
            let Id = this.value;
            if (!Id) return;

            fetch(`/fleet/assignments/get-vehicles/${Id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('VehicleType').value = data.fleetVehicleType;

                    let vehicleSelect = document.getElementById('VehicleID');
                    vehicleSelect.innerHTML = '<option value="">-- Select Vehicle --</option>';
                    data.vehicles.forEach(v => {
                        vehicleSelect.innerHTML += `<option value="${v.Id}" ${v.Id == "{{ $assignment->VehicleID }}" ? 'selected' : ''}>${v.RegistrationNo}</option>`;
                    });

                    loadVehicleData("{{ $assignment->VehicleID }}");
                });
        });

        document.getElementById('VehicleID').addEventListener('change', function () {
            loadVehicleData(this.value);
        });

        window.addEventListener('DOMContentLoaded', function () {
            let existingVehicle = document.getElementById('VehicleID').value;
            if (existingVehicle) {
                loadVehicleData(existingVehicle);
            }
        });
    </script>
@endpush
