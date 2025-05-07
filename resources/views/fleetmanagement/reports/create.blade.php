@extends('layouts.app')
@section('title', 'Reports & Dashboard')
@section('content')
<div class="container mt-5">
    <h2>Dashboard & Reporting Setup</h2>
    <form action="submit_dashboard.php" method="post">
        <div class="form-group mb-3">
            <label for="report_name">Report Name</label>
            <input type="text" class="form-control" id="report_name" name="report_name" placeholder="e.g., Weekly Fleet Summary">
        </div>

        <div class="form-group mb-3">
            <label for="report_type">Report Type</label>
            <select class="form-control" id="report_type" name="report_type">
                <option value="utilization">Fleet Utilization</option>
                <option value="fuel">Fuel Consumption</option>
                <option value="maintenance">Maintenance Logs</option>
                <option value="gps">GPS Tracking</option>
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="frequency">Reporting Frequency</label>
            <select class="form-control" id="frequency" name="frequency">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="metrics">Metrics to Include</label>
            <textarea class="form-control" id="metrics" name="metrics" rows="2" placeholder="e.g., Distance, Fuel Cost, Downtime"></textarea>
        </div>

        <div class="form-group mb-3">
            <label for="delivery_method">Delivery Method</label>
            <select class="form-control" id="delivery_method" name="delivery_method">
                <option value="email">Email</option>
                <option value="portal">Web Portal</option>
                <option value="pdf">PDF Download</option>
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="recipients">Recipients (Email addresses)</label>
            <input type="text" class="form-control" id="recipients" name="recipients" placeholder="e.g., manager@example.com, ops@example.com">
        </div>

        <div class="form-group mb-3">
            <label for="notes">Additional Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Optional instructions or remarks..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Save Dashboard Settings</button>
    </form>
</div>

@endsection