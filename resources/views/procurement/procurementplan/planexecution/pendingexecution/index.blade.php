@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>📦 Items Pending Execution</h4>
    <a href="/execution/dashboard" class="btn btn-sm btn-outline-secondary">← Back to Execution Dashboard</a>
  </div>

  <!-- Filters -->
  <form method="GET" action="/execution/pending">
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
        <label class="form-label">Procurement Method</label>
        <select class="form-select">
          <option>All</option>
          <option>Open Tender</option>
          <option>RFQ</option>
          <option>Direct Procurement</option>
        </select>
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <button class="btn btn-outline-primary w-100">Apply Filters</button>
      </div>
    </div>
  </form>

  <!-- Pending Execution Table -->
  <form method="POST" action="/execution/initiate">
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th><input type="checkbox" id="selectAll"></th>
            <th>Item</th>
            <th>Branch</th>
            <th>Qty</th>
            <th>Cost</th>
            <th>Procurement Method</th>
            <th>Planned Quarter</th>
          </tr>
        </thead>
        <tbody>
          <!-- Sample row -->
          <tr>
            <td><input type="checkbox" name="selectedItems[]" value="501"></td>
            <td>Desktop Computers</td>
            <td>Nairobi</td>
            <td>100</td>
            <td>3,000,000</td>
            <td>Open Tender</td>
            <td>Q1</td>
          </tr>
          <tr>
            <td><input type="checkbox" name="selectedItems[]" value="502"></td>
            <td>Filing Cabinets</td>
            <td>Mombasa</td>
            <td>50</td>
            <td>750,000</td>
            <td>RFQ</td>
            <td>Q2</td>
          </tr>
          <!-- More rows dynamically loaded -->
        </tbody>
      </table>
    </div>

    <!-- Submit Action -->
    <div class="d-flex justify-content-end mt-3">
      <button class="btn btn-success" type="submit">
        🚀 Initiate Selected Items
      </button>
    </div>
  </form>

</div>

@endsection