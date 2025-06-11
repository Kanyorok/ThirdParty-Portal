@extends('layouts.app')
@section('title', 'New Prequalification Period')
@section('content')

<div class="card">
  <div class="card-header bg-primary text-white">➕ New Prequalification Period</div>
  <div class="card-body">

    <form method="POST" action="#">
      {{-- CSRF will go here later --}}
      
      <div class="mb-3">
        <label class="form-label">Round Name</label>
        <input type="text" name="RoundName" class="form-control" placeholder="e.g. 2025 Annual Supplier Prequalification">
      </div>

      <div class="mb-3">
        <label class="form-label">Start Date</label>
        <input type="date" name="StartDate" class="form-control">
      </div>

      <div class="mb-3">
        <label class="form-label">End Date</label>
        <input type="date" name="EndDate" class="form-control">
      </div>

      <div class="mb-3">
        <label class="form-label">Description / Notes</label>
        <textarea name="Description" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
      </div>
<div class="mb-3">
  <label for="max_vendors" class="form-label">Max Vendors to Prequalify per Category</label>
  <input type="number" name="max_vendors" class="form-control" placeholder="e.g., 10">
</div>
      <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="Status" class="form-select">
          <option value="Draft" selected>Draft</option>
          <option value="Open">Open</option>
          <option value="Closed">Closed</option>
        </select>
      </div>

      <div class="text-end">
        <button type="submit" class="btn btn-success">💾 Save Round</button>
        <a href="{{ route('preqrounds.index') }}" class="btn btn-secondary">🔙 Back</a>
      </div>

    </form>
  </div>
</div>

@endsection
