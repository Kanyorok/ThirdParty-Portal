@extends('layouts.app')
@section('title', 'Edit Draft Plan Items')
@section('content')
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>✏️ Edit Draft Plan Items – Annual Procurement Plan 2025</h4>
    <a href="/planning" class="btn btn-sm btn-outline-secondary">← Back to Dashboard</a>
  </div>

  <!-- Summary -->
  <div class="alert alert-info">
    <strong>Status:</strong> DRAFT | <strong>Total Items:</strong> 20 | <strong>Editable:</strong> Yes
  </div>

  <form method="POST" action="/planning/update-draft-items">
    <!-- You’ll add CSRF and hidden plan ID when backend is ready -->
    
    <div class="table-responsive">
      <table class="table table-bordered align-middle table-hover">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Item</th>
            <th>From Branch</th>
            <th>Original Qty</th>
            <th>Planned Qty</th>
            <th>Unit Cost</th>
            <th>Total</th>
            <th>Remarks</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <!-- Sample Editable Row -->
          <tr>
            <td>1</td>
            <td>Desktop Computers</td>
            <td>Nairobi (ICT)</td>
            <td>120</td>
            <td>
              <input type="number" class="form-control form-control-sm" name="qty_501" value="100" min="1">
            </td>
            <td>
              <input type="number" class="form-control form-control-sm" name="unitCost_501" value="30000" min="0">
            </td>
            <td><span class="text-muted">3,000,000</span></td>
            <td>
              <textarea name="remarks_501" class="form-control form-control-sm" rows="1" placeholder="e.g. Reduced to meet cap"></textarea>
            </td>
            <td>
              <button type="submit" name="removeItem" value="501" class="btn btn-sm btn-outline-danger">🗑 Remove</button>
              <input type="hidden" name="lineItemIds[]" value="501">
            </td>
          </tr>

          <!-- More dynamic rows -->
        </tbody>
      </table>
    </div>

    <!-- Save All Button -->
    <div class="d-flex justify-content-end mt-3">
      <button type="submit" class="btn btn-success">
        💾 Save Changes to Draft Plan
      </button>
    </div>
  </form>
</div>

@endsection