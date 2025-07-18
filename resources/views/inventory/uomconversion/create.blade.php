@extends('layouts.app')
@section('title', 'Unit of Measure & Conversion Setup')
@section('content')
<div class="container mt-4">

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">➕ Add UOM Conversion for Item</div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Item</label>
          <select class="form-select">
            <option>ITM-001 - A4 Paper</option>
            <option>ITM-002 - Printer</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Base UOM</label>
          <select class="form-select">
            <option>pcs</option>
            <option>unit</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Alternate UOM</label>
          <select class="form-select">
            <option>dozen</option>
            <option>carton</option>
            <option>box</option>
          </select>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Conversion Factor</label>
          <input type="number" class="form-control" placeholder="e.g. 12 (dozen = 12 pcs)">
        </div>
        <div class="col-md-4">
          <label class="form-label">Remarks</label>
          <input type="text" class="form-control" placeholder="Optional">
        </div>
        <div class="col-md-4 d-flex align-items-end justify-content-end">
          <button class="btn btn-success mt-2">💾 Save Mapping</button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection