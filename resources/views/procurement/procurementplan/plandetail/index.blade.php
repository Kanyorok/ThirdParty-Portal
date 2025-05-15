@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>📊 Procurement Plan Dashboard – Annual Procurement Plan 2025</h4>
    <a href="/planning" class="btn btn-sm btn-outline-secondary">← Back to Plans</a>
  </div>

  <!-- Summary Info -->
  <div class="row mb-4 bg-light border rounded p-3">
    <div class="col-md-3"><strong>Plan Ref:</strong> PLAN/2025/001</div>
    <div class="col-md-3"><strong>Year:</strong> 2025</div>
    <div class="col-md-3"><strong>Status:</strong> <span class="badge bg-warning text-dark">DRAFT</span></div>
    <div class="col-md-3"><strong>Created By:</strong> Procurement Admin</div>
  </div>

  <!-- KPI Cards (Static Example) -->
  <div class="row text-center mb-4">
    <div class="col-md-3"><div class="border p-3 rounded bg-white"><h6>Total Items</h6><h4>20</h4></div></div>
    <div class="col-md-3"><div class="border p-3 rounded bg-white"><h6>Budget Linked</h6><h4 class="text-warning">18 / 20</h4></div></div>
    <div class="col-md-3"><div class="border p-3 rounded bg-white"><h6>Method Assigned</h6><h4 class="text-warning">17 / 20</h4></div></div>
    <div class="col-md-3"><div class="border p-3 rounded bg-white"><h6>Scheduled</h6><h4 class="text-warning">15 / 20</h4></div></div>
  </div>

  <!-- Item List Table (Sample Static Rows) -->
  <div class="table-responsive mb-4">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Item</th>
          <th>Branch</th>
          <th>Qty</th>
          <th>Cost</th>
          <th>Budget Line</th>
          <th>Method</th>
          <th>Schedule</th>
          <th>Status</th>
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
          <td>Q1: 25, Q2: 25, Q3: 25, Q4: 25</td>
          <td><span class="badge bg-success">Ready</span></td>
        </tr>
        <tr>
          <td>2</td>
          <td>Printers</td>
          <td>Kisumu</td>
          <td>10</td>
          <td>400,000</td>
          <td><span class="text-danger">Unlinked</span></td>
          <td>—</td>
          <td><span class="text-danger">Not Scheduled</span></td>
          <td><span class="badge bg-danger">Incomplete</span></td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Submit for Approval Button (Always Shown for Now) -->
  <div class="mt-4 p-4 bg-light border rounded">
    <h5>📤 Submit Plan for Approval</h5>
    <p>This will forward the plan for multi-level approval once you are confident all details are correctly filled.</p>

    <form method="POST" action="/planning/submit-for-approval">
      <!-- You may eventually use a real PlanID here -->
      <input type="hidden" name="planId" value="1">

      <div class="mb-3">
        <label class="form-label">Remarks (Optional)</label>
        <textarea name="remarks" class="form-control" rows="2" placeholder="Finalized and ready for approval."></textarea>
      </div>

      <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">📤 Submit Plan</button>
      </div>
    </form>
  </div>
</div>


@endsection