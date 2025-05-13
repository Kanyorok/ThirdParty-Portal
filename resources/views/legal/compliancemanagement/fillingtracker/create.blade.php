@extends('layouts.app')
@section('title', 'fillingtracker')
@section('content')
<div class="container mt-5" style="max-width: 850px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📄 Add New Filing</h3>
    <a href="{{ route('fillingtracker.index') }}" class="btn btn-outline-secondary">← Back to Tracker</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="">
        <div class="mb-3">
          <label for="report_title" class="form-label">Report Title</label>
          <input type="text" name="report_title" id="report_title" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="authority" class="form-label">Submitted To</label>
          <input type="text" name="authority" id="authority" class="form-control" placeholder="e.g., KRA, NEMA" required>
        </div>
        <div class="mb-3">
          <label for="submission_date" class="form-label">Submission Date</label>
          <input type="date" name="submission_date" id="submission_date" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="reference_number" class="form-label">Reference Number</label>
          <input type="text" name="reference_number" id="reference_number" class="form-control">
        </div>
        <div class="mb-3">
          <label for="status" class="form-label">Status</label>
          <select name="status" id="status" class="form-select" required>
            <option value="">-- Select Status --</option>
            <option value="Submitted">Submitted</option>
            <option value="Acknowledged">Acknowledged</option>
            <option value="Pending">Pending</option>
          </select>
        </div>
        <button type="submit" class="btn btn-success">Add Filing</button>
      </form>
    </div>
  </div>
</div>
@endsection