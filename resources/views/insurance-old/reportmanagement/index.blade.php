@extends('layouts.app')
@section('title', 'reportmanagement')
@section('content')
<div class="container mt-4">
  <a href="/dashboard" class="btn btn-sm btn-outline-primary mb-3">← Back to Dashboard</a>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Insurance Reports</h4>
    <a href="{{ route('reportmanagement.create') }}" class="btn btn-sm btn-success">+ Generate Report</a>
  </div>

  <!-- All Active Policies -->
  <h5 class="mb-3">All Active Policies</h5>
  <div class="table-responsive mb-4">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Policy No</th>
          <th>Name</th>
          <th>Type</th>
          <th>Provider</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>POL-10001</td>
          <td>Vehicle Cover</td>
          <td>Motor</td>
          <td>Jubilee</td>
          <td><span class="badge bg-success">Active</span></td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Policy per Asset -->
  <h5 class="mb-3">Policy per Asset</h5>
  <div class="table-responsive mb-4">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Asset</th>
          <th>Policy No</th>
          <th>Type</th>
          <th>Provider</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>Office Building</td>
          <td>POL-20002</td>
          <td>Property</td>
          <td>Britam</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Claims Summary -->
  <h5 class="mb-3">Claims Summary (by Status/Type)</h5>
  <div class="table-responsive mb-4">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Claim Type</th>
          <th>Status</th>
          <th>Total Claims</th>
          <th>Total Amount</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>Motor</td>
          <td>Approved</td>
          <td>3</td>
          <td>KES 120,000</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Upcoming Premiums -->
  <h5 class="mb-3">Upcoming Premiums</h5>
  <div class="table-responsive mb-4">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Policy No</th>
          <th>Type</th>
          <th>Provider</th>
          <th>Due Date</th>
          <th>Amount</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>POL-30003</td>
          <td>Health</td>
          <td>APA</td>
          <td>2025-06-15</td>
          <td>KES 18,500</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Expired Policies -->
  <h5 class="mb-3">Expired Policies</h5>
  <div class="table-responsive mb-4">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Policy No</th>
          <th>Name</th>
          <th>Type</th>
          <th>Provider</th>
          <th>Expired On</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>POL-40004</td>
          <td>Equipment Cover</td>
          <td>Asset</td>
          <td>UAP</td>
          <td>2025-03-30</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
@endsection