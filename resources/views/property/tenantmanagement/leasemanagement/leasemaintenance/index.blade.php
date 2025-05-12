@extends('layouts.app')
@section('title', 'Lease Agreements')
@section('content')

<div class="container mt-4">
  <h4 class="fw-bold mb-3">📄 New Lease Agreement</h4>
  <a href="{{ route('addlease.create') }}" class="btn btn-primary mb-3">New Lease</a>
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">📝 Lease Details</div>
    <div class="card-body">

      <!-- Tenant -->
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Select Tenant</label>
          <select class="form-select">
            <option>Moses K. (Individual)</option>
            <option>Acme Ltd. (Corporate)</option>
          </select>
        </div>
      </div>

      <!-- Property Drill-down -->
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">Select Property</label>
          <select class="form-select" id="propertySelect">
            <option>Sunset Plaza</option>
            <option>Mountain View Estate</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Select Block</label>
          <select class="form-select" id="blockSelect">
            <option>Block A</option>
            <option>Block B</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Select Floor</label>
          <select class="form-select" id="floorSelect">
            <option>Ground Floor</option>
            <option>1st Floor</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Select Unit</label>
          <select class="form-select" id="unitSelect">
            <option>Unit 101</option>
            <option>Unit 102</option>
          </select>
        </div>
      </div>

      <!-- Lease Duration -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Start Date</label>
          <input type="date" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">End Date</label>
          <input type="date" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Payment Frequency</label>
          <select class="form-select">
            <option>Monthly</option>
            <option>Quarterly</option>
            <option>Annually</option>
          </select>
        </div>
      </div>

      <!-- Financials -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Monthly Rent (KES)</label>
          <input type="number" class="form-control" placeholder="e.g. 25000">
        </div>
        <div class="col-md-4">
          <label class="form-label">Deposit (KES)</label>
          <input type="number" class="form-control" placeholder="e.g. 25000">
        </div>
        <div class="col-md-4">
          <label class="form-label">Due Day</label>
          <input type="number" class="form-control" placeholder="e.g. 5">
        </div>
      </div>

      <!-- Terms & Documents -->
      <div class="mb-3">
        <label class="form-label">Special Terms & Conditions</label>
        <textarea class="form-control" rows="3" placeholder="Optional terms or notes..."></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Upload Lease Document</label>
        <input type="file" class="form-control" accept=".pdf,.docx">
      </div>

      <div class="text-end">
        <button class="btn btn-success">💾 Save Lease</button>
      </div>
    </div>
  </div>
</div>
@endsection