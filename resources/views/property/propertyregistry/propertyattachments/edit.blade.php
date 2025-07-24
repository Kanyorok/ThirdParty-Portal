@extends('layouts.app')
@section('title', 'Edit Property Attachment')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📂 Edit Property Attachment</h4>

  <form action="{{ route('attachments.update', $propertyattachments->Id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="card shadow">
      <div class="card-header bg-light fw-bold">✏️ Update Document</div>
      <div class="card-body">
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label">Select Property</label>
            <select name="PropertyID" class="form-select" required>
              @foreach ($properties as $property)
                <option value="{{ $property->Id }}" {{ $property->Id == old('PropertyID', $propertyattachments->PropertyID) ? 'selected' : '' }}>
                  {{ $property->PropertyName }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Document Title</label>
            <input type="text" class="form-control" placeholder="e.g. Title Deed, Blueprint"
              name="DocumentTitle" value="{{ old('DocumentTitle', $propertyattachments->DocumentTitle) }}">
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label">Document Type</label>
            <select class="form-select" name="DocumentType">
              <option value="">--Select a status--</option>
              @foreach ($documenttypes as $documenttype)
                <option value="{{ $documenttype->ID }}" {{ $documenttype->ID == old('DocumentType', $propertyattachments->DocumentType) ? 'selected' : '' }}>
                  {{ $documenttype->Description }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Upload File</label>
            <input type="file" class="form-control" name="DocumentFile"
              accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx">
            @if ($propertyattachments->FilePath)
              <small class="text-muted">Current file: <a href="{{ asset('storage/' . $propertyattachments->FilePath) }}" target="_blank">View</a></small>
            @endif
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Description / Notes</label>
          <textarea class="form-control" rows="2" placeholder="Optional notes..." name="Description">{{ old('Description', $propertyattachments->Description) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary"
          onclick="this.disabled=true; this.innerText='Updating...'; this.form.submit();">💾 Update Document</button>
      </div>
    </div>
  </form>
</div>
@endsection
