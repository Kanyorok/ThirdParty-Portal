@extends('layouts.app')
@section('title', 'compliancecalendar')
@section('content')
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📅 Compliance Calendar</h3>
    <a href="{{ route('compliancecalendar.create') }}" class="btn btn-primary">+ Add New Deadline</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Title</th>
          <th>Type</th>
          <th>Due Date</th>
          <th>Responsible Person</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sample static row -->
        <tr>
          <td>1</td>
          <td>Annual Tax Return</td>
          <td>Return Filing</td>
          <td>2025-06-30</td>
          <td>John Doe</td>
          <td><span class="badge bg-warning text-dark">Pending</span></td>
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