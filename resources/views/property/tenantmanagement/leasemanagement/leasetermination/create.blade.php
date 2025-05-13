@extends('layouts.app')
@section('title', 'Terminate Lease')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🔚 Terminate Lease</h4>

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">📄 Termination Details</div>
    <div class="card-body">
      <!-- Select Lease -->
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Select Lease</label>
          <select class="form-select">
            <option>Lease #L-2025-001 – Moses K. – Unit 101</option>
            <option>Lease #L-2025-002 – Acme Ltd. – Unit B204</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Termination Date</label>
          <input type="date" class="form-control" value="2025-08-31">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Reason for Termination</label>
          <select class="form-select">
            <option>Normal Expiry</option>
            <option>Voluntary Exit</option>
            <option>Breach of Terms</option>
            <option>Eviction</option>
            <option>Other</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Remarks</label>
          <input type="text" class="form-control" placeholder="e.g. Cleared & handed back keys">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Upload Clearance Document (optional)</label>
        <input type="file" class="form-control">
      </div>

      <div class="text-end">
        <button class="btn btn-danger">🛑 Terminate Lease</button>
      </div>
    </div>
  </div>
</div>
@endsection