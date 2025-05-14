@extends('layouts.app')
@section('title', 'noncomplianceregister')
@section('content')
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📋 Non-Compliance Register</h3>
    <a href="{{ route('noncomplianceregister.create') }}" class="btn btn-primary">+ Log New Incident</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Violation Type</th>
          <th>Date Reported</th>
          <th>Description</th>
          <th>Penalty</th>
          <th>Remediation Action</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sample static row -->
        <tr>
          <td>1</td>
          <td>Data Breach</td>
          <td>2025-05-10</td>
          <td>Unauthorized access to client data.</td>
          <td>KES 100,000 fine</td>
          <td>Implemented two-factor authentication.</td>
          <td><span class="badge bg-warning text-dark">In Progress</span></td>
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