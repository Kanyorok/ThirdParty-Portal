@extends('layouts.app')
@section('title', 'Lease Schedule')
@section('content')
<table class="table table-bordered table-striped mt-4">
  <thead class="table-light">
    <tr>
      <th>#</th>
      <th>Billing Period</th>
      <th>Due Date</th>
      <th>Rent</th>
      <th>Service</th>
      <th>Parking</th>
      <th>Total Due</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>1</td>
      <td>May 2025</td>
      <td>2025-05-05</td>
      <td>25,000</td>
      <td>1,500</td>
      <td>2,000</td>
      <td>28,500</td>
    </tr>
    <!-- Repeat for each period -->
  </tbody>
</table>
<button class="btn btn-success">🧾 Print Schedule</button>
@endsection