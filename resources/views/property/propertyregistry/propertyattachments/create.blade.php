@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📂 Add Property Attachment</h4>

  <form action="{{ route('attachments.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">➕ Upload Document</div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Select Property</label>
            <select name="PropertyID" class="form-select" required>
              @foreach ($properties as $property)
                <option value="{{ $property->id }}">{{ $property->PropertyName }}</option>
              @endforeach
            </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Document Title</label>
          <input type="text" class="form-control" placeholder="e.g. Title Deed, Blueprint" name="DocumentTitle">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Document Type</label>
          <select class="form-select" name="DocumentType">
            <option>Ownership</option>
            <option>Architectural Plan</option>
            <option>Utility Bill</option>
            <option>Insurance</option>
            <option>Other</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Upload File</label>
          <input type="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Description / Notes</label>
        <textarea class="form-control" rows="2" placeholder="Optional notes..." name="Description"></textarea>
      </div>
      <button class="btn btn-success">📎 Upload Document</button>
      </form>
    </div>
  </div>
</div>
@endsection