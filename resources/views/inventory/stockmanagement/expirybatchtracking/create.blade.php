@extends('layouts.app')
@section('title', 'Stock Expiry')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">⏳ Add Stock Expiry / Perishable Batch</h4>

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">➕ Add Expiry Batch</div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">Item</label>
          <select class="form-select">
            <option>ITM-010 - Vitamin C</option>
            <option>ITM-011 - Milk Powder</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Batch Number</label>
          <input type="text" class="form-control" placeholder="e.g. BATCH-00123">
        </div>
        <div class="col-md-3">
          <label class="form-label">Store</label>
          <select class="form-select">
            <option>Main Store</option>
            <option>Cold Room</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Branch</label>
          <select class="form-select">
            <option>Branch A</option>
            <option>Branch B</option>
          </select>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">Received Date</label>
          <input type="date" class="form-control" value="2025-05-02">
        </div>
        <div class="col-md-3">
          <label class="form-label">Expiry Date</label>
          <input type="date" class="form-control">
        </div>
        <div class="col-md-3">
          <label class="form-label">Quantity</label>
          <input type="number" class="form-control" placeholder="e.g. 100">
        </div>
        <div class="col-md-3">
          <label class="form-label">Remarks</label>
          <input type="text" class="form-control" placeholder="Optional notes">
        </div>
      </div>

      <div class="text-end">
        <button class="btn btn-success">💾 Save Batch</button>
      </div>
    </div>
  </div>
</div>
@endsection