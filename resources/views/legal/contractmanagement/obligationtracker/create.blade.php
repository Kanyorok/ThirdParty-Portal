@extends('layouts.app')
@section('title', 'obligationtracker')
@section('content')
<div class="container mt-5" style="max-width: 850px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📌 Add New Obligation</h3>
    <a href="index.php" class="btn btn-outline-secondary">← Back to Tracker</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="">
        <div class="mb-3">
          <label for="contract_name" class="form-label">Contract Name</label>
          <input type="text" name="contract_name" id="contract_name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="obligation_type" class="form-label">Obligation Type</label>
          <select name="obligation_type" id="obligation_type" class="form-select" required>
            <option value="">-- Select Type --</option>
            <option value="Deliverable">Deliverable</option>
            <option value="Penalty">Penalty</option>
            <option value="Commitment">Commitment</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="description" class="form-label">Description</label>
          <textarea name="description" id="description" class="form-control" rows="3" required></textarea>
        </div>
        <div class="mb-3">
          <label for="due_date" class="form-label">Due Date</label>
          <input type="date" name="due_date" id="due_date" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="responsible_party" class="form-label">Responsible Party</label>
          <input type="text" name="responsible_party" id="responsible_party" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="status" class="form-label">Status</label>
          <select name="status" id="status" class="form-select" required>
            <option value="">-- Select Status --</option>
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Completed">Completed</option>
            <option value="Overdue">Overdue</option>
          </select>
        </div>
        <button type="submit" class="btn btn-success">Add Obligation</button>
      </form>
    </div>
  </div>
</div>
@endsection