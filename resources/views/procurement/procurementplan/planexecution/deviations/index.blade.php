@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>⚠️ Procurement Plan Deviation Dashboard</h4>
    <a href="/execution/dashboard" class="btn btn-sm btn-outline-secondary">← Back to Execution</a>
  </div>

  <!-- Filters -->
  <div class="row mb-4">
    <div class="col-md-3">
      <label class="form-label">Year</label>
      <select class="form-select">
        <option>2025</option>
        <option>2026</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Branch</label>
      <select class="form-select">
        <option>All</option>
        <option>Nairobi</option>
        <option>Mombasa</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Deviation Type</label>
      <select class="form-select">
        <option>All</option>
        <option>Unexecuted</option>
        <option>Delayed</option>
        <option>Off-Plan</option>
        <option>Overbudget</option>
      </select>
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button class="btn btn-outline-primary w-100">Apply Filters</button>
    </div>
  </div>

  <!-- Summary Counters -->
  <div class="row text-center mb-4">
    <div class="col-md-3"><div class="p-3 bg-white border rounded shadow-sm"><h6>Unexecuted</h6><h4 class="text-danger">12</h4></div></div>
    <div class="col-md-3"><div class="p-3 bg-white border rounded shadow-sm"><h6>Delayed</h6><h4 class="text-warning">7</h4></div></div>
    <div class="col-md-3"><div class="p-3 bg-white border rounded shadow-sm"><h6>Off-Plan</h6><h4 class="text-primary">5</h4></div></div>
    <div class="col-md-3"><div class="p-3 bg-white border rounded shadow-sm"><h6>Overbudget</h6><h4 class="text-danger">3</h4></div></div>
  </div>

  <!-- Deviation Table -->
  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Item</th>
          <th>Branch</th>
          <th>Planned Qty</th>
          <th>Actual Qty</th>
          <th>Planned Cost</th>
          <th>Actual Cost</th>
          <th>Deviation Type</th>
          <th>Remarks</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sample Rows -->
        <tr>
          <td>1</td>
          <td>Desktop Computers</td>
          <td>Nairobi</td>
          <td>100</td>
          <td>80</td>
          <td>3,000,000</td>
          <td>3,200,000</td>
          <td><span class="badge bg-danger">Overbudget</span></td>
          <td>Procured at higher market rate</td>
        </tr>
        <tr>
          <td>2</td>
          <td>Air Conditioners</td>
          <td>Kisumu</td>
          <td>—</td>
          <td>5</td>
          <td>—</td>
          <td>900,000</td>
          <td><span class="badge bg-primary">Off-Plan</span></td>
          <td>Urgent technical issue</td>
        </tr>
        <tr>
          <td>3</td>
          <td>Filing Cabinets</td>
          <td>Mombasa</td>
          <td>50</td>
          <td>0</td>
          <td>750,000</td>
          <td>—</td>
          <td><span class="badge bg-danger">Unexecuted</span></td>
          <td>No RFQ issued</td>
        </tr>
        <tr>
          <td>4</td>
          <td>Routers</td>
          <td>Nairobi</td>
          <td>30</td>
          <td>30</td>
          <td>600,000</td>
          <td>600,000</td>
          <td><span class="badge bg-warning text-dark">Delayed</span></td>
          <td>Delivered one quarter late</td>
        </tr>
        <!-- More rows -->
      </tbody>
    </table>
  </div>

</div>

@endsection