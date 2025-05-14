@extends('layouts.app')
@section('title', 'Consolidated Procurement Plans')
@section('content')

        <div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>📄 Consolidated Procurement Plans</h4>
    <a href="{{ route('procurementplanmaintain.create') }}" class="btn btn-success btn-sm">+ New Plan</a>
  </div>

  <!-- Optional: Filter Controls -->
  <div class="row mb-3">
    <div class="col-md-4">
      <select class="form-select">
        <option selected>All Years</option>
        <option>2025</option>
        <option>2026</option>
      </select>
    </div>
    <div class="col-md-4">
      <select class="form-select">
        <option selected>All Statuses</option>
        <option>Draft</option>
        <option>Pending Approval</option>
        <option>Approved</option>
      </select>
    </div>
    <div class="col-md-4">
      <button class="btn btn-outline-primary w-100">Apply Filters</button>
    </div>
  </div>

  <!-- Table -->
  <div class="table-responsive">
    <table class="table table-striped table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Plan Ref No</th>
          <th>Title</th>
          <th>Year</th>
          <th>Items</th>
          <th>Estimated Cost (KES)</th>
          <th>Status</th>
          <th>Created By</th>
          <th>Created On</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sample Row -->
        <tr>
          <td>1</td>
          <td>PLAN/2025/001</td>
          <td>Annual Procurement Plan - 2025</td>
          <td>2025</td>
          <td>18</td>
          <td>12,800,000</td>
          <td><span class="badge bg-warning">Pending Approval</span></td>
          <td>Procurement Admin</td>
          <td>2025-01-10</td>
          <td>
            <a href="#" class="btn btn-sm btn-outline-primary">View</a>
            <a href="#" class="btn btn-sm btn-outline-success">Edit</a>
          </td>
        </tr>
        <tr>
          <td>2</td>
          <td>PLAN/2025/002</td>
          <td>Supplementary Plan - Mid Year</td>
          <td>2025</td>
          <td>9</td>
          <td>5,200,000</td>
          <td><span class="badge bg-success">Approved</span></td>
          <td>Procurement Officer</td>
          <td>2025-03-01</td>
          <td>
            <a href="#" class="btn btn-sm btn-outline-primary">View</a>
            <a href="#" class="btn btn-sm btn-outline-secondary">Print</a>
          </td>
        </tr>
        <!-- More dynamic rows -->
      </tbody>
    </table>
  </div>
</div>

@endsection