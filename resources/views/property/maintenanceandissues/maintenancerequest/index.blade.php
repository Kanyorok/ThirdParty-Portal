@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">

<a href="{{ route('maintenancerequest.create') }}" class="btn btn-primary mb-3">New Request</a>
  <h4 class="fw-bold mb-3">📋 Maintenance Requests</h4>

  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Property</th>
        <th>Unit</th>
        <th>Issue Type</th>
        <th>Reported By</th>
        <th>Priority</th>
        <th>Status</th>
        <th>Date Reported</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Sunset Plaza</td>
        <td>Unit 101</td>
        <td>Plumbing</td>
        <td>Moses K.</td>
        <td><span class="badge bg-warning text-dark">High</span></td>
        <td><span class="badge bg-info">Open</span></td>
        <td>2025-05-03</td>
        <td>
        <a href="{{ route('assignrequest.create') }}" class="btn btn-sm btn-outline-primary">👁 View</a>  
          <button class="btn btn-sm btn-outline-success">🛠 Assign</button>
        </td>
      </tr>
      <tr>
        <td>2</td>
        <td>Mountain View Estate</td>
        <td>Unit 204</td>
        <td>Electrical</td>
        <td>Jane W.</td>
        <td><span class="badge bg-danger">Emergency</span></td>
        <td><span class="badge bg-secondary">In Progress</span></td>
        <td>2025-05-02</td>
        <td>
          <button class="btn btn-sm btn-outline-primary">👁 View</button>
          <button class="btn btn-sm btn-outline-secondary">✔ Complete</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>

@endsection