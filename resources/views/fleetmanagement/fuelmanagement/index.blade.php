@extends('layouts.app')
@section('title', 'Fuel Management')
@section('content')
<div class="container mt-5">
    <h2>Fuel Management</h2>
    <a href="{{ route('fuelmanagement.create') }}" class="btn btn-primary mb-3">Add Fuel Entry</a>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Vehicle</th>
                <th>Driver</th>
                <th>Date</th>
                <th>Fuel Type</th>
                <th>Station</th>
                <th>Quantity (L)</th>
                <th>Unit Price</th>
                <th>Total Cost</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Toyota Hilux - KDA 123A</td>
                <td>John Doe</td>
                <td>2025-05-01</td>
                <td>Diesel</td>
                <td>Total - Westlands</td>
                <td>40</td>
                <td>185</td>
                <td>7,400</td>
                <td>Trip to Mombasa</td>
            </tr>
            <tr>
                <td>2</td>
                <td>Isuzu NQR - KCF 987B</td>
                <td>Jane Mwangi</td>
                <td>2025-05-02</td>
                <td>Petrol</td>
                <td>Shell - Industrial Area</td>
                <td>30</td>
                <td>182</td>
                <td>5,460</td>
                <td>Daily delivery run</td>
            </tr>
            <tr>
                <td>3</td>
                <td>Nissan NV200 - KBV 456C</td>
                <td>Peter Otieno</td>
                <td>2025-05-03</td>
                <td>Diesel</td>
                <td>Rubis - Nakuru</td>
                <td>35</td>
                <td>180</td>
                <td>6,300</td>
                <td>Resupply trip</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection