@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')

<div class="card p-4 shadow rounded-4">
  <h4 class="mb-4">➕ Add Line Item to Procurement Plan</h4>

  <form>
    <!-- Plan Selection -->
    <div class="mb-3">
      <label class="form-label">Procurement Plan</label>
      <select class="form-select">
        <option selected disabled>Select Plan</option>
        <option>Annual Procurement Plan - 2025</option>
        <option>Supplementary Plan - 2025</option>
      </select>
    </div>

    <!-- Item Details -->
    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Item Name</label>
        <input type="text" class="form-control" placeholder="e.g. Office Chairs">
      </div>
      <div class="col-md-6">
        <label class="form-label">Item Category</label>
        <select class="form-select">
          <option>Furniture</option>
          <option>IT Equipment</option>
          <option>Stationery</option>
        </select>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-4">
        <label class="form-label">Quantity</label>
        <input type="number" class="form-control" placeholder="e.g. 10">
      </div>
      <div class="col-md-4">
        <label class="form-label">Unit of Measure</label>
        <select class="form-select">
          <option>Pcs</option>
          <option>Boxes</option>
          <option>Units</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Estimated Cost</label>
        <input type="number" class="form-control" placeholder="e.g. 50000">
      </div>
    </div>

    <!-- Schedule -->
    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Planned Quarter</label>
        <select class="form-select">
          <option>Q1</option>
          <option>Q2</option>
          <option>Q3</option>
          <option>Q4</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Expected Delivery Date</label>
        <input type="date" class="form-control">
      </div>
    </div>

    <!-- ✅ Budget Line Linking -->
    <div class="mb-4">
      <label class="form-label">Link to Budget Line</label>
      <select class="form-select">
        <option selected disabled>Select Budget Line</option>
        <option value="101">Office Furniture - FY2025 (KES 500,000 Available)</option>
        <option value="102">IT Equipment - FY2025 (KES 1,200,000 Available)</option>
        <option value="103">Stationery - FY2025 (KES 300,000 Available)</option>
        <!-- Dynamically loaded -->
      </select>
    </div>

    <!-- Notes -->
    <div class="mb-3">
      <label class="form-label">Notes / Justification</label>
      <textarea class="form-control" rows="3" placeholder="Add any remarks..."></textarea>
    </div>

    <div class="d-flex justify-content-end">
      <button type="reset" class="btn btn-outline-secondary me-2">Clear</button>
      <button type="submit" class="btn btn-primary">➕ Add to Plan</button>
    </div>
  </form>
</div>

@endsection