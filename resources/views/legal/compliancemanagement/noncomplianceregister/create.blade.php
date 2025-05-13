@extends('layouts.app')
@section('title', 'noncomplianceregister')
@section('content')
<div class="container mt-5" style="max-width: 850px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>🚨 Log Non-Compliance Incident</h3>
    <a href="{{ route('noncomplianceregister.index') }}" class="btn btn-outline-secondary">← Back to Register</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="">
        <div class="mb-3">
          <label for="violation_type" class="form-label">Violation Type</label>
          <input type="text" name="violation_type" id="violation_type" class="form-control" placeholder="e.g., Data Breach, Safety Violation" required>
        </div>
        <div class="mb-3">
          <label for="date_reported" class="form-label">Date Reported</label>
          <input type="date" name="date_reported" id="date_reported" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="description" class="form-label">Description</label>
          <textarea name="description" id="description" class="form-control" rows="3" required></textarea>
        </div>
        <div class="mb-3">
          <label for="penalty" class="form-label">Penalty</label>
          <input type="text" name="penalty" id="penalty" class="form-control" placeholder="e.g., KES 100,000 fine">
        </div>
        <div class="mb-3">
          <label for="remediation_action" class="form-label">Remediation Action</label>
          <textarea name="remediation_action" id="remediation_action" class="form-control" rows="3"></textarea>
        </div>
        <div class="mb-3">
          <label for="status" class="form-label">Status</label>
          <select name="status" id="status" class="form-select" required>
            <option value="">-- Select Status --</option>
            <option value="Open">Open</option>
            <option value="In Progress">In Progress</option>
            <option value="Closed">Closed</option>
          </select>
        </div>
        <button type="submit" class="btn btn-success">Log Incident</button>
      </form>
    </div>
  </div>
@endsection