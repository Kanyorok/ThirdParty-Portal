@extends('layouts.app')
@section('title', 'contractapproval')
@section('content')
<div class="container mt-5" style="max-width: 850px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📝 Start Contract Approval</h3>
    <a href="{{ route('contractapproval.index') }}" class="btn btn-outline-secondary">← Back to Workflow</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="" enctype="multipart/form-data">
        <div class="mb-3">
          <label for="contract_name" class="form-label">Contract Name</label>
          <input type="text" name="contract_name" id="contract_name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="uploaded_by" class="form-label">Initiated By</label>
          <input type="text" name="uploaded_by" id="uploaded_by" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="upload_file" class="form-label">Upload Contract Document</label>
          <input type="file" name="upload_file" id="upload_file" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="comments" class="form-label">Comments / Notes</label>
          <textarea name="comments" id="comments" class="form-control" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Submit for Review</button>
      </form>
    </div>
  </div>
</div>
@endsection