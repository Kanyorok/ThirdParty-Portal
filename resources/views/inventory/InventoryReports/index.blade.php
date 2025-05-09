@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📈 Inventory Reports</h4>

  <div class="row g-3">
    <div class="col-md-4">
      <div class="card shadow-sm border">
        <div class="card-body">
          <h5 class="card-title">📦 Stock Position</h5>
          <p class="card-text text-muted">View current quantities and values by store/item</p>
          <a href="#" class="btn btn-primary w-100">📄 View Report</a>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm border">
        <div class="card-body">
          <h5 class="card-title">📜 Stock Ledger</h5>
          <p class="card-text text-muted">Detailed movement history per item (in/out/adjust)</p>
          <a href="#" class="btn btn-primary w-100">📄 View Report</a>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm border">
        <div class="card-body">
          <h5 class="card-title">🕑 Expiry Report</h5>
          <p class="card-text text-muted">Items nearing expiry or expired</p>
          <a href="#" class="btn btn-primary w-100">📄 View Report</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Add more cards for Fast-Moving, Aging, Valuation, etc. -->
</div>

@endsection