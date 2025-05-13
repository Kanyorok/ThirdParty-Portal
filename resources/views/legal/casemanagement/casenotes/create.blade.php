@extends('layouts.app')
@section('title', 'casenotes')
@section('content')
<div class="container mt-5" style="max-width: 800px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📝 Add Case Note</h3>
    <a href="{{ route('casenotes.index') }}" class="btn btn-outline-secondary">← Back to Notes List</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="">
        <div class="mb-3">
          <label for="case_number" class="form-label">Case Number</label>
          <input type="text" id="case_number" name="case_number" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="note" class="form-label">Note / Comment</label>
          <textarea id="note" name="note" class="form-control" rows="4" placeholder="Enter your comment or instructions..." required></textarea>
        </div>
        <div class="mb-3">
          <label for="author" class="form-label">Added By</label>
          <input type="text" id="author" name="author" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Add Note</button>
      </form>
    </div>
  </div>
</div>
@endsection