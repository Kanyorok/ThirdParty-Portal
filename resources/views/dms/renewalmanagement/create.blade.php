@extends('layouts.app')
@section('title', 'renewalmanagement')
@section('content')
<div class="container mt-5" style="max-width: 800px;">
  <h4 class="mb-4">⏰ Set Document Expiry Date</h4>

  <form method="post" action="save_expiry.php">
    <div class="mb-3">
      <label class="form-label">Document Title</label>
      <input type="text" name="title" class="form-control" placeholder="e.g., Business License" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Type</label>
      <select name="type" class="form-select" required>
        <option value="">-- Select Type --</option>
        <option>License</option>
        <option>Insurance</option>
        <option>Certification</option>
        <option>Other</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Expiry Date</label>
      <input type="date" name="expiry_date" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Renewal Status</label>
      <select name="renewal_status" class="form-select">
        <option value="active">Active</option>
        <option value="pending">Pending</option>
        <option value="renewed">Renewed</option>
      </select>
    </div>

    <button type="submit" class="btn btn-success">Save</button>
  </form>
</div>
@endsection