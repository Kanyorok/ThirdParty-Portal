@extends('layouts.app')
@section('title', 'Request a Vehicle')

@if($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">🚗 Vehicle Request Form</h4>

    <form method="POST" action="{{ route('fleet.vehicle_requests.store') }}">
        @csrf

        <div class="row g-3">
            {{-- Requested By --}}
            <div class="col-md-6">
                <label for="RequestedBy" class="form-label">Requester</label>
                <select name="RequestedBy" id="RequestedBy" class="form-select" required>
                    <option value="">-- Select Requester --</option>
                    @foreach($requester as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Request Date --}}
            <div class="col-md-6">
                <label for="RequestDate" class="form-label">Request Date</label>
                <input type="date" name="RequestDate" class="form-control" required>
            </div>

            {{-- Department --}}
            <div class="col-md-6">
                <label for="Department" class="form-label">Department</label>
                <select name="Department" class="form-select" required>
                    <option value="">-- Select Department --</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->Id }}">{{ $dept->Name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Trip No --}}
            <div class="col-md-6">
                <label for="TripNo" class="form-label">Trip No (Ongoing Trips)</label>
                <select name="TripNo" id="TripNo" class="form-select">
                    <option value="">-- Select Trip --</option>
                    @foreach($trips as $trip)
                    <option value="{{ $trip->Id }}"
                        data-from="{{ $trip->StartLocation }}"
                        data-to="{{ $trip->EndLocation }}"
                        data-start="{{ $trip->TripStartDate }}"
                        data-end="{{ $trip->TripEndDate }}"
                        data-type="{{ $trip->vehicle->vehicleType->Description ?? '' }} - {{ $trip->vehicle->RegistrationNo ?? 'N/A' }}"
                        data-typeid="{{ $trip->vehicle->VehicleType ?? '' }}">
                        {{ $trip->TripNo }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Pickup Location --}}
            <div class="col-md-6">
                <label for="FromLocation" class="form-label">Pickup Location</label>
                <input type="text" name="FromLocation" id="FromLocation" class="form-control" readonly>
            </div>

            {{-- Destination --}}
            <div class="col-md-6">
                <label for="ToLocation" class="form-label">Destination</label>
                <input type="text" name="ToLocation" id="ToLocation" class="form-control" readonly>
            </div>

            {{-- Trip Date (auto-filled with TripStartDate) --}}
            <div class="col-md-4">
                <label for="TripDate" class="form-label">Trip Date</label>
                <input type="date" name="TripDate" id="TripDate" class="form-control" required readonly>
            </div>

            {{-- Vehicle Type (readonly + hidden ID) --}}
            <div class="col-md-6">
                <label for="PreferredVehicleTypeName" class="form-label">Vehicle Type</label>
                <input type="text" id="PreferredVehicleTypeName" class="form-control" readonly>
                <input type="hidden" name="PreferredVehicleType" id="PreferredVehicleTypeId">
            </div>

            {{-- Passengers Count --}}
            <div class="col-md-6">
                <label for="PassengerCount" class="form-label">No. of Passengers</label>
                <input type="number" name="PassengerCount" class="form-control" min="1">
            </div>

            {{-- Purpose --}}
            <div class="col-md-12">
                <label for="Purpose" class="form-label">Purpose</label>
                <textarea name="Purpose" class="form-control" rows="3" required></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-success">Submit Request</button>
            <a href="{{ route('fleet.vehicle_requests.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    const tripSelect = document.getElementById('TripNo');
    const fromInput = document.getElementById('FromLocation');
    const toInput = document.getElementById('ToLocation');
    const tripDateInput = document.getElementById('TripDate');
    const vehicleTypeText = document.getElementById('PreferredVehicleTypeName');
    const vehicleTypeHidden = document.getElementById('PreferredVehicleTypeId');

    tripSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (!selected.value) {
            fromInput.value = '';
            toInput.value = '';
            tripDateInput.value = '';
            vehicleTypeText.value = '';
            vehicleTypeHidden.value = '';
            return;
        }

        fromInput.value = selected.dataset.from || '';
        toInput.value = selected.dataset.to || '';

        vehicleTypeText.value = selected.dataset.type || '';
        vehicleTypeHidden.value = selected.dataset.typeid || '';

        const startDate = selected.dataset.start || '';
        const endDate = selected.dataset.end || '';

        if (startDate) {
            tripDateInput.value = startDate;
        }
    });
</script>
@endsection
