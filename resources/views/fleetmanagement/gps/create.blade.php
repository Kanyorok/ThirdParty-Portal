@extends('layouts.app')
@section('title', 'GPS & Telematics')
@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Add Telematics Device</h2>

    <form action="submit_telematics.php" method="post">
        <div class="mb-3">
            <label for="vehicle_id" class="form-label">Vehicle</label>
            <input type="text" class="form-control" id="vehicle_id" name="vehicle_id" placeholder="e.g., Toyota Hilux - KDA 123A" required>
        </div>

        <div class="mb-3">
            <label for="device_id" class="form-label">GPS Device ID</label>
            <input type="text" class="form-control" id="device_id" name="device_id" placeholder="e.g., GPS123456789" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="install_date" class="form-label">Installation Date</label>
                <input type="date" class="form-control" id="install_date" name="install_date" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="subscription_renewal" class="form-label">Renewal Date</label>
                <input type="date" class="form-control" id="subscription_renewal" name="subscription_renewal">
            </div>
        </div>

        <div class="mb-3">
            <label for="provider" class="form-label">Service Provider</label>
            <input type="text" class="form-control" id="provider" name="provider" placeholder="e.g., TrackMe Ltd." required>
        </div>

        <div class="mb-3">
            <label for="subscription_status" class="form-label">Subscription Status</label>
            <select class="form-select" id="subscription_status" name="subscription_status" required>
                <option disabled selected>Select status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="expired">Expired</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">Additional Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Optional remarks..."></textarea>
        </div>
        <div class="text-end">
            <button type="submit" class="btn btn-success">Save Entry</button>
        </div>
    </form>
</div>
@endsection








