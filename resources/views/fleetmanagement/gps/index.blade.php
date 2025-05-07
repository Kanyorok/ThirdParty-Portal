@extends('layouts.app')
@section('title', 'GPS & Telematics')
@section('content')
<div class="container mt-5">
    <h2>Telematics & GPS Integration</h2>
    <a href="{{ route('gps.create') }}" class="btn btn-primary mb-3">Add Telematics Device</a>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Vehicle</th>
                <th>Device ID</th>
                <th>Install Date</th>
                <th>Provider</th>
                <th>Subscription Status</th>
                <th>Renewal Date</th>
                <th>Notes</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Toyota Hilux - KDA 123A</td>
                <td>GPS987654321</td>
                <td>2025-04-01</td>
                <td>TrackMe Ltd.</td>
                <td>Active</td>
                <td>2026-04-01</td>
                <td>Main tracker installed front panel</td>
                <td>
                    <form action="view_telematics.php" method="get">
                        <input type="hidden" name="device" value="GPS987654321">
                        <button type="submit" class="btn btn-sm btn-secondary">View</button>
                    </form>
                </td>
            </tr>
            <tr>
                <td>2</td>
                <td>Isuzu NQR - KCF 987B</td>
                <td>GPS123456789</td>
                <td>2025-03-10</td>
                <td>FleetWatch</td>
                <td>Expired</td>
                <td>2026-03-10</td>
                <td>Renewal pending for next quarter</td>
                <td>
                    <form action="view_telematics.php" method="get">
                        <input type="hidden" name="device" value="GPS123456789">
                        <button type="submit" class="btn btn-sm btn-secondary">View</button>
                    </form>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection