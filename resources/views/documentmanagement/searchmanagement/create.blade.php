@extends('layouts.app')
@section('title', 'searchmanagement')
@section('content')
<div class="container mt-5" style="max-width: 900px;">
  <h4 class="mb-4">🔍 Search & Advanced Filter</h4>
  
  <form method="GET" action="index.php">
    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Keyword</label>
        <input type="text" name="keyword" class="form-control" placeholder="Enter keyword...">
      </div>
      <div class="col-md-6">
        <label class="form-label">Tags</label>
        <input type="text" name="tags" class="form-control" placeholder="e.g., insurance, tax">
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Linked Entity</label>
        <input type="text" name="entity" class="form-control" placeholder="Department, Person, etc.">
      </div>
      <div class="col-md-6">
        <label class="form-label">Uploaded By</label>
        <input type="text" name="uploaded_by" class="form-control" placeholder="Uploader name">
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Start Date</label>
        <input type="date" name="start_date" class="form-control">
      </div>
      <div class="col-md-6">
        <label class="form-label">End Date</label>
        <input type="date" name="end_date" class="form-control">
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Document Type</label>
        <select name="doc_type" class="form-select">
          <option value="">-- All Types --</option>
          <option value="policy">Policy</option>
          <option value="contract">Contract</option>
          <option value="invoice">Invoice</option>
          <option value="certificate">Certificate</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Shape</label>
        <select name="shape" class="form-select">
          <option value="">-- Any Shape --</option>
          <option value="pdf">PDF</option>
          <option value="image">Image</option>
          <option value="word">Word Document</option>
        </select>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Search</button>
  </form>
</div>
@endsection