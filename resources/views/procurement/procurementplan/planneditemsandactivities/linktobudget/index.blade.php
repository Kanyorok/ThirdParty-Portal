@extends('layouts.app')
@section('title', 'Link Procurement Items to Budget Lines')
@section('content')
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>🛠️ Assign Procurement Method – Draft Plan</h4>
    <a href="/planning" class="btn btn-sm btn-outline-secondary">← Back to Plans</a>
  </div>

  <!-- Plan Summary -->
  <div class="mb-4 p-3 border rounded bg-light">
    <p><strong>Plan:</strong> Annual Procurement Plan - 2025</p>
    <p><strong>Status:</strong> DRAFT</p>
    <p><strong>Total Items:</strong> 15 | <strong>Unassigned Methods:</strong> 9</p>
  </div>

  <form method="POST" action="/planning/assign-methods">
    <div class="table-responsive">
      <table class="table table-bordered align-middle table-hover">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Item</th>
            <th>Branch</th>
            <th>Dept</th>
            <th>Qty</th>
            <th>Est. Cost</th>
            <th>Procurement Method</th>
          </tr>
        </thead>
        <tbody>
          <!-- Sample Row -->
          <tr>
            <td>1</td>
            <td>ICT Equipment</td>
            <td>Nairobi</td>
            <td>ICT</td>
            <td>5</td>
            <td>480,000</td>
            <td>
              <select name="procMethod_301" class="form-select">
                <option selected disabled>Select Method</option>
                <option value="OPEN">Open Tender</option>
                <option value="RFQ">Request for Quotation</option>
                <option value="DIRECT">Direct Procurement</option>
                <option value="RESTRICTED">Restricted Tender</option>
              </select>
              <input type="hidden" name="lineItemIds[]" value="301">
            </td>
          </tr>

          <tr>
            <td>2</td>
            <td>Office Furniture</td>
            <td>Mombasa</td>
            <td>Admin</td>
            <td>10</td>
            <td>200,000</td>
            <td>
              <select name="procMethod_302" class="form-select">
                <option selected disabled>Select Method</option>
                <option value="OPEN">Open Tender</option>
                <option value="RFQ">Request for Quotation</option>
                <option value="DIRECT">Direct Procurement</option>
              </select>
              <input type="hidden" name="lineItemIds[]" value="302">
            </td>
          </tr>
          <!-- Dynamically loaded rows -->
        </tbody>
      </table>
    </div>

    <!-- Submission -->
    <div class="d-flex justify-content-end mt-3">
      <button type="submit" class="btn btn-primary">
        💾 Save Procurement Methods
      </button>
    </div>
  </form>
</div>

@endsection