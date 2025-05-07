@extends('layouts.app')
@section('title', 'Reports Dashboard')
@section('content')
<div class="container mt-5">
    <h2>Dashboard & Reporting</h2>
    <a href="{{ route('reports.create') }}" class="btn btn-primary mb-3">Add Report Configuration</a>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Report Name</th>
                <th>Type</th>
                <th>Frequency</th>
                <th>Delivery</th>
                <th>Recipients</th>
                <th>Metrics</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Weekly Fuel Report</td>
                <td>Fuel Consumption</td>
                <td>Weekly</td>
                <td>Email</td>
                <td>fleetmanager@example.com</td>
                <td>Litres Used, Cost</td>
                <td>Send every Monday morning</td>
            </tr>
            <tr>
                <td>2</td>
                <td>Monthly Utilization</td>
                <td>Fleet Utilization</td>
                <td>Monthly</td>
                <td>PDF</td>
                <td>opslead@example.com</td>
                <td>Trips, Engine Hours, Distance</td>
                <td>Include charts</td>
            </tr>
            <tr>
                <td>3</td>
                <td>Maintenance Overview</td>
                <td>Maintenance Logs</td>
                <td>Monthly</td>
                <td>Portal</td>
                <td>techsupervisor@example.com</td>
                <td>Service Dates, Downtime</td>
                <td>Sync with workshop data</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection