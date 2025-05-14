@extends('layouts.app')
@section('title', 'Tenant Exit')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🚪 Tenant Exit & Clearance Checklist</h4>

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">📋 Exit Process</div>
    <div class="card-body">

      <!-- Select Lease -->
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Tenant / Lease</label>
          <select class="form-select">
            <option>Moses K. – Lease #L-2025-001 – Unit 101</option>
            <option>Acme Ltd. – Lease #L-2025-002 – Unit B204</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Exit Date</label>
          <input type="date" class="form-control" value="2025-08-31">
        </div>
      </div>

      <!-- Checklist Items -->
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">Final Inspection Done?</label>
          <select class="form-select">
            <option>Yes</option>
            <option>No</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">All Dues Paid?</label>
          <select class="form-select">
            <option>Yes</option>
            <option>No</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Keys Returned?</label>
          <select class="form-select">
            <option>Yes</option>
            <option>No</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Deposit Refunded?</label>
          <select class="form-select">
            <option>Fully</option>
            <option>Partially</option>
            <option>Not Refunded</option>
          </select>
        </div>
      </div>

      <!-- Upload & Remarks -->
      <div class="mb-3">
        <label class="form-label">Upload Exit Document (optional)</label>
        <input type="file" class="form-control">
      </div>

      <div class="mb-3">
        <label class="form-label">Additional Notes</label>
        <textarea class="form-control" rows="2" placeholder="Any final notes or clearance details..."></textarea>
      </div>

      <div class="text-end">
        <button class="btn btn-danger">✔ Finalize Exit</button>
      </div>
    </div>
  </div>
</div>

@endsection
