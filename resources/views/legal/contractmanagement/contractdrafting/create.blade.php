@extends('layouts.app')
@section('title', 'contractdrafting')
@section('content')
<div class="container mt-5" style="max-width: 850px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📝 Initiate New Contract</h3>
    <a href="{{ route('contractdrafting.index') }}" class="btn btn-outline-secondary">← Back to Contracts</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="">
        <div class="mb-3">
          <label for="template" class="form-label">Select Template</label>
          <select name="template" id="template" class="form-select" required>
            <option value="">-- Choose Template --</option>
            <option value="nda">Non-Disclosure Agreement (NDA)</option>
            <option value="mou">Memorandum of Understanding (MoU)</option>
            <option value="lease">Lease Agreement</option>
            <!-- Add more templates as needed -->
          </select>
        </div>
        <div class="mb-3">
          <label for="contract_title" class="form-label">Contract Title</label>
          <input type="text" name="contract_title" id="contract_title" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="initiated_by" class="form-label">Initiated By</label>
          <input type="text" name="initiated_by" id="initiated_by" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="parties_involved" class="form-label">Parties Involved</label>
          <input type="text" name="parties_involved" id="parties_involved" class="form-control" placeholder="e.g., Company A, Company B" required>
        </div>
        <div class="mb-3">
          <label for="contract_terms" class="form-label">Contract Terms</label>
          <textarea name="contract_terms" id="contract_terms" class="form-control" rows="5" placeholder="Enter specific terms or modifications to the template..."></textarea>
        </div>
        <button type="submit" class="btn btn-success">Create Contract</button>
      </form>
    </div>
  </div>
</div>
@endsection
