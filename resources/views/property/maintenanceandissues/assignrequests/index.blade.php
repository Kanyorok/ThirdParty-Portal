@extends('layouts.app')
@section('title', 'Maintenance Assignments')
@section('content')
<div class="container mt-4">

<a href="{{ route('assignrequest.create') }}" class="btn btn-primary mb-3">Assign Task</a>
  <h4 class="fw-bold mb-3">📋 Maintenance Assignments</h4>

  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Request</th>
        <th>Assigned To</th>
        <th>Type</th>
        <th>Start Date</th>
        <th>Due Date</th>
        <th>Status</th>
        <th>Priority</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <!-- Assignment to Internal Staff -->
      <tr>
        <td>1</td>
        <td>REQ-2025-001 – Plumbing – Unit 101</td>
        <td>Mary N. (In-House)</td>
        <td>Internal</td>
        <td>2025-05-03</td>
        <td>2025-05-04</td>
        <td><span class="badge bg-warning text-dark">In Progress</span></td>
        <td><span class="badge bg-danger">High</span></td>
        <td>
          <button class="btn btn-sm btn-outline-primary">👁 View</button>
          <button class="btn btn-sm btn-outline-success">✔ Complete</button>
        </td>
      </tr>

      <!-- Assignment to Vendor -->
      <tr>
        <td>2</td>
        <td>REQ-2025-002 – Electrical – Unit 204</td>
        <td>BrightElectric Co.</td>
        <td>Vendor</td>
        <td>2025-05-02</td>
        <td>2025-05-03</td>
        <td><span class="badge bg-info">Assigned</span></td>
        <td><span class="badge bg-warning text-dark">Medium</span></td>
        <td>
          <button class="btn btn-sm btn-outline-primary">👁 View</button>
          <button class="btn btn-sm btn-outline-success">✔ Complete</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
@endsection