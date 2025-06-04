@extends('layouts.app')
@section('title', 'Terminate Lease')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🔚 Terminate Lease</h4>

<form action="{{ route('terminatelease.store') }}" method="POST">
   @csrf
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">📄 Termination Details</div>
    <div class="card-body">
      <!-- Select Lease -->
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Select Lease</label>
            <select name="LeaseID" class="form-select" required>
              @foreach ($newtenants as $newtenant)
                <option value="{{ $newtenant->id }}">{{ $newtenant->TenantName }}</option>
              @endforeach
            </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Termination Date</label>
          <input type="date" class="form-control" value="2025-08-31" name="TerminationDate">
        </div>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Reason for Termination</label>
          <select class="form-select" name="TerminationReason">
            <option>Normal Expiry</option>
            <option>Voluntary Exit</option>
            <option>Breach of Terms</option>
            <option>Eviction</option>
            <option>Other</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Remarks</label>        
          <input type="text" class="form-control" placeholder="e.g. Cleared & handed back keys" name="Remarks">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Upload Clearance Document (optional)</label>
        <input type="file" class="form-control" >
      </div>
      <button class="btn btn-danger">🛑 Terminate Lease</button>
      </form>
    </div>
  </div>
</div>
@endsection