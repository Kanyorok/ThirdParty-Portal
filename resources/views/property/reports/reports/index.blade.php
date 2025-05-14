@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h3 class="fw-bold mb-4">📊 Property Management Reports Dashboard</h3>

  <!-- Tenancy Section -->
  <div class="mb-4">
    <h5 class="fw-bold">🏢 Tenancy Reports</h5>
    <div class="row g-3 mb-2">
      <div class="col-md-3">
        <div class="card bg-success text-white shadow">
          <div class="card-body">
            <h6>Occupancy Rate</h6>
            <h4>92%</h4>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-info text-white shadow">
          <div class="card-body">
            <h6>Vacant Units</h6>
            <h4>14</h4>
          </div>
        </div>
      </div>
    </div>
    <div class="card shadow-sm">
      <div class="card-header bg-light fw-bold">📄 Available Reports</div>
      <ul class="list-group list-group-flush">
        <li class="list-group-item">✔ Lease Expiry Timeline Report</li>
        <li class="list-group-item">✔ Unit Occupancy by Block / Floor</li>
        <li class="list-group-item">✔ Active vs Expired Lease Summary</li>
        <li class="list-group-item">✔ Tenants Without Leases</li>
      </ul>
    </div>
  </div>

  <!-- Rent Collection Section -->
  <div class="mb-4">
    <h5 class="fw-bold">💰 Rent Collection Reports</h5>
    <div class="row g-3 mb-2">
      <div class="col-md-3">
        <div class="card bg-primary text-white shadow">
          <div class="card-body">
            <h6>Total Billed This Month</h6>
            <h4>KES 1,540,000</h4>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-warning text-dark shadow">
          <div class="card-body">
            <h6>Outstanding Rent</h6>
            <h4>KES 310,000</h4>
          </div>
        </div>
      </div>
    </div>
    <div class="card shadow-sm">
      <div class="card-header bg-light fw-bold">📄 Available Reports</div>
      <ul class="list-group list-group-flush">
        <li class="list-group-item">✔ Rent Collection Summary by Property</li>
        <li class="list-group-item">✔ Monthly Rent Billing vs Collection Trend</li>
        <li class="list-group-item">✔ Tenant Ledger Outstanding Report</li>
        <li class="list-group-item">✔ Defaulters / Overdue Payment Report</li>
      </ul>
    </div>
  </div>

  <!-- Maintenance Reports Section -->
  <div class="mb-4">
    <h5 class="fw-bold">🛠 Maintenance Reports</h5>
    <div class="row g-3 mb-2">
      <div class="col-md-3">
        <div class="card bg-danger text-white shadow">
          <div class="card-body">
            <h6>Open Issues</h6>
            <h4>6</h4>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-success text-white shadow">
          <div class="card-body">
            <h6>Completed This Month</h6>
            <h4>19</h4>
          </div>
        </div>
      </div>
    </div>
    <div class="card shadow-sm">
      <div class="card-header bg-light fw-bold">📄 Available Reports</div>
      <ul class="list-group list-group-flush">
        <li class="list-group-item">✔ Maintenance Summary by Property</li>
        <li class="list-group-item">✔ Issues by Category (Plumbing, Electrical, etc.)</li>
        <li class="list-group-item">✔ Average Resolution Time</li>
        <li class="list-group-item">✔ Vendor Performance Tracker</li>
        <li class="list-group-item">✔ Cost of Maintenance by Month</li>
      </ul>
    </div>
  </div>

  <!-- Financial & Deposit Reports -->
  <div class="mb-4">
    <h5 class="fw-bold">📦 Financial & Deposit Reports</h5>
    <div class="row g-3 mb-2">
      <div class="col-md-3">
        <div class="card bg-secondary text-white shadow">
          <div class="card-body">
            <h6>Tenant Deposits Held</h6>
            <h4>KES 2,300,000</h4>
          </div>
        </div>
      </div>
    </div>
    <div class="card shadow-sm">
      <div class="card-header bg-light fw-bold">📄 Available Reports</div>
      <ul class="list-group list-group-flush">
        <li class="list-group-item">✔ Deposit Register</li>
        <li class="list-group-item">✔ Refund / Clearance Summary</li>
        <li class="list-group-item">✔ Property Revenue vs Expenses</li>
        <li class="list-group-item">✔ Lease Profitability Overview</li>
      </ul>
    </div>
  </div>
</div>

@endsection