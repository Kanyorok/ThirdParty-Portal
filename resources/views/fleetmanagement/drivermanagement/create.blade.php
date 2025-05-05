@extends('layouts.app')
@section('title', 'Add Driver ')
@section('content')

<div class="container mt-5">
    <h2 class="mb-4">Add New Driver</h2>
    
    <form action="#" method="POST">
        <div class="mb-3">
            <label for="driverName" class="form-label">Driver Name</label>
            <input type="text" class="form-control" id="driverName" name="driverName" placeholder="Enter full name">
        </div>

        <div class="mb-3">
            <label for="licenseNumber" class="form-label">License Number</label>
            <input type="text" class="form-control" id="licenseNumber" name="licenseNumber" placeholder="e.g. ABC123456">
        </div>

        <div class="mb-3">
            <label for="vehicleAssigned" class="form-label">Vehicle Assigned</label>
            <input type="text" class="form-control" id="vehicleAssigned" name="vehicleAssigned" placeholder="e.g. Truck A">
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="startTime" class="form-label">Start Time</label>
            <input type="datetime-local" class="form-control" id="startTime" name="startTime">
        </div>

        <div class="mb-3">
            <label for="endTime" class="form-label">End Time</label>
            <input type="datetime-local" class="form-control" id="endTime" name="endTime">
        </div>

        <button type="submit" class="btn btn-success">Save Driver</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

@endsection