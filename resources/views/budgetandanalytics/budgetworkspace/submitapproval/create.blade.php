@extends('layouts.app')
@section('title', 'Submit Budget For review')
@section('content')
<div class="card p-4">
  <h5>📤 Submit Budget</h5>
  <p class="text-muted">Submit your completed budget for review. Once submitted, it will be locked and routed for approval.</p>

  <div class="mb-3">
    <label for="branch" class="form-label">Branch</label>
    <input type="text" class="form-control" id="branch" value="Central Branch" readonly>
  </div>

  <div class="mb-3">
    <label for="scenario" class="form-label">Scenario</label>
    <select class="form-select" id="scenario">
      <option selected disabled>Select Scenario</option>
      <option>Base Case</option>
      <option>Best Case</option>
      <option>Worst Case</option>
    </select>
  </div>

  <div class="mb-3">
    <label for="period" class="form-label">Budget Period</label>
    <select class="form-select" id="period">
      <option>2025</option>
      <option>2026</option>
    </select>
  </div>

  <div class="mb-3">
    <label for="remarks" class="form-label">Remarks</label>
    <textarea class="form-control" id="remarks" rows="3" placeholder="Any notes or comments to the approver..."></textarea>
  </div>

  <div class="alert alert-warning mt-3">
    ⚠️ Once submitted, this budget cannot be edited unless rejected by the reviewer.
  </div>

  <button class="btn btn-primary">📤 Submit Budget</button>
  <button class="btn btn-secondary">🔙 Cancel</button>
</div>


@endsection
