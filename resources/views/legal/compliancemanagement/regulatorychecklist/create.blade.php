@extends('layouts.app')
@section('title', 'regulatorychecklist')
@section('content')
<div class="container mt-5" style="max-width: 850px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3> 📄 Add New Regulation</h3>
    <a href="{{ route('regulatorychecklist.index') }}" class="btn btn-outline-secondary">← Back to Register</a>
  </div>
   

  <div class="card">
    <div class="card-body">
      <form method="POST" action="">
        <div class="mb-3">
          <label for="regulation_name" class="form-label">Regulation Name</label>
          <input type="text" name="regulation_name" id="regulation_name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="sector" class="form-label">Applicable Sector</label>
          <input type="text" name="sector" id="sector" class="form-control" placeholder="e.g., Healthcare, Finance" required>
        </div>
        <div class="mb-3">
          <label for="description" class="form-label">Description</label>
          <textarea name="description" id="description" class="form-control" rows="3" required></textarea>
        </div>
        <div class="mb-3">
          <label for="compliance_status" class="form-label">Compliance Status</label>
          <select name="compliance_status" id="compliance_status" class="form-select" required>
            <option value="">-- Select Status --</option>
            <option value="Compliant">Compliant</option>
            <option value="Non-Compliant">Non-Compliant</option>
            <option value="Under Review">Under Review</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="last_reviewed" class="form-label">Last Reviewed Date</label>
          <input type="date" name="last_reviewed" id="last_reviewed" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Add Regulation</button>
      </form>
    </div>
  </div>
</div>
@endsection