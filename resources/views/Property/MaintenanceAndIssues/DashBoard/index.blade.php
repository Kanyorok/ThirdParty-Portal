@extends('layouts.app')
@section('title', 'Maintenance Dashboard')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📈 Maintenance Dashboard</h4>

  <!-- Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card text-white bg-primary shadow">
        <div class="card-body">
          <h6>Total Requests</h6>
          <h3>42</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-white bg-info shadow">
        <div class="card-body">
          <h6>Open</h6>
          <h3>12</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-white bg-warning shadow">
        <div class="card-body">
          <h6>In Progress</h6>
          <h3>18</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-white bg-success shadow">
        <div class="card-body">
          <h6>Completed</h6>
          <h3>12</h3>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters -->
  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <select class="form-select">
        <option selected>All Properties</option>
        <option>Sunset Plaza</option>
        <option>Mountain View</option>
      </select>
    </div>
    <div class="col-md-3">
      <select class="form-select">
        <option selected>All Priorities</option>
        <option>Low</option>
        <option>Medium</option>
        <option>High</option>
        <option>Emergency</option>
      </select>
    </div>
    <div class="col-md-3">
      <select class="form-select">
        <option selected>All Statuses</option>
        <option>Open</option>
        <option>In Progress</option>
        <option>Completed</option>
      </select>
    </div>
    <div class="col-md-3">
      <button class="btn btn-outline-primary w-100">🔍 Refresh</button>
    </div>
  </div>

  <!-- Table View -->
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Request</th>
        <th>Unit</th>
        <th>Type</th>
        <th>Priority</th>
        <th>Status</th>
        <th>Assigned To</th>
        <th>Date Reported</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>REQ-2025-001</td>
        <td>Unit 101</td>
        <td>Plumbing</td>
        <td><span class="badge bg-danger">High</span></td>
        <td><span class="badge bg-warning text-dark">In Progress</span></td>
        <td>Mary N.</td>
        <td>2025-05-03</td>
        <td><button class="btn btn-sm btn-outline-primary">👁</button></td>
      </tr>
    </tbody>
  </table>
</div>
@endsection