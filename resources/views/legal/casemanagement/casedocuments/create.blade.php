@extends('layouts.app')
@section('title', 'casedocuments')
@section('content')
<div class="container mt-5" style="max-width: 800px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📤 Upload Case Document</h3>
    <a href="{{ route('casedocuments.index') }}" class="btn btn-outline-secondary">← Back to Document List</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label for="case_number" class="form-label">Case Number</label>
          <input type="text" id="case_number" name="case_number" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="document_type" class="form-label">Document Type</label>
          <select id="document_type" name="document_type" class="form-select" required>
            <option value="">-- Select Type --</option>
            <option value="Contract">Contract</option>
            <option value="Pleading">Pleading</option>
            <option value="Judgment">Judgment</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="upload_file" class="form-label">Select File</label>
          <input type="file" id="upload_file" name="upload_file" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Upload Document</button>
      </form>
    </div>
  </div>
</div>
@endsection