@extends('layouts.app')
@section('title', 'obligationtracker')
@section('content')
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📋 Obligation Tracker</h3>
    <a href="{{ route('obligationtracker.create') }}" class="btn btn-primary">+ Add New Obligation</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Contract Name</th>
          <th>Obligation</th>
          <th>Responsible Party</th>
          <th>Due Date</th>
          <th>Status</th>
          <th>Penalty</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sample static row -->
        <tr>
          <td>1</td>
          <td>Service Agreement A</td>
          <td>Deliver monthly performance report</td>
          <td>John Doe</td>
          <td>2025-06-15</td>
          <td><span class="badge bg-warning text-dark">Pending</span></td>
          <td>$500</td>
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