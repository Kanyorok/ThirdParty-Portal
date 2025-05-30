@extends('layouts.app')
@section('title', 'Registered Properties')
@section('content')
<div class="container mt-4">

<a href="{{ route('addproperty.create') }}" class="btn btn-primary mb-3">Add Property</a>

  <h4 class="fw-bold mb-3">📋 Registered Properties</h4>


  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Property Name</th>
        <th>Code</th>
        <th>Type</th>
        <th>Category</th>
        <th>Owner</th>
        <th>Location</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Sunset Plaza</td>
        <td>PROP-001</td>
        <td>Building</td>
        <td>Commercial</td>
        <td>ABC Holdings Ltd.</td>
        <td>Westlands, Nairobi</td>
        <td><span class="badge bg-success">Active</span></td>
        <td>
          <button class="btn btn-sm btn-outline-primary">👁 View</button>
          <button class="btn btn-sm btn-outline-warning">✏️ Edit</button>
        </td>
      </tr>
      <tr>
        <td>2</td>
        <td>Mountain View Estate</td>
        <td>PROP-002</td>
        <td>Land</td>
        <td>Residential</td>
        <td>XYZ Estates</td>
        <td>Ngong Road, Nairobi</td>
        <td><span class="badge bg-secondary">Inactive</span></td>
        <td>
          <button class="btn btn-sm btn-outline-primary">👁 View</button>
          <button class="btn btn-sm btn-outline-warning">✏️ Edit</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
@endsection