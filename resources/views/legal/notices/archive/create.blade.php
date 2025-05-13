@extends('layouts.app')
@section('title','Correspondence Archive')
@section('content')

<div class="container my-5">
  <div class="text-center mb-4">
    <h2 class="fw-bold text-primary">Add New Correspondence</h2>
    <p class="text-muted">Fill in the details below to archive correspondence</p>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="post" action="save.php">
        <!-- Type -->
        <div class="mb-3">
          <label for="type" class="form-label">Correspondence Type</label>
          <select class="form-select" id="type" name="type" required>
            <option selected disabled value="">Choose type</option>
            <option value="Email">Email</option>
            <option value="Letter">Letter</option>
            <option value="Official Communication">Official Communication</option>
          </select>
        </div>

        <!-- Subject -->
        <div class="mb-3">
          <label for="subject" class="form-label">Subject</label>
          <input type="text" class="form-control" id="subject" name="subject" required>
        </div>

        <!-- Recipient -->
        <div class="mb-3">
          <label for="recipient" class="form-label">Recipient</label>
          <input type="text" class="form-control" id="recipient" name="recipient" required>
        </div>

        <!-- Date Sent -->
        <div class="mb-3">
          <label for="date_sent" class="form-label">Date Sent</label>
          <input type="date" class="form-control" id="date_sent" name="date_sent" required>
        </div>

        <!-- Reference ID -->
        <div class="mb-4">
          <label for="ref_id" class="form-label">Reference ID</label>
          <input type="text" class="form-control" id="ref_id" name="ref_id" required>
        </div>

        <!-- Submit Button -->
        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-primary">Submit Correspondence</button>
          <a href="{{route('archive.index')}} " class="btn btn-secondary">← Back to Archive</a>
        </div>
      </form>
    </div>
  </div>
</div>


@endsection