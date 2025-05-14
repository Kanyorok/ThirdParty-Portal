@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>📊 Procurement Plan Execution Dashboard</h4>
    <a href="/plan" class="btn btn-sm btn-outline-secondary">← Back to Plans</a>
  </div>

  <!-- Filters -->
  <div class="row mb-4">
    <div class="col-md-3">
      <label class="form-label">Plan Year</label>
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
      <label class="form-label">Department</label>
      <select class="form-select">
        <option>All</option>
        <option>ICT</option>
        <option>Finance</option>
      </select>
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button class="btn btn-outline-primary w-100">Apply Filters</button>
    </div>
  </div>

  <!-- High-Level KPIs -->
  <div class="row text-center mb-4">
    <div class="col-md-3">
      <div class="border p-3 rounded shadow-sm bg-white">
        <h6>Total Items</h6>
        <h4>120</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="border p-3 rounded shadow-sm bg-white">
        <h6>Procurements Initiated</h6>
        <h4 class="text-success">85</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="border p-3 rounded shadow-sm bg-white">
        <h6>Items Delivered</h6>
        <h4 class="text-primary">48</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="border p-3 rounded shadow-sm bg-white">
        <h6>Overdue / Delayed</h6>
        <h4 class="text-danger">10</h4>
      </div>
    </div>
  </div>

  <!-- Execution by Branch Progress Bars -->
  <h5 class="mt-4 mb-3">🏢 Progress by Branch</h5>
  <div class="mb-3">
    <label class="form-label">Nairobi (65 / 80 items)</label>
    <div class="progress">
      <div class="progress-bar bg-success" style="width: 81%">81%</div>
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label">Mombasa (20 / 30 items)</label>
    <div class="progress">
      <div class="progress-bar bg-warning" style="width: 66%">66%</div>
    </div>
  </div>

  <!-- Execution by Procurement Method -->
  <h5 class="mt-4 mb-3">📦 Execution by Method</h5>
  <table class="table table-bordered">
    <thead class="table-light">
      <tr>
        <th>Method</th>
        <th>Total Items</th>
        <th>Initiated</th>
        <th>Delivered</th>
        <th>Pending</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Open Tender</td>
        <td>50</td>
        <td>40</td>
        <td>15</td>
        <td>10</td>
      </tr>
      <tr>
        <td>RFQ</td>
        <td>40</td>
        <td>35</td>
        <td>25</td>
        <td>5</td>
      </tr>
      <tr>
        <td>Direct Procurement</td>
        <td>30</td>
        <td>10</td>
        <td>8</td>
        <td>20</td>
      </tr>
    </tbody>
  </table>

</div>

@endsection