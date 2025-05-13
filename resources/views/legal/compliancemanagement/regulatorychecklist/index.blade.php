@extends('layouts.app')
@section('title', 'regulatorychecklist')
@section('content')
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📋 Regulatory Checklist</h3>
    <a href="{{ route('regulatorychecklist.create') }}" class="btn btn-primary">+ Add New Regulation</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Regulation Name</th>
          <th>Sector</th>
          <th>Description</th>
          <th>Compliance Status</th>
          <th>Last Reviewed</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sample static row -->
        <tr>
          <td>1</td>
          <td>OSHA Safety Standards</td>
          <td>Manufacturing</td>
          <td>Occupational safety and health standards for workers.</td>
          <td><span class="badge bg-success">Compliant</span></td>
          <td>2025-04-01</td>
          <td>
            <button class="btn btn-sm btn-info">View</button>
            <button class="btn btn-sm btn-secondary">Edit</button>
            <button class="btn btn-sm btn-danger">Delete</button>
          </td>
        </tr>
        <!-- Add dynamic PHP rows here -->
      </tbody>
    </table>
  </div>
</div>
@endsection