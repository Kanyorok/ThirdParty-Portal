@extends('layouts.app')
@section('title', 'Opening Stock Load')
@section('content')
<div class="container mt-4">
    <h4 class="fw-bold mb-3">📥 Opening Stock Load (Bulk Upload)</h4>
  <!-- Bulk Upload Card -->
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">📤 Upload Opening Stock (Excel)</div>
    <div class="card-body">
        <form method="POST" action="{{ route('openingstock.upload') }}" enctype="multipart/form-data">
            @csrf
            @csrf
        <div class="row g-3 align-items-end">
          <div class="col-md-6">
            <label class="form-label">Upload Excel File (.xlsx)</label>
              <input type="file" name="excel_file" class="form-control" accept=".xlsx" required>
          </div>
          <div class="col-md-6 text-end">
              <a href="{{ route('openingstock.sample') }}" class="btn btn-outline-primary">
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
