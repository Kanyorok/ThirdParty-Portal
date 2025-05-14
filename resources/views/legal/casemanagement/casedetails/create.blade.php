@extends('layouts.app')
@section('title', 'casedetails')
@section('content')
<div class="container mt-5" style="max-width: 800px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>➕ Add Case Details</h3>
    <a href="{{ route('casedetails.index') }}" class="btn btn-outline-secondary">← Back to Case List</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="" method="POST">
        <div class="mb-3">
          <label for="case_number" class="form-label">Case Number</label>
          <input type="text" id="case_number" name="case_number" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="parties" class="form-label">Parties Involved</label>
          <input type="text" id="parties" name="parties" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="jurisdiction" class="form-label">Jurisdiction</label>
          <input type="text" id="jurisdiction" name="jurisdiction" class="form-control">
        </div>
        <div class="mb-3">
          <label for="court" class="form-label">Court</label>
          <input type="text" id="court" name="court" class="form-control">
        </div>
        <div class="mb-3">
          <label for="status" class="form-label">Case Status</label>
          <select id="status" name="status" class="form-select">
            <option value="Open">Open</option>
            <option value="Ongoing">Ongoing</option>
            <option value="Closed">Closed</option>
          </select>
        </div>
        <button type="submit" class="btn btn-success">Save Case</button>
      </form>
    </div>
  </div>
</div>
@endsection