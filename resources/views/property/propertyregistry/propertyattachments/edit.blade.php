@extends('layouts.app')
@section('title', 'Edit Property Attachment')
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

  <form action="{{ route('attachments.update', $propertyattachments->Id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="card shadow">
        <div class="card-header bg-light fw-bold">Update Document</div>
      <div class="card-body">
        <div class="row g-3 mb-3">
          <div class="col-md-6">
              <label class="form-label">Select Property<span class="text-danger">*</span></label>
            <select name="PropertyID" class="form-select" required>
              @foreach ($properties as $property)
                <option value="{{ $property->Id }}" {{ $property->Id == old('PropertyID', $propertyattachments->PropertyID) ? 'selected' : '' }}>
                  {{ $property->PropertyName }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
              <label class="form-label">Document Title<span class="text-danger">*</span></label>
            <input type="text" class="form-control" placeholder="e.g. Title Deed, Blueprint"
              name="DocumentTitle" value="{{ old('DocumentTitle', $propertyattachments->DocumentTitle) }}">
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
              <label class="form-label">Document Type<span class="text-danger">*</span></label>
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
                <label class="form-label">Upload File<span class="text-danger">*</span></label>
                <input type="file" name="file[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx">
                <small class="text-muted d-block mb-1">Allowed file types: .pdf, .jpg, .jpeg, .png, .docx, .xlsx | Max size: 25MB</small>
                
            </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Description / Notes</label>
          <textarea class="form-control" rows="2" placeholder="Optional notes..." name="Description">{{ old('Description', $propertyattachments->Description) }}</textarea>
        </div>

          <div class="p-2 border rounded bg-light mt-2">
              @forelse($propertyattachments->documents()->get(['t_Documents.Id', 't_Documents.DocumentId','MimeType','Name']) as $document)
                  {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
              @empty
                  <span class="text-muted">No documents attached.</span>
              @endforelse
          </div>

          <button type="submit" class="btn btn-primary"
                  onclick="this.disabled=true; this.innerText='Updating...'; this.form.submit();">Update Document
          </button>
      </div>
    </div>
  </form>
</div>
@endsection

@section('scripts')
 @include('snippets.actions.preview-files')
@endsection
