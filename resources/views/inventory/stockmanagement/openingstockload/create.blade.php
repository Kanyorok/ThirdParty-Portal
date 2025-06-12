@extends('layouts.app')
@section('title', 'Opening Stock Load')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📥 Opening Stock Load (Manual & Bulk Upload)</h4>

  <!-- Manual Entry Card -->
  <div class="card shadow mb-4">
    <div class="card-header bg-light fw-bold">➕ Add Opening Stock Entry (Manual)</div>
    <div class="card-body">
      <form method="POST" action="{{ route('openingstock.store') }}">
        @csrf
        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">Branch</label>
            <select name="BranchId" class="form-select" required>
              <option value="">-- Select Branch --</option>
              @foreach ($branches as $branch)
              <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Store</label>
            <select name="StoreId" class="form-select" required>
              <option value="">-- Select Store --</option>
              @foreach ($stores as $store)
              <option value="{{ $store->Id }}">{{ $store->Name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Item</label>
            <select name="ItemCode" class="form-select" required>
              <option value="">-- Select Item --</option>
              @foreach ($items as $item)
              <option value="{{ $item->ItemCode }}">{{ $item->ItemName }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Date</label>
            <input type="date" name="Date" class="form-control" value="{{ date('Y-m-d') }}" required>
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">Quantity</label>
            <input type="number" name="Quantity" class="form-control" placeholder="e.g. 10" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">UOM</label>
            <select name="UOM" class="form-select" required>
              <option value="">-- Select UOM --</option>
              @foreach ($items as $item)
              <option value="{{ $item->UOM }}">{{ $item->uom->Code }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Value (KES)</label>
            <input type="number" name="Value" class="form-control" placeholder="e.g. 2400" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Remarks</label>
            <input type="text" name="Remarks" class="form-control" placeholder="Optional">
          </div>
        </div>

        <div class="text-end">
          <button type="submit" class="btn btn-success">💾 Save Entry</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Bulk Upload Card -->
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">📤 Upload Opening Stock (Excel)</div>
    <div class="card-body">
      <form method="POST" action="#" enctype="multipart/form-data">
        @csrf
        <div class="row g-3 align-items-end">
          <div class="col-md-6">
            <label class="form-label">Upload Excel File (.xlsx)</label>
            <input type="file" name="excel_file" class="form-control" accept=".xlsx" required>
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
