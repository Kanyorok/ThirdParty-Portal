@extends('layouts.app')
@section('title', 'renewalmanagement')
@section('content')
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Renewal Management</h4>
    <button class="btn btn-sm btn-success">+ New Renewal</button>
  </div>

  <!-- Expiring Soon -->
  <h5 class="mb-3">Expiring Soon</h5>
  <div class="table-responsive mb-5">
    <table class="table table-bordered table-striped">
      <thead class="table-warning">
        <tr>
          <th>#</th>
          <th>Policy No</th>
          <th>Name</th>
          <th>Type</th>
          <th>Provider</th>
          <th>End Date</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>POL-00876</td>
          <td>Motor Cover</td>
          <td>Vehicle</td>
          <td>Britam</td>
          <td>2025-05-15</td>
          <td><span class="badge bg-warning text-dark">Expiring Soon</span></td>
          <td>
            <button class="btn btn-sm btn-primary">Renew</button>
            <button class="btn btn-sm btn-outline-secondary">Details</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Due for Renewal -->
  <h5 class="mb-3">Due for Renewal</h5>
  <div class="table-responsive mb-5">
    <table class="table table-bordered table-striped">
      <thead class="table-info">
        <tr>
          <th>#</th>
          <th>Policy No</th>
          <th>Name</th>
          <th>Type</th>
          <th>Provider</th>
          <th>End Date</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>2</td>
          <td>POL-00421</td>
          <td>Medical Cover</td>
          <td>Health</td>
          <td>Jubilee</td>
          <td>2025-05-01</td>
          <td><span class="badge bg-info text-dark">Due for Renewal</span></td>
          <td>
            <button class="btn btn-sm btn-primary">Renew</button>
            <button class="btn btn-sm btn-outline-secondary">Details</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Already Expired -->
  <h5 class="mb-3">Already Expired</h5>
  <div class="table-responsive mb-5">
    <table class="table table-bordered table-striped">
      <thead class="table-danger">
        <tr>
          <th>#</th>
          <th>Policy No</th>
          <th>Name</th>
          <th>Type</th>
          <th>Provider</th>
          <th>End Date</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>3</td>
          <td>POL-00219</td>
          <td>Home Insurance</td>
          <td>Property</td>
          <td>UAP Old Mutual</td>
          <td>2025-04-20</td>
          <td><span class="badge bg-danger">Expired</span></td>
          <td>
            <button class="btn btn-sm btn-primary">Renew</button>
            <button class="btn btn-sm btn-outline-secondary">Details</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
@endsection