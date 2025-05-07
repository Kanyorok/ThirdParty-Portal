@extends('layouts.app')
@section('title', 'Fuel Management')
@section('content')
<div class="container mt-5">
    <h2>Vehicle Service Records</h2>
    <a href="{{ route('servicetracking.create') }}" class="btn btn-primary mb-3">Add Service Entry</a>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Vehicle</th>
                <th>Service Date</th>
                <th>Next Service</th>
                <th>Service Type</th>
                <th>Provider</th>
                <th>Cost (Ksh)</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Toyota Hilux - KDA 123A</td>
                <td>2025-05-05</td>
                <td>2025-08-05</td>
                <td>Routine Maintenance</td>
                <td>AutoX Garage - Nairobi</td>
                <td>12,500</td>
                <td>Changed oil, filters, and brake pads</td>
            </tr>
            <tr>
                <td>2</td>
                <td>Isuzu NQR - KCF 987B</td>
                <td>2025-04-20</td>
                <td>2025-07-20</td>
                <td>Brake Service</td>
                <td>Mega AutoTech - Nakuru</td>
                <td>8,000</td>
                <td>Replaced front brake system</td>
            </tr>
            <tr>
                <td>3</td>
                <td>Nissan Caravan - KDH 234C</td>
                <td>2025-03-18</td>
                <td>2025-06-18</td>
                <td>Tyre Replacement</td>
                <td>Tyre World - Eldoret</td>
                <td>22,000</td>
                <td>4 new tyres installed</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection