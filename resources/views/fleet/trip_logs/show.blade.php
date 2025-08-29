@extends('layouts.app')
@section('title', 'Trip Log Details')

@section('content')
    <div class="d-flex gap-4">
        <!-- Side Panel -->
        <div class="card shadow rounded-4 p-4" style="min-width: 300px; max-width: 350px;">
            <h5 class="mb-3">📋 Trip Details</h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Trip No:</strong> {{ $tripLogs->TripNo }}</li>
                <li class="list-group-item"><strong>Start Date:</strong> {{ $tripLogs->TripStartDate }}</li>
                <li class="list-group-item"><strong>Start Time:</strong> {{ $tripLogs->StartTime }}</li>
                <li class="list-group-item"><strong>End Date:</strong> {{ $tripLogs->TripEndDate }}</li>
                <li class="list-group-item"><strong>End Time:</strong> {{ $tripLogs->EndTime }}</li>
                <li class="list-group-item"><strong>Start Location:</strong> {{ $tripLogs->StartLocation }}</li>
                <li class="list-group-item"><strong>End Location:</strong> {{ $tripLogs->EndLocation }}</li>
                <li class="list-group-item"><strong>Route Used:</strong> {{ $tripLogs->Route }}</li>
                <li class="list-group-item"><strong>Distance Covered:</strong> {{ $tripLogs->DistanceCovered }} km</li>
                <li class="list-group-item"><strong>Purpose:</strong> {{ $tripLogs->Purpose }}</li>
                <li class="list-group-item"><strong>Notes:</strong> {{ $tripLogs->Notes ?? 'N/A' }}</li>
            </ul>
        </div>

        <!-- Table Panel -->
        <div class="flex-grow-1 card shadow rounded-4 p-4">
            <h5 class="mb-3">👤 Other Details</h5>
            <table class="table table-bordered">
                <tbody>
                <tr>
                    <th>Vehicle</th>
                    <td>{{ $tripLogs->vehicle->RegistrationNo ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Driver Type</th>
                    <td>{{ $tripLogs->driverType->Description }}</td>
                </tr>
                <tr>
                    <th>Driver</th>
                    <td>
                        @if($tripLogs->driverType->Description === 'Permanent')
                            {{ $tripLogs->driverPermanent->FullName ?? 'N/A' }}
                        @else
                            {{ $tripLogs->driverContracted->FullName ?? 'N/A' }}
                        @endif
                    </td>
                </tr>
                </tbody>
            </table>

            <div class="mt-4 d-flex justify-content-between">
                <a href="{{ route('fleet.trip_logs.index') }}" class="btn btn-secondary">⬅ Back</a>
                <div>
                    <!-- Button trigger modal -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#editTripModal">
                        ✏ Edit
                    </button>

                    <form action="{{ route('fleet.trip_logs.destroy', $tripLogs->Id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"
                                onclick="return confirm('Are you sure you want to delete this Trip Log?')">
                            🗑 Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Edit Trip Modal (aligned with your create form behavior) -->
<div class="modal fade" id="editTripModal" tabindex="-1" aria-labelledby="editTripModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form action="{{ route('fleet.trip_logs.update', $tripLogs->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="modal-header">
          <h5 class="modal-title" id="editTripModalLabel">✏ Edit Trip Log</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            {{-- Vehicle --}}
            <div class="col-md-4">
              <label for="VehicleID" class="form-label">Select Vehicle</label>
              <select name="VehicleID" class="form-select" required>
                <option value="">-- Select Vehicle --</option>
                @foreach($vehicles as $vehicle)
                  <option value="{{ $vehicle->Id }}" {{ $tripLogs->VehicleID == $vehicle->Id ? 'selected' : '' }}>
                    {{ $vehicle->RegistrationNo }} - {{ $vehicle->Make }} {{ $vehicle->Model }}
                  </option>
                @endforeach
              </select>
            </div>

            {{-- Driver Type (stores CodeDetail->ID) --}}
            <div class="col-md-4">
              <label for="DriverType" class="form-label">Driver Type</label>
              <select name="DriverType" id="DriverTypeEdit" class="form-select" required>
                <option value="">-- Select Driver Type --</option>
                @foreach($driverTypes as $type)
                  <option value="{{ $type->ID }}" {{ $tripLogs->DriverType == $type->ID ? 'selected' : '' }}>
                    {{ $type->Description }}
                  </option>
                @endforeach
              </select>
            </div>

            {{-- Driver (both lists; preselect only within the correct type to avoid collisions) --}}
            @php
              $currentType = optional($tripLogs->driverType)->Description; // "Permanent" or "Contracted"
            @endphp
            <div class="col-md-4">
              <label for="DriverID" class="form-label">Select Driver</label>
              <select
                name="DriverID"
                id="DriverIDEdit"
                class="form-select"
                required
                data-current-id="{{ $tripLogs->DriverID }}"
                data-current-type="{{ $currentType }}"
              >
                <option value="">-- Select Driver --</option>

                {{-- Permanent drivers --}}
                @foreach($drivers as $driver)
                  <option
                    value="{{ $driver->Id }}"
                    data-type="Permanent"
                    {{ ($currentType === 'Permanent' && $tripLogs->DriverID == $driver->Id) ? 'selected' : '' }}
                  >
                    {{ $driver->FullName }}
                  </option>
                @endforeach

                {{-- Contracted drivers --}}
                @foreach($contractedDrivers as $cDriver)
                  {{-- If ContractedDriver PK is "Id", keep ->Id; if it's "DriverId", change both here and in relationships --}}
                  <option
                    value="{{ $cDriver->Id }}"
                    data-type="Contracted"
                    {{ ($currentType === 'Contracted' && $tripLogs->DriverID == $cDriver->Id) ? 'selected' : '' }}
                  >
                    {{ $cDriver->FullName }}
                  </option>
                @endforeach
              </select>
            </div>

            {{-- Dates --}}
            <div class="col-md-6">
              <label for="TripStartDate" class="form-label">Trip Start Date</label>
              <input type="date" name="TripStartDate" class="form-control"
                     value="{{ $tripLogs->TripStartDate ? \Carbon\Carbon::parse($tripLogs->TripStartDate)->format('Y-m-d') : '' }}">
            </div>
            <div class="col-md-6">
              <label for="TripEndDate" class="form-label">Trip End Date</label>
              <input type="date" name="TripEndDate" class="form-control"
                     value="{{ $tripLogs->TripEndDate ? \Carbon\Carbon::parse($tripLogs->TripEndDate)->format('Y-m-d') : '' }}">
            </div>

            {{-- Times --}}
            <div class="col-md-6">
              <label for="StartTime" class="form-label">Start Time</label>
              <input type="time" name="StartTime" class="form-control"
                     value="{{ $tripLogs->StartTime ? \Carbon\Carbon::parse($tripLogs->StartTime)->format('H:i') : '' }}">
            </div>
            <div class="col-md-6">
              <label for="EndTime" class="form-label">End Time</label>
              <input type="time" name="EndTime" class="form-control"
                     value="{{ $tripLogs->EndTime ? \Carbon\Carbon::parse($tripLogs->EndTime)->format('H:i') : '' }}">
            </div>

            {{-- Locations --}}
            <div class="col-md-6">
              <label for="StartLocation" class="form-label">Start Location</label>
              <input type="text" name="StartLocation" class="form-control" value="{{ $tripLogs->StartLocation }}">
            </div>
            <div class="col-md-6">
              <label for="EndLocation" class="form-label">End Location</label>
              <input type="text" name="EndLocation" class="form-control" value="{{ $tripLogs->EndLocation }}">
            </div>

            {{-- Route & Distance --}}
            <div class="col-md-6">
              <label for="Route" class="form-label">Route</label>
              <input type="text" name="Route" class="form-control" value="{{ $tripLogs->Route }}">
            </div>
            <div class="col-md-6">
              <label for="DistanceCovered" class="form-label">Distance Covered (km)</label>
              <input type="number" step="0.01" name="DistanceCovered" class="form-control" value="{{ $tripLogs->DistanceCovered }}">
            </div>

            {{-- Purpose & Notes --}}
            <div class="col-md-12">
              <label for="Purpose" class="form-label">Purpose</label>
              <input type="text" name="Purpose" class="form-control" value="{{ $tripLogs->Purpose }}">
            </div>
            <div class="col-md-12">
              <label for="Notes" class="form-label">Notes</label>
              <textarea name="Notes" class="form-control">{{ $tripLogs->Notes }}</textarea>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </div>
@endsection
{{-- Same filtering behavior as create, but also fix selection if it points to hidden option --}}
@section('scripts')
<script>
(function() {
  const driverTypeSelect = document.getElementById('DriverTypeEdit');
  const driverSelect     = document.getElementById('DriverIDEdit');

  function filterDrivers() {
    const selectedType = driverTypeSelect.options[driverTypeSelect.selectedIndex]?.text?.trim();

    let hasVisibleSelected = false;

    for (const option of driverSelect.options) {
      if (!option.value) continue; // skip placeholder
      const show = option.dataset.type === selectedType;
      option.style.display = show ? 'block' : 'none';
      if (show && option.selected) hasVisibleSelected = true;
    }

    // If the currently selected option is hidden, try to switch to the record's original one for this type
    if (!hasVisibleSelected) {
      const currentId   = driverSelect.getAttribute('data-current-id');
      const currentType = driverSelect.getAttribute('data-current-type');
      const targetType  = selectedType || currentType;

      // pick the option that matches both id and type
      const match = Array.from(driverSelect.options).find(
        o => o.value && o.value === currentId && o.dataset.type === targetType
      );

      if (match) {
        driverSelect.value = currentId;
      } else {
        driverSelect.value = '';
      }
    }
  }

  driverTypeSelect.addEventListener('change', filterDrivers);
  window.addEventListener('DOMContentLoaded', filterDrivers);
})();
</script>
@endsection
