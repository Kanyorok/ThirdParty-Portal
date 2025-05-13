@extends('layouts.app')
@section('title', 'casemanagement')
@section('content')
<div class="container mt-5" style="max-width: 900px;">
  <h4 class="mb-4">⚖️ Register New Case</h4>

  <form method="post" action="save_case.php" enctype="multipart/form-data">
    <!-- Case Details -->
    <div class="card mb-4">
      <div class="card-header bg-primary text-white">Case Details</div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">Case Number</label>
          <input type="text" class="form-control" name="case_number" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Case Title</label>
          <input type="text" class="form-control" name="title" required>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Jurisdiction</label>
            <input type="text" class="form-control" name="jurisdiction">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Court</label>
            <input type="text" class="form-control" name="court">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Status</label>
          <select class="form-select" name="status">
            <option>Open</option>
            <option>Closed</option>
            <option>Pending</option>
            <option>Appealed</option>
          </select>
        </div>
      </div>
    </div>
@endsection
