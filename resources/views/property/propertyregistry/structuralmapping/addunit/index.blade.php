@extends('layouts.app')
@section('title', 'Property Units')
@section('content')
<div class="container mt-4">

<a href="{{ route('addunit.create') }}" class="btn btn-primary mb-3">Add Unit</a>

  <h4 class="fw-bold mb-3">📋 Property Units</h4>

  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Unit Code</th>
        <th>Floor</th>
        <th>Block</th>
        <th>Property</th>
        <th>Size (sq.ft)</th>
        <th>Status</th>
        <th>Rentable?</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Unit 101</td>
        <td>1st Floor</td>
        <td>Block A</td>
        <td>Sunset Plaza</td>
        <td>1200</td>
        <td><span class="badge bg-success">Vacant</span></td>
        <td>Yes</td>
        <td>
          <button class="btn btn-sm btn-outline-primary">👁 View</button>
          <button class="btn btn-sm btn-outline-warning">✏️ Edit</button>
        </td>
      </tr>
      <tr>
        <td>2</td>
        <td>Unit B204</td>
        <td>2nd Floor</td>
        <td>Tower 1</td>
        <td>Mountain View Estate</td>
        <td>900</td>
        <td><span class="badge bg-danger">Occupied</span></td>
        <td>Yes</td>
        <td>
          <button class="btn btn-sm btn-outline-primary">👁 View</button>
          <button class="btn btn-sm btn-outline-warning">✏️ Edit</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
@endsection