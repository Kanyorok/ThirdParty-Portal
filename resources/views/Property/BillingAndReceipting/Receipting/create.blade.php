@extends('layouts.app')
@section('title', 'Tenant Payments')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">💳 Record Tenant Payment (Supports Partials)</h4>

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">🧾 Payment Details</div>
    <div class="card-body">

      <!-- Select Invoice -->
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Select Invoice</label>
          <select class="form-select">
            <option value="1">INV-2025-0001 – Moses K. – KES 26,500</option>
            <option value="2">INV-2025-0002 – Acme Ltd. – KES 78,000</option>
          </select>
        </div>
      </div>

      <!-- Invoice Summary (Static example, should populate dynamically) -->
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">Total Due</label>
          <input type="text" class="form-control" value="26,500" readonly>
        </div>
        <div class="col-md-3">
          <label class="form-label">Amount Paid So Far</label>
          <input type="text" class="form-control" value="10,000" readonly>
        </div>
        <div class="col-md-3">
          <label class="form-label">Balance</label>
          <input type="text" class="form-control" value="16,500" readonly>
        </div>
        <div class="col-md-3">
          <label class="form-label">Payment Date</label>
          <input type="date" class="form-control" value="2025-05-03">
        </div>
      </div>

      <!-- Payment Entry -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Amount Paid Now</label>
          <input type="number" class="form-control" placeholder="e.g. 5000" max="16500">
        </div>
        <div class="col-md-4">
          <label class="form-label">Payment Method</label>
          <select class="form-select">
            <option>MPESA</option>
            <option>Bank Transfer</option>
            <option>Cash</option>
            <option>Cheque</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Reference / Receipt No</label>
          <input type="text" class="form-control" placeholder="e.g. MPESA12345">
        </div>
      </div>

      <!-- Optional Notes -->
      <div class="mb-3">
        <label class="form-label">Remarks</label>
        <textarea class="form-control" rows="2" placeholder="e.g. Paid KES 5,000 - next part due 10th"></textarea>
      </div>

      <div class="text-end">
        <button class="btn btn-success">💾 Record Payment</button>
      </div>
    </div>
  </div>
</div>
@endsection