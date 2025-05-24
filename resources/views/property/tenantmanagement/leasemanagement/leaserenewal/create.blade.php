@extends('layouts.app')
@section('title', 'Renew Lease Agreement')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🔁 Renew Lease Agreement</h4>

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">📝 New Lease Terms</div>
    <div class="card-body">

      <!-- Select Current Lease -->
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Current Lease</label>
          <select class="form-select">
            <option>Lease #L-2025-001 – Moses K. – Unit 101</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">End Date of Current Lease</label>
          <input type="date" class="form-control" value="2025-08-31" readonly>
        </div>
        <div class="col-md-3">
          <label class="form-label">New Start Date</label>
          <input type="date" class="form-control" value="2025-09-01">
        </div>
      </div>

      <!-- New Terms -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">New End Date</label>
          <input type="date" class="form-control" value="2026-08-31">
        </div>
        <div class="col-md-4">
          <label class="form-label">New Monthly Rent</label>
          <input type="number" class="form-control" value="27500">
        </div>
        <div class="col-md-4">
          <label class="form-label">Payment Frequency</label>
          <select class="form-select">
            <option selected>Monthly</option>
            <option>Quarterly</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Remarks or Changes</label>
        <textarea class="form-control" rows="2" placeholder="E.g. rent increased by KES 2,500"></textarea>
      </div>

      <div class="text-end">
        <button class="btn btn-success">🔁 Renew Lease</button>
      </div>
    </div>
  </div>
</div>
@endsection