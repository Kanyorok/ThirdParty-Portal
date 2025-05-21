@extends('layouts.app')
@section('title', 'uploadmanagement')
@section('content')
<div class="container mt-5" style="max-width: 700px;">
  <h4 class="mb-4">📤 Bulk Upload Documents</h4>

  <form action="upload_handler.php" method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label for="documents" class="form-label">Select Files</label>
      <input class="form-control" type="file" name="documents[]" id="documents" multiple required>
      <small class="text-muted">You can select and upload multiple documents at once.</small>
    </div>

    <div class="mb-3">
      <label for="upload_by" class="form-label">Uploaded By</label>
      <input type="text" class="form-control" name="upload_by" placeholder="e.g., John Doe">
    </div>

    <button type="submit" class="btn btn-primary">Upload Files</button>
  </form>
</div>
@endsection