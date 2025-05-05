@extends('layouts.app')
@section('title', 'Create Journal Batch ')
@section('content')
<div class="container">
    <div class="card shadow rounded-3">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Create Journal Batch</h5>
      </div>
      <div class="card-body">
        <form id="journalBatchForm">
          <div class="mb-3">
            <label for="batchNo" class="form-label">Batch Number</label>
            <input type="text" class="form-control" id="batchNo" name="batchNo" required>
          </div>

          <div class="mb-3">
            <label for="batchDate" class="form-label">Batch Date</label>
            <input type="date" class="form-control" id="batchDate" name="batchDate" required>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
          </div>

          <div class="mb-3">
            <label for="status" class="form-label">Posting Status</label>
            <select class="form-select" id="status" name="status" required>
              <option value="">Select Status</option>
              <option value="Draft">Draft</option>
              <option value="Posted">Posted</option>
              <option value="Reversed">Reversed</option>
            </select>
          </div>

          <div class="d-flex justify-content-end">
            <button type="reset" class="btn btn-secondary me-2">Clear</button>
            <button type="submit" class="btn btn-success">Save Batch</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection