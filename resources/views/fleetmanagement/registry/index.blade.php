@extends('layouts.app')
@section('title', 'Trip Management')
@section('content')
<div class="container mt-5">
<h2>Registered Vehicles</h2>
<a href="{{route ('vehicle-registry.create')}}" class="btn btn-primary mb-3">Add New Vehicle</a>
 
    <table class="table table-bordered table-striped">
<thead class="thead-dark">
<tr>
<th>#</th>
<th>Registration Number</th>
<th>Make</th>
<th>Model</th>
<th>Year</th>
<th>Type</th>
<th>Color</th>
<th>Chassis No.</th>
<th>Engine No.</th>
</tr>
</thead>
<tbody>
<tr>
<td>1</td>
<td>KDA 123A</td>
<td>Toyota</td>
<td>Hilux</td>
<td>2021</td>
<td>Pickup</td>
<td>White</td>
<td>JT121A9876X001234</td>
<td>2KD-FTV123456</td>
</tr>
<tr>
<td>2</td>
<td>KCF 456B</td>
<td>Isuzu</td>
<td>NQR</td>
<td>2019</td>
<td>Truck</td>
<td>Blue</td>
<td>ISU9X001789</td>
<td>4HG1-FTV458964</td>
</tr>
<tr>
<td>3</td>
<td>KDH 789C</td>
<td>Nissan</td>
<td>Caravan</td>
<td>2020</td>
<td>Van</td>
<td>Silver</td>
<td>NSN1C7890K1234</td>
<td>ZD30-123999A</td>
</tr>
</tbody>
</table>
</div>
@endsection