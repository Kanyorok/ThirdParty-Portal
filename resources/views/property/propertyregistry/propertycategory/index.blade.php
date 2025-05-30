@extends('layouts.app')
@section('title', 'Property Categories')
@section('content')
<div class="container mt-4">

<a href="{{ route('propertycategory.create') }}" class="btn btn-primary mb-3">Add Category</a>

  <h4 class="fw-bold mb-3">📋 Property Categories</h4>

  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Category Name</th>
        <th>Description</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Residential</td>
        <td>Houses, apartments, and dwellings for individuals/families</td>
        <td><span class="badge bg-success">Active</span></td>
        <td><button class="btn btn-sm btn-outline-warning">✏️ Edit</button></td>
      </tr>
      <tr>
        <td>2</td>
        <td>Commercial</td>
        <td>Offices, retail shops, malls</td>
        <td><span class="badge bg-success">Active</span></td>
        <td><button class="btn btn-sm btn-outline-warning">✏️ Edit</button></td>
      </tr>
      <tr>
        <td>3</td>
        <td>Agricultural</td>
        <td>Farms, ranches, and agricultural land</td>
        <td><span class="badge bg-secondary">Inactive</span></td>
        <td><button class="btn btn-sm btn-outline-warning">✏️ Edit</button></td>
      </tr>
    </tbody>
  </table>
</div>
@endsection