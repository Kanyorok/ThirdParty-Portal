@extends('layouts.app')
@section('title', 'fillingtracker')
@section('content')
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📁 Filing Tracker</h3>
    <a href="{{ route('fillingtracker.create') }}" class="btn btn-primary">+ Add New Filing</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Report Title</th>
          <th>Authority</th>
          <th>Submission Date</th>
          <th>Reference No.</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sample static row -->
        <tr>
          <td>1</td>
          <td>Annual Tax Return</td>
          <td>KRA</td>
          <td>2025-04-15</td>
          <td>KRA-2025-001</td>
          <td><span class="badge bg-success">Acknowledged</span></td>
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