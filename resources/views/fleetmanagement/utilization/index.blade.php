@extends('layouts.app')
@section('title', 'Utilization & Costing')
@section('content')
<div class="container mt-5">
    <h2>Fleet Utilization & Costing</h2>
    <a href="{{ route('fuelmanagement.create') }}" class="btn btn-primary mb-3">Add Utilization Entry</a>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Vehicle</th>
                <th>Driver</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Distance (km)</th>
                <th>Fuel Cost ($)</th>
                <th>Maintenance ($)</th>
                <th>Total Cost ($)</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Toyota Hilux - KDA 123A</td>
                <td>John Doe</td>
                <td>2025-05-01</td>
                <td>2025-05-03</td>
                <td>540</td>
                <td>185.00</td>
                <td>70.00</td>
                <td>255.00</td>
                <td>Trip to Mombasa</td>
            </tr>
            <tr>
                <td>2</td>
                <td>Isuzu NQR - KCF 987B</td>
                <td>Jane Mwangi</td>
                <td>2025-05-02</td>
                <td>2025-05-02</td>
                <td>120</td>
                <td>182.00</td>
                <td>45.00</td>
                <td>227.00</td>
                <td>Daily delivery run</td>
            </tr>
            <tr>
                <td>3</td>
                <td>Nissan NV200 - KBV 456C</td>
                <td>Peter Otieno</td>
                <td>2025-05-03</td>
                <td>2025-05-03</td>
                <td>200</td>
                <td>180.00</td>
                <td>65.00</td>
                <td>245.00</td>
                <td>Resupply trip</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection