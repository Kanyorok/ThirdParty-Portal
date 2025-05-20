@extends('layouts.app')
@section('title', 'trailmanagement')
@section('content')
<div class="container mt-5" style="max-width: 800px;">
  <h4 class="mb-4">🛠 Log Document Interaction (Manual)</h4>

  <form method="post" action="save_audit.php">
    <div class="mb-3">
      <label class="form-label">User</label>
      <input type="text" name="user" class="form-control" placeholder="e.g., johndoe" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Document ID or Title</label>
      <input type="text" name="document" class="form-control" placeholder="e.g., 101 or Policy.pdf" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Action</label>
      <select name="action" class="form-select" required>
        <option value="">-- Select Action --</option>
        <option value="uploaded">Uploaded</option>
        <option value="edited">Edited</option>
        <option value="viewed">Viewed</option>
        <option value="deleted">Deleted</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">IP Address</label>
      <input type="text" name="ip" class="form-control" placeholder="e.g., 192.168.1.1">
    </div>

    <div class="mb-3">
      <label class="form-label">User Agent</label>
      <input type="text" name="user_agent" class="form-control" placeholder="e.g., Mozilla/5.0">
    </div>

    <button type="submit" class="btn btn-primary">Log Action</button>
  </form>
</div>
@endsection