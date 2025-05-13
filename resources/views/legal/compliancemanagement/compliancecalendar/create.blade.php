@extends('layouts.app')
@section('title', 'compliancecalendar')
@section('content')
<div class="container mt-5" style="max-width: 850px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📄 Add Compliance Deadline</h3>
    <a href="{{ route('compliancecalendar.index') }}" class="btn btn-outline-secondary">← Back to Calendar</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="">
        <div class="mb-3">
          <label for="compliance_title" class="form-label">Compliance Title</label>
          <input type="text" name="compliance_title" id="compliance_title" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="compliance_type" class="form-label">Compliance Type</label>
          <select name="compliance_type" id="compliance_type" class="form-select" required>
            <option value="">-- Select Type --</option>
            <option value="Return Filing">Return Filing</option>
            <option value="Audit">Audit</option>
            <option value="License Renewal">License Renewal</option>
            <!-- Add more types as needed -->
          </select>
        </div>
        <div class="mb-3">
          <label for="due_date" class="form-label">Due Date</label>
          <input type="date" name="due_date" id="due_date" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="responsible_person" class="form-label">Responsible Person</label>
          <input type="text" name="responsible_person" id="responsible_person" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="status" class="form-label">Status</label>
          <select name="status" id="status" class="form-select" required>
            <option value="">-- Select Status --</option>
            <option value="Pending">Pending</option>
            <option value="Completed">Completed</option>
            <option value="Overdue">Overdue</option>
          </select>
        </div>
        <button type="submit" class="btn btn-success">Add Deadline</button>
      </form>
    </div>
  </div>
</div>
@endsection