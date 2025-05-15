@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>📋 Procurement Plan Scheduling Summary</h4>
    <a href="/planning" class="btn btn-sm btn-outline-secondary">← Back to Plan</a>
  </div>

  <!-- Plan Info -->
  <div class="mb-4 p-3 bg-light rounded border">
    <p><strong>Plan:</strong> Annual Procurement Plan - 2025</p>
    <p><strong>Status:</strong> DRAFT</p>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered align-middle table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Item</th>
          <th>Total Qty</th>
          <th>Scheduled Qty</th>
          <th>Schedule Type</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sample Row -->
        <tr>
          <td>1</td>
          <td>Desktop Computers</td>
          <td>100</td>
          <td>100</td>
          <td>Quarterly</td>
          <td><span class="badge bg-success">Fully Scheduled</span></td>
          <td>
            <a href="{{ route('procurementplanquaterly.create') }}" class="btn btn-sm btn-outline-primary">Edit</a>
          </td>
        </tr>
        <tr>
          <td>2</td>
          <td>Filing Cabinets</td>
          <td>60</td>
          <td>30</td>
          <td>Monthly</td>
          <td><span class="badge bg-warning text-dark">Partially Scheduled</span></td>
          <td>
            <a href="/planning/schedule-item/502/edit" class="btn btn-sm btn-outline-primary">Edit</a>
          </td>
        </tr>
        <tr>
          <td>3</td>
          <td>Projector Screens</td>
          <td>12</td>
          <td>0</td>
          <td>—</td>
          <td><span class="badge bg-danger">Not Scheduled</span></td>
          <td>
            <a href="/planning/schedule-item/503/edit" class="btn btn-sm btn-outline-primary">Schedule</a>
          </td>
        </tr>
        <!-- More items -->
      </tbody>
    </table>
  </div>
</div>


@endsection
