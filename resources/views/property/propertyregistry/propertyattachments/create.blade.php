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
          <label class="form-label">Upload File</label>
          <input type="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Description / Notes </label>
          <textarea class="form-control" rows="2" placeholder="Optional notes..." name="Description"></textarea>
      </div>
        <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">📎 Upload Document</button>
    </form>
    </div>
  </div>
</div>
@endsection
