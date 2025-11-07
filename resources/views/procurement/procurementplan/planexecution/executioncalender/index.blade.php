@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>📆 Procurement Plan Execution Calendar – 2025</h4>
    <a href="/execution/dashboard" class="btn btn-sm btn-outline-secondary">← Back to Dashboard</a>
  </div>

  <!-- Filters -->
  <div class="row mb-4">
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
    <div class="col-md-3">
      <label class="form-label">Method</label>
      <select class="form-select">
        <option>All</option>
        <option>RFQ</option>
        <option>Open Tender</option>
        <option>Direct Procurement</option>
      </select>
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button class="btn btn-outline-primary w-100">Apply Filters</button>
    </div>
  </div>

  <!-- Execution Calendar Table -->
  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light text-center">
        <tr>
          <th rowspan="2">#</th>
          <th rowspan="2">Item</th>
          <th rowspan="2">Branch</th>
          <th colspan="4">Planned Execution</th>
          <th colspan="4">Actual Execution</th>
        </tr>
        <tr>
          <th>Q1</th>
          <th>Q2</th>
          <th>Q3</th>
          <th>Q4</th>
          <th>Q1</th>
          <th>Q2</th>
          <th>Q3</th>
          <th>Q4</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sample Item -->
        <tr>
          <td>1</td>
          <td>Desktop Computers</td>
          <td>Nairobi</td>
          <!-- Planned -->
          <td class="table-success text-center">✔️</td>
          <td class="text-center">—</td>
          <td class="text-center">✔️</td>
          <td class="text-center">—</td>
          <!-- Actual -->
          <td class="table-warning text-center">Delayed</td>
          <td class="text-center">—</td>
          <td class="text-center">✔️</td>
          <td class="text-center">—</td>
        </tr>
        <tr>
          <td>2</td>
          <td>Filing Cabinets</td>
          <td>Mombasa</td>
          <td class="text-center">—</td>
          <td class="table-success text-center">✔️</td>
          <td class="text-center">—</td>
          <td class="text-center">✔️</td>
          <td class="text-center">—</td>
          <td class="table-danger text-center">Missed</td>
          <td class="text-center">✔️</td>
          <td class="text-center">—</td>
        </tr>
        <!-- More items dynamically -->
      </tbody>
    </table>
  </div>

</div>

@endsection