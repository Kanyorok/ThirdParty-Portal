@extends('layouts.app')
@section('title', 'Complete Maintenance Request')
@section('content')

<div class="container mt-4">
  <h4 class="fw-bold mb-3">✅ Complete Maintenance Request</h4>

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">🧰 Work Execution & Resolution</div>
    <div class="card-body">

      <!-- Request Details -->
      <div class="row g-3 mb-3">
        <div class="col-md-8">
          <label class="form-label">Maintenance Request</label>
          <select class="form-select">
            <option>REQ-2025-001 – Plumbing – Unit 101 – Sunset Plaza</option>
            <option>REQ-2025-002 – Electrical – Unit 204 – Mountain View</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Completion Date</label>
          <input type="date" class="form-control" value="2025-05-04">
        </div>
      </div>

      <!-- Work Details -->
      <div class="mb-3">
        <label class="form-label">Work Done Summary</label>
        <textarea class="form-control" rows="3" placeholder="e.g. Replaced leaking pipe and sealed joints."></textarea>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Parts Used (Optional)</label>
          <input type="text" class="form-control" placeholder="e.g. 3/4” Pipe, Valve">
        </div>
        <div class="col-md-4">
          <label class="form-label">Cost (KES)</label>
          <input type="number" class="form-control" placeholder="e.g. 1500">
        </div>
        <div class="col-md-4">
          <label class="form-label">Final Status</label>
          <select class="form-select">
            <option>Completed</option>
            <option>Delayed – Awaiting Part</option>
            <option>Not Fixed – Reassign</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Upload Resolution Evidence (Photos/Invoice)</label>
        <input type="file" class="form-control" multiple>
      </div>

      <div class="text-end">
        <button class="btn btn-success">✔ Mark as Completed</button>
      </div>
    </div>
  </div>
</div>

@endsection
