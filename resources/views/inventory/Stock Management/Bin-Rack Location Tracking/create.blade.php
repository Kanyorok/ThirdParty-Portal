@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📦 Bin / Location Mapping</h4>
  <a href="{{ route('bintracking.create') }}" class="btn btn-sm btn-light">➕ Add New</a>
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">➕ Add Bin Location for Item</div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Store</label>
          <select class="form-select">
            <option>Main Store</option>
            <option>Back Store</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Item</label>
          <select class="form-select">
            <option>ITM-001 - A4 Paper</option>
            <option>ITM-002 - Printer</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Bin/Location Code</label>
          <input type="text" class="form-control" placeholder="e.g. R1-S2-B3">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Max Capacity</label>
          <input type="number" class="form-control" placeholder="Optional">
        </div>
        <div class="col-md-4">
          <label class="form-label">Current Qty</label>
          <input type="number" class="form-control" placeholder="Optional">
        </div>
        <div class="col-md-4">
          <label class="form-label">Remarks</label>
          <input type="text" class="form-control" placeholder="e.g. Fragile zone">
        </div>
      </div>

      <div class="text-end">
        <button class="btn btn-success">💾 Save Bin Mapping</button>
      </div>
    </div>
  </div>
</div>
@endsection