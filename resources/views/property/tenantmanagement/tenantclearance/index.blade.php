@extends('layouts.app')
@section('title', 'Tenant Exit')
@section('content')
<div class="container mt-4">
<a href="{{ route('tenantclearance.create') }}" class="btn btn-primary mb-3">New Clearance</a>
  <h4 class="fw-bold mb-3">📋 Tenant Exit & Clearance Records</h4>

  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Tenant</th>
        <th>Lease No</th>
        <th>Unit</th>
        <th>Exit Date</th>
        <th>Keys Returned</th>
        <th>Dues Cleared</th>
        <th>Deposit Status</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <!-- Sample Record: Fully Cleared -->
      <tr>
        <td>1</td>
        <td>Moses K.</td>
        <td>#L-2025-001</td>
        <td>Unit 101 - Sunset Plaza</td>
        <td>2025-08-31</td>
        <td>Yes</td>
        <td>Yes</td>
        <td>Fully Refunded</td>
        <td><span class="badge bg-success">Cleared</span></td>
        <td>
          <button class="btn btn-sm btn-outline-secondary">📄 View</button>
        </td>
      </tr>

      <!-- Sample Record: Pending Dues -->
      <tr>
        <td>2</td>
        <td>Jane W.</td>
        <td>#L-2025-004</td>
        <td>Unit 203 - Green Court</td>
        <td>2025-05-15</td>
        <td>No</td>
        <td>No</td>
        <td>Not Refunded</td>
        <td><span class="badge bg-danger">Pending</span></td>
        <td>
          <button class="btn btn-sm btn-outline-warning">✏️ Complete</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
@endsection
