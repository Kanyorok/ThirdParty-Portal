@extends('layouts.app')
@section('title', 'contractapproval')
@section('content')
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📊 Contract Approval Workflow</h3>
    <a href="{{ route('contractapproval.create') }}" class="btn btn-primary">+ Add New Contract</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Contract</th>
          <th>Initiated By</th>
          <th>Status</th>
          <th>Legal</th>
          <th>Compliance</th>
          <th>Management</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sample Static Row -->
        <tr>
          <td>1</td>
          <td>NDA - Vendor Agreement</td>
          <td>John Doe</td>
          <td><span class="badge bg-warning text-dark">In Progress</span></td>
          <td><span class="badge bg-success">Approved</span></td>
          <td><span class="badge bg-secondary">Pending</span></td>
          <td><span class="badge bg-secondary">Pending</span></td>
          <td>
            <button class="btn btn-sm btn-info">View</button>
            <button class="btn btn-sm btn-danger">Delete</button>
            <button class="btn btn-sm btn-success">Approve</button>
            
          </td>
        </tr>
        <!-- Add dynamic PHP rows here -->
      </tbody>
    </table>
  </div>
</div>
@endsection