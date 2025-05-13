@extends('layouts.app')
@section('title', 'renewals')
@section('content')
<div class="container mt-5" style="max-width: 850px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📄 Add New Contract</h3>
    <a href="{{ route('renewals.index') }}" class="btn btn-outline-secondary">← Back to Contracts</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="">
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
          <label for="start_date" class="form-label">Start Date</label>
          <input type="date" name="start_date" id="start_date" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="expiry_date" class="form-label">Expiry Date</label>
          <input type="date" name="expiry_date" id="expiry_date" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="auto_renewal" class="form-label">Auto-Renewal</label>
          <select name="auto_renewal" id="auto_renewal" class="form-select" required>
            <option value="">-- Select Option --</option>
            <option value="Yes">Yes</option>
            <option value="No">No</option>
          </select>
        </div>
        <button type="submit" class="btn btn-success">Add Contract</button>
      </form>
    </div>
  </div>
</div>
@endsection