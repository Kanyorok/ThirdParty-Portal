@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📆 Lease Schedule Generator</h4>

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">🧮 Generate Billing Periods</div>
    <div class="card-body">
      <!-- Lease Selection -->
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Select Lease Agreement</label>
          <select class="form-select">
            <option>Lease #L-2025-001 - Moses K. - Unit 101</option>
            <option>Lease #L-2025-002 - Acme Ltd. - Unit B204</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Payment Frequency</label>
          <input type="text" class="form-control" value="Monthly" readonly>
        </div>
      </div>

      <!-- Financial Parameters -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Start Date</label>
          <input type="date" class="form-control" value="2025-05-01" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label">End Date</label>
          <input type="date" class="form-control" value="2026-04-30" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label">Base Rent per Period (KES)</label>
          <input type="number" class="form-control" value="25000" readonly>
        </div>
      </div>

      <!-- Optional Charges -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Service Charge (KES)</label>
          <input type="number" class="form-control" value="1500">
        </div>
        <div class="col-md-4">
          <label class="form-label">Parking Fee (KES)</label>
          <input type="number" class="form-control" value="2000">
        </div>
        <div class="col-md-4">
          <label class="form-label">Other Charges (KES)</label>
          <input type="number" class="form-control" value="0">
        </div>
      </div>

      <!-- Action -->
      <div class="text-end">
        <a href="{{ route('schedulelease.create') }}" class="btn btn-success">🧾 Generate Schedule</a>
      </div>
    </div>
  </div>
</div>
@endsection