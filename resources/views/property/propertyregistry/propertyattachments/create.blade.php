@extends('layouts.app')
@section('title', 'Add Property Attachment')
@section('content')

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

<div class="container mt-4">
    <form action="{{ route('attachments.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
  <div class="card shadow">
      <div class="card-header bg-light fw-bold">Upload Document</div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Select Property<span class="text-danger">*</span></label>
            <select name="PropertyID" class="form-select" required>
                @foreach ($properties as $property)
                    <option value="{{ $property->Id }}">{{ $property->PropertyName }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Document Title<span class="text-danger">*</span></label>
            <input type="text" class="form-control" placeholder="e.g. Title Deed, Blueprint" name="DocumentTitle">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Document Type<span class="text-danger">*</span></label>
        <select class="form-select" name="DocumentType">
          <option value="">--Select a status--</option>
              @foreach ($documenttypes as $documenttype)
                <option value="{{ $documenttype->ID }}">
                  {{ $documenttype->Description }}
                </option>
              @endforeach
            </select>
          </div>
        <div class="col-md-6">
            <label class="form-label">Upload File<span class="text-danger">*</span></label>
            <small class="text-muted d-block mb-1">Allowed file types: .pdf, .jpg, .jpeg, .png, .docx, .xlsx | Max size: 25MB</small>
            <input type="file" name="file[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Description / Notes </label>
          <textarea class="form-control" rows="2" placeholder="Optional notes..." name="Description"></textarea>
      </div>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <a href="{{ route('attachments.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-success"
          onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Upload Document
        </button>
            </div>
    </form>
    </div>
  </div>
</div>
@endsection
