@extends('layouts.app')
@section('title', 'renewals')
@section('content')
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📋 Contract Expiry Overview</h3>
    <a href="{{ route('renewals.create') }}" class="btn btn-primary">+ Add New Contract</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Title</th>
          <th>Type</th>
          <th>Parties</th>
          <th>Start Date</th>
          <th>Expiry Date</th>
          <th>Auto-Renewal</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sample static row -->
        <tr>
          <td>1</td>
          <td>Vendor NDA</td>
          <td>NDA</td>
          <td>Company A, Vendor B</td>
          <td>2024-06-15</td>
          <td>2025-06-15</td>
          <td>Yes</td>
          <td><span class="badge bg-warning text-dark">Expiring Soon</span></td>
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