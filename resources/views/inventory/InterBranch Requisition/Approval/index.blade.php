@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">✅ Approve Inter-Branch Requisition</h4>

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">🔍 Requisition Details</div>
    <div class="card-body">
      <div class="row mb-3">
        <div class="col-md-4"><strong>Requisition No:</strong> REQ-2025-0012</div>
        <div class="col-md-4"><strong>Date:</strong> 2025-05-02</div>
        <div class="col-md-4"><strong>Status:</strong> <span class="badge bg-warning">Pending Approval</span></div>
      </div>
      <div class="row mb-3">
        <div class="col-md-4"><strong>From Branch:</strong> Branch A</div>
        <div class="col-md-4"><strong>To Branch:</strong> Branch B</div>
        <div class="col-md-4"><strong>Requested By:</strong> Moses K.</div>
      </div>

      <hr>
      <h5 class="mb-3">📦 Requested Items</h5>

      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Item Code</th>
              <th>Item Name</th>
              <th>UOM</th>
              <th>Requested Qty</th>
              <th>Approved Qty</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td>ITM-001</td>
              <td>A4 Paper</td>
              <td>pcs</td>
              <td>50</td>
              <td><input type="number" class="form-control" value="50"></td>
              <td><input type="text" class="form-control" placeholder="Optional remarks"></td>
            </tr>
            <tr>
              <td>2</td>
              <td>ITM-002</td>
              <td>Printer</td>
              <td>pcs</td>
              <td>3</td>
              <td><input type="number" class="form-control" value="3"></td>
              <td><input type="text" class="form-control" placeholder="Optional remarks"></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Approver Remarks</label>
          <textarea class="form-control" rows="2" placeholder="Optional notes..."></textarea>
        </div>
        <div class="col-md-6 text-end d-flex align-items-end justify-content-end">
          <button class="btn btn-danger me-2">❌ Reject</button>
          <button class="btn btn-success">✅ Approve & Forward</button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection