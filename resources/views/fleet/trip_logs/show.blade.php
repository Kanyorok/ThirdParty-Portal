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
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editTripModal">
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
<!-- Edit Trip Modal -->
<div class="modal fade" id="editTripModal" tabindex="-1" aria-labelledby="editTripModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form action="{{ route('fleet.trip_logs.update', $tripLogs->Id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title" id="editTripModalLabel">✏ Edit Trip Log</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <!-- Trip No -->
            <div class="col-md-3">
              <label for="TripNo" class="form-label">Trip No</label>
              <input type="text" name="TripNo" class="form-control" value="{{ $tripLogs->TripNo }}" readonly>
            </div>

            <!-- Start Date / Time -->
            <div class="col-md-3">
              <label for="TripStartDate" class="form-label">Start Date</label>
              <input type="date" name="TripStartDate" class="form-control" value="{{ \Carbon\Carbon::parse($tripLogs->TripStartDate)->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
              <label for="StartTime" class="form-label">Start Time</label>
              <input type="time" name="StartTime" class="form-control" value="{{ $tripLogs->StartTime ? \Carbon\Carbon::parse($tripLogs->StartTime)->format('H:i') : '' }}">
            </div>

            <!-- End Date / Time -->
            <div class="col-md-3">
              <label for="TripEndDate" class="form-label">End Date</label>
              <input type="date" name="TripEndDate" class="form-control" value="{{ \Carbon\Carbon::parse($tripLogs->TripEndDate)->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
              <label for="EndTime" class="form-label">End Time</label>
              <input type="time" name="EndTime" class="form-control" value="{{ $tripLogs->EndTime ? \Carbon\Carbon::parse($tripLogs->EndTime)->format('H:i') : '' }}">
            </div>

            <!-- Vehicle -->
            <div class="col-md-3">
              <label for="VehicleID" class="form-label">Vehicle</label>
              <select name="VehicleID" class="form-select">
                @foreach($vehicles as $vehicle)
                  <option value="{{ $vehicle->Id }}" {{ $tripLogs->VehicleID == $vehicle->Id ? 'selected' : '' }}>
                    {{ $vehicle->RegistrationNo }}
                  </option>
                @endforeach
              </select>
            </div>

            <!-- Driver Type -->
            <div class="col-md-3">
              <label for="DriverType" class="form-label">Driver Type</label>
              <select name="DriverType" class="form-select">
                @foreach($driverTypes as $type)
                  <option value="{{ $type->Id }}" {{ $tripLogs->DriverType == $type->Id ? 'selected' : '' }}>
                    {{ $type->Description }}
                  </option>
                @endforeach
              </select>
            </div>

            <!-- Driver -->
            <div class="col-md-3">
              <label for="DriverID" class="form-label">Driver</label>
              <select name="DriverID" class="form-select">
                @foreach($drivers as $driver)
                  <option value="{{ $driver->Id }}" {{ $tripLogs->DriverID == $driver->Id ? 'selected' : '' }}>
                    {{ $driver->FullName }}
                  </option>
                @endforeach
              </select>
            </div>

            <!-- Locations -->
            <div class="col-md-6">
              <label for="StartLocation" class="form-label">Start Location</label>
              <input type="text" name="StartLocation" class="form-control" value="{{ $tripLogs->StartLocation }}">
            </div>
            <div class="col-md-6">
              <label for="EndLocation" class="form-label">End Location</label>
              <input type="text" name="EndLocation" class="form-control" value="{{ $tripLogs->EndLocation }}">
            </div>

            <!-- Other Details -->
            <div class="col-md-3">
              <label for="DistanceCovered" class="form-label">Distance Covered (km)</label>
              <input type="number" step="0.01" name="DistanceCovered" class="form-control" value="{{ $tripLogs->DistanceCovered }}">
            </div>
            <div class="col-md-3">
              <label for="Purpose" class="form-label">Purpose</label>
              <input type="text" name="Purpose" class="form-control" value="{{ $tripLogs->Purpose }}">
            </div>
            <div class="col-md-6">
              <label for="Route" class="form-label">Route Used</label>
              <input type="text" name="Route" class="form-control" value="{{ $tripLogs->Route }}">
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
      </form>
    </div>
  </div>
</div>

@endsection
