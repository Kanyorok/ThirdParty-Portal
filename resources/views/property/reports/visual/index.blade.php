@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h3 class="fw-bold mb-4">📊 Property Management Analytics Dashboard</h3>

  <!-- Filters -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <label class="form-label">Select Property</label>
      <select class="form-select">
        <option>All Properties</option>
        <option>Sunset Plaza</option>
        <option>Mountain View</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Status</label>
      <select class="form-select">
        <option>All</option>
        <option>Active</option>
        <option>Vacant</option>
        <option>Under Maintenance</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">From</label>
      <input type="date" class="form-control">
    </div>
    <div class="col-md-3">
      <label class="form-label">To</label>
      <input type="date" class="form-control">
    </div>
  </div>

  <!-- Tenancy Charts -->
  <div class="mb-5">
    <h5 class="fw-bold">🏢 Tenancy Reports</h5>
    <div class="row">
      <div class="col-md-6 mb-3">
        <div class="card shadow">
          <div class="card-header bg-light fw-bold">Occupancy vs Vacant Units</div>
          <div class="card-body">
            <canvas id="occupancyChart" height="200"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="card shadow">
          <div class="card-header bg-light fw-bold">Lease Expiry Distribution</div>
          <div class="card-body">
            <canvas id="leaseExpiryChart" height="200"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Rent Collection Charts -->
  <div class="mb-5">
    <h5 class="fw-bold">💰 Rent Collection Reports</h5>
    <div class="row">
      <div class="col-md-6 mb-3">
        <div class="card shadow">
          <div class="card-header bg-light fw-bold">Billing vs Collection Trend</div>
          <div class="card-body">
            <canvas id="billingCollectionChart" height="200"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="card shadow">
          <div class="card-header bg-light fw-bold">Outstanding Rent by Month</div>
          <div class="card-body">
            <canvas id="outstandingChart" height="200"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Maintenance Charts -->
  <div class="mb-5">
    <h5 class="fw-bold">🛠 Maintenance Reports</h5>
    <div class="row">
      <div class="col-md-6 mb-3">
        <div class="card shadow">
          <div class="card-header bg-light fw-bold">Issues by Category</div>
          <div class="card-body">
            <canvas id="issueCategoryChart" height="200"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="card shadow">
          <div class="card-header bg-light fw-bold">Open vs Completed Requests</div>
          <div class="card-body">
            <canvas id="issueStatusChart" height="200"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Financial Charts -->
  <div class="mb-5">
    <h5 class="fw-bold">📦 Deposit & Revenue Reports</h5>
    <div class="row">
      <div class="col-md-6 mb-3">
        <div class="card shadow">
          <div class="card-header bg-light fw-bold">Tenant Deposits by Property</div>
          <div class="card-body">
            <canvas id="depositChart" height="200"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="card shadow">
          <div class="card-header bg-light fw-bold">Revenue vs Expense Trend</div>
          <div class="card-body">
            <canvas id="revenueExpenseChart" height="200"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection