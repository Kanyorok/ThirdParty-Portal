@extends('layouts.app')
@section('title', 'Opening Stock Load')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📥 Opening Stock Load (Manual & Bulk Upload)</h4>

  <!-- Manual Entry Card -->
  <div class="card shadow mb-4">
    <div class="card-header bg-light fw-bold">➕ Add Opening Stock Entry (Manual)</div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">Branch</label>
          <select class="form-select">
            <option>Branch A</option>
            <option>Branch B</option>
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
          <label class="form-label">Item</label>
          <select class="form-select">
            <option>ITM-001 - A4 Paper</option>
            <option>ITM-002 - Printer</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Date</label>
          <input type="date" class="form-control" value="2025-05-02">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">Quantity</label>
          <input type="number" class="form-control" placeholder="e.g. 10">
        </div>
        <div class="col-md-3">
          <label class="form-label">UOM</label>
          <select class="form-select">
            <option value="pcs">pcs (base)</option>
            <option value="dozen">dozen (×12)</option>
            <option value="carton">carton (×24)</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Value (KES)</label>
          <input type="number" class="form-control" placeholder="e.g. 2400">
        </div>
        <div class="col-md-3">
          <label class="form-label">Remarks</label>
          <input type="text" class="form-control" placeholder="Optional">
        </div>
      </div>

      <div class="text-end">
        <button class="btn btn-success">💾 Save Entry</button>
      </div>
    </div>
  </div>

  <!-- Bulk Upload Card -->
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">📤 Upload Opening Stock (Excel)</div>
    <div class="card-body">
      <form>
        <div class="row g-3 align-items-end">
          <div class="col-md-6">
            <label class="form-label">Upload Excel File (.xlsx)</label>
            <input type="file" class="form-control" accept=".xlsx">
          </div>
          <div class="col-md-6 text-end">
            <a href="/downloads/opening_stock_uom_sample.xlsx" class="btn btn-outline-primary">
              ⬇️ Download Sample Template
            </a>
            <button type="submit" class="btn btn-primary ms-2">📤 Upload & Import</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection