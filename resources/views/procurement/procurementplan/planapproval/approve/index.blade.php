@extends('layouts.app')
@section('title', 'Review Procurement Plan')
@section('content')

<div class="container mt-4">

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>🔎 Review Procurement Plan – PLAN/2025/001</h4>
    <span class="badge bg-primary">Logged in as: Budget Officer</span>
  </div>

  <!-- Plan Summary -->
  <div class="row mb-4 bg-light p-3 border rounded">
    <div class="col-md-4"><strong>Title:</strong> Annual Procurement Plan – 2025</div>
    <div class="col-md-4"><strong>Status:</strong> <span class="badge bg-warning text-dark">Pending Approval</span></div>
    <div class="col-md-4"><strong>Current Level:</strong> Level 1 – Budget Review</div>
  </div>

  <!-- Approval Trail -->
  <div class="mb-4">
    <h5>📝 Approval Trail</h5>
    <table class="table table-sm table-bordered">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Role</th>
          <th>Approver</th>
          <th>Action</th>
          <th>Comment</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>Originator</td>
          <td>Procurement Admin</td>
          <td><span class="badge bg-info">Submitted</span></td>
          <td>Verified and ready</td>
          <td>2025-05-13</td>
        </tr>
        <!-- Additional approval rows will be loaded here -->
      </tbody>
    </table>
  </div>

  <!-- Plan Items (Read-Only View) -->
  <div class="mb-4">
    <h5>📋 Plan Items Summary</h5>
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Item</th>
            <th>Branch</th>
            <th>Qty</th>
            <th>Cost</th>
            <th>Budget Line</th>
            <th>Procurement Method</th>
            <th>Schedule</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>Desktop Computers</td>
            <td>Nairobi</td>
            <td>100</td>
            <td>3,000,000</td>
            <td>ICT Equip - 2025</td>
            <td>Open Tender</td>
            <td>Q1–Q4 (25 per Qtr)</td>
          </tr>
          <!-- More items -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- 🟢 Approver Action Form -->
  <div class="card p-4 shadow-sm border rounded">
    <h5>✅ Your Approval Action</h5>

    <form method="POST" action="/approval/submit-decision">
      <!-- Hidden Inputs -->
      <input type="hidden" name="planId" value="1">
      <input type="hidden" name="role" value="Budget Officer">

      <!-- Action -->
      <div class="mb-3">
        <label class="form-label">Action</label>
        <select name="action" class="form-select" required>
          <option disabled selected>-- Select Action --</option>
          <option value="APPROVED">✅ Approve</option>
          <option value="REJECTED">❌ Reject</option>
          <option value="COMMENTED">📝 Comment Only</option>
        </select>
      </div>

      <!-- Comments -->
      <div class="mb-3">
        <label class="form-label">Comment (Required)</label>
        <textarea name="comments" class="form-control" rows="3" placeholder="e.g. Matches budget allocations, approved for next level" required></textarea>
      </div>

      <div class="d-flex justify-content-end">
        <button class="btn btn-success">📤 Submit Decision</button>
      </div>
    </form>
  </div>

  <div class="container mt-4">
  <h5>📘 Approval Log for PLAN/2025/001</h5>

  <table class="table table-bordered table-striped align-middle mt-3">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Role</th>
        <th>Approver</th>
        <th>Action</th>
        <th>Comments</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <!-- Sample Row -->
      <tr>
        <td>1</td>
        <td>Originator</td>
        <td>Procurement Admin</td>
        <td><span class="badge bg-info">Submitted</span></td>
        <td>Verified and ready</td>
        <td>2025-05-13</td>
      </tr>
      <tr>
        <td>2</td>
        <td>Budget Officer</td>
        <td>Mary Njenga</td>
        <td><span class="badge bg-success">Approved</span></td>
        <td>Within allocation. Proceed.</td>
        <td>2025-05-14</td>
      </tr>
      <tr>
        <td>3</td>
        <td>Head of Procurement</td>
        <td>James K.</td>
        <td><span class="badge bg-warning text-dark">Commented</span></td>
        <td>Ensure IT items align with strategy</td>
        <td>2025-05-15</td>
      </tr>
      <!-- Dynamically loaded -->
    </tbody>
  </table>
</div>

</div>



@endsection