@extends('layouts.app')
@section('title', 'Request a Vehicle')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">🚗 Vehicle Request Form</h4>
    <form method="POST" action="{{ route('fleet.vehicle_requests.store') }}">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label for="RequestedBy" class="form-label">Requested By</label>
                <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
            </div>

            <div class="col-md-6">
                <label for="RequestDate" class="form-label">Request Date</label>
                <input type="date" name="RequestDate" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="col-md-6">
                <label for="PickupLocation" class="form-label">Pickup Location</label>
                <input type="text" name="PickupLocation" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label for="Destination" class="form-label">Destination</label>
                <input type="text" name="Destination" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label for="TripDate" class="form-label">Trip Date</label>
                <input type="date" name="TripDate" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label for="StartTime" class="form-label">Start Time</label>
                <input type="time" name="StartTime" class="form-control">
            </div>

            <div class="col-md-4">
                <label for="EndTime" class="form-label">End Time</label>
                <input type="time" name="EndTime" class="form-control">
            </div>

            <div class="col-md-12">
                <label for="Purpose" class="form-label">Purpose</label>
                <textarea name="Purpose" class="form-control" rows="3" required></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-success">📤 Submit Request</button>
        </div>
    </form>
</div>
@endsection