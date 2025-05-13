@extends('layouts.app')
@section('title', 'contractrepository')
@section('content')
<div class="container mt-5" style="max-width: 850px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📁 Upload New Contract</h3>
    <a href="{{ route('contractrepository.index') }}" class="btn btn-outline-secondary">← Back to Repository</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="" enctype="multipart/form-data">
        <div class="mb-3">
          <label for="contract_title" class="form-label">Contract Title</label>
          <input type="text" name="contract_title" id="contract_title" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="contract_type" class="form-label">Contract Type</label>
          <select name="contract_type" id="contract_type" class="form-select" required>
            <option value="">-- Select Type --</option>
            <option value="NDA">Non-Disclosure Agreement (NDA)</option>
            <option value="MoU">Memorandum of Understanding (MoU)</option>
            <option value="Lease">Lease Agreement</option>
            <!-- Add more types as needed -->
          </select>
        </div>
        <div class="mb-3">
          <label for="parties" class="form-label">Parties Involved</label>
          <input type="text" name="parties" id="parties" class="form-control" placeholder="e.g., Company A, Company B" required>
        </div>
        <div class="mb-3">
          <label for="effective_date" class="form-label">Effective Date</label>
          <input type="date" name="effective_date" id="effective_date" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="expiry_date" class="form-label">Expiry Date</label>
          <input type="date" name="expiry_date" id="expiry_date" class="form-control">
        </div>
        <div class="mb-3">
          <label for="contract_file" class="form-label">Upload Contract Document</label>
          <input type="file" name="contract_file" id="contract_file" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Upload Contract</button>
      </form>
    </div>
  </div>
</div>
@endsection