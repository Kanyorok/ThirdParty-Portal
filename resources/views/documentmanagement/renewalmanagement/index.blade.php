@extends('layouts.app')
@section('title', 'renewalmanagement')
@section('content')
<div class="container mt-5">
  <h4 class="mb-4">📆 Expiry Dashboard</h4>

  <div class="mb-4">
    <a href="{{ route('renewalmanagement.create') }}"" class="btn btn-primary">⏰ Add Document with Expiry</a>
  </div>

  <!-- Upcoming Expiries -->
  <div class="card mb-4">
    <div class="card-header bg-warning text-dark">
      🔔 Upcoming Expiries (Next 30 Days)
    </div>
    <div class="card-body p-0">
      <table class="table table-bordered table-sm mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Title</th>
            <th>Type</th>
            <th>Expiry Date</th>
            <th>Renewal Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>Business License</td>
            <td>License</td>
            <td>2025-06-01</td>
            <td><span class="badge bg-warning text-dark">Pending</span></td>
          </tr>
          <!-- Add more from PHP backend -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Expired Documents -->
  <div class="card">
    <div class="card-header bg-danger text-white">
      ❌ Expired Documents
    </div>
    <div class="card-body p-0">
      <table class="table table-bordered table-sm mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Title</th>
            <th>Type</th>
            <th>Expiry Date</th>
            <th>Renewal Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>2</td>
            <td>Vehicle Insurance</td>
            <td>Insurance</td>
            <td>2025-04-15</td>
            <td><span class="badge bg-danger">Expired</span></td>
          </tr>
          <!-- Add more from PHP backend -->
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection