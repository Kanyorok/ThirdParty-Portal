@extends('layouts.app')
@section('title', 'Consolidated Procurement Needs')
@section('content')

<div class="card p-4 shadow rounded-4">
  <h4 class="mb-4">🧾 Consolidated Procurement Needs - HO Dashboard</h4>

  <!-- Filters -->
  <div class="row mb-4">
    <div class="col-md-3">
      <label class="form-label">Filter by Branch</label>
      <select class="form-select">
        <option selected>All Branches</option>
        <option>Head Office</option>
        <option>Nairobi Branch</option>
        <option>Mombasa Branch</option>
        <!-- Load dynamically -->
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Filter by Department</label>
      <select class="form-select">
        <option selected>All Departments</option>
        <option>ICT</option>
        <option>Finance</option>
        <option>HR</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Planning Year</label>
      <select class="form-select">
        <option>2025</option>
        <option>2026</option>
        <option>2027</option>
      </select>
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button class="btn btn-primary w-100">Apply Filters</button>
    </div>
  </div>

  <!-- Consolidation Table -->
  <table class="table table-bordered table-striped">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Item Name</th>
        <th>Branch</th>
        <th>Department</th>
        <th>Qty</th>
        <th>Est. Cost</th>
        <th>Required By</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <!-- Sample Row -->
      <tr>
        <td>1</td>
        <td>Desktop Computer</td>
        <td>Nairobi Branch</td>
        <td>ICT</td>
        <td>5</td>
        <td>125,000</td>
        <td>2025-07-01</td>
        <td><span class="badge bg-info">Branch Approved</span></td>
        <td>
          <a href="#" class="btn btn-sm btn-outline-info">View</a>
          <button class="btn btn-sm btn-outline-primary">Include in Plan</button>
        </td>
      </tr>
      <!-- Loop other rows dynamically -->
    </tbody>
  </table>

  <div class="mt-4 d-flex justify-content-end">
    <button class="btn btn-outline-secondary">🗃 Export to Excel</button>
  </div>
</div>

@endsection