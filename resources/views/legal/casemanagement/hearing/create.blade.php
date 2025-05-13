@extends('layouts.app')
@section('title', 'hearing')
@section('content')
<div class="container mt-5" style="max-width: 850px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📅 Add Hearing Schedule</h3>
    <a href="{{ route('hearing.index') }}" class="btn btn-outline-secondary">← Back to Schedule</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="">
        <div class="mb-3">
          <label for="case_number" class="form-label">Case Number</label>
          <input type="text" name="case_number" id="case_number" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="court_date" class="form-label">Court Date</label>
          <input type="date" name="court_date" id="court_date" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="next_hearing" class="form-label">Next Hearing Date</label>
          <input type="date" name="next_hearing" id="next_hearing" class="form-control">
        </div>
        <div class="mb-3">
          <label for="outcome" class="form-label">Outcome</label>
          <textarea name="outcome" id="outcome" class="form-control" placeholder="Summary of hearing outcome" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Add Hearing</button>
      </form>
    </div>
  </div>
</div>
@endsection