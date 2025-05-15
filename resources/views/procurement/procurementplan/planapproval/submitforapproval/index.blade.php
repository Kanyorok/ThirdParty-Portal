@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>📤 Submit Procurement Plan for Approval</h4>
    <a href="/planning" class="btn btn-sm btn-outline-secondary">← Back to Plans</a>
  </div>

  <!-- Summary -->
  <div class="alert alert-info">
    <strong>Plan Ref:</strong> PLAN/2025/001 <br>
    <strong>Year:</strong> 2025<br>
    <strong>Status:</strong> DRAFT
  </div>

  <!-- Pre-submission checklist -->
  <div class="mb-3">
    <label class="form-label"><strong>Checklist Before Submission:</strong></label>
    <ul>
      <li>✅ All items linked to valid Budget Lines</li>
      <li>✅ Procurement Methods assigned</li>
      <li>✅ Schedule breakdown completed for each item</li>
    </ul>
  </div>

  <!-- Remarks -->
  <div class="mb-3">
    <label class="form-label">Submission Remarks (Optional)</label>
    <textarea class="form-control" name="remarks" rows="3" placeholder="e.g. Finalized and verified by Procurement Team"></textarea>
  </div>

  <!-- Submit -->
  <form method="POST" action="/planning/submit-for-approval">
    <input type="hidden" name="planId" value="1">
    <div class="d-flex justify-content-end">
      <button class="btn btn-primary">📤 Submit for Approval</button>
    </div>
  </form>
</div>

@endsection
