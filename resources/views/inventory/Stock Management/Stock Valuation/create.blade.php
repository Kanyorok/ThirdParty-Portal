@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🧮 Add Stock Valuation Entry</h4>

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">➕ Manual Valuation Entry</div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">Item</label>
          <select class="form-select">
            <option>ITM-001 - A4 Paper</option>
            <option>ITM-002 - Printer</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Store</label>
          <select class="form-select">
            <option>Main Store</option>
            <option>Back Store</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Movement Type</label>
          <select class="form-select">
            <option>GRN</option>
            <option>Issue</option>
            <option>Adjust</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Movement Ref</label>
          <input type="text" class="form-control" placeholder="e.g. GRN-2025-0012">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">Quantity</label>
          <input type="number" class="form-control" placeholder="e.g. 10">
        </div>
        <div class="col-md-3">
          <label class="form-label">Unit Cost (KES)</label>
          <input type="number" class="form-control" placeholder="e.g. 50">
        </div>
        <div class="col-md-3">
          <label class="form-label">Total Value</label>
          <input type="number" class="form-control" placeholder="Auto-calculated" disabled>
        </div>
        <div class="col-md-3">
          <label class="form-label">Cost Method</label>
          <select class="form-select">
            <option>AVERAGE</option>
            <option>FIFO</option>
            <option>LIFO</option>
            <option>STANDARD</option>
          </select>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Remarks</label>
          <input type="text" class="form-control" placeholder="Optional">
        </div>
        <div class="col-md-6 d-flex align-items-end justify-content-end">
          <button class="btn btn-success">💾 Save Valuation Entry</button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection