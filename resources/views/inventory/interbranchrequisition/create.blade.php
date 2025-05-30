@extends('layouts.app')
@section('title', 'New Inter-Branch Requisition')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🔄 New Inter-Branch Requisition</h4>

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">➕ Request Stock from Another Branch</div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Requesting Branch</label>
          <select class="form-select">
            <option>Branch A</option>
            <option>Branch B</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">To Branch</label>
          <select class="form-select">
            <option>Branch B</option>
            <option>Branch A</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Date</label>
          <input type="date" class="form-control" value="2025-05-02">
        </div>
      </div>

      <!-- Items -->
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Item Code</th>
              <th>Item Name</th>
              <th>UOM</th>
              <th>Requested Qty</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td>ITM-001</td>
              <td>A4 Paper</td>
              <td>pcs</td>
              <td><input type="number" class="form-control" value="50"></td>
              <td><input type="text" class="form-control" placeholder="Optional"></td>
            </tr>
            <tr>
              <td>2</td>
              <td>ITM-002</td>
              <td>Printer</td>
              <td>pcs</td>
              <td><input type="number" class="form-control" value="3"></td>
              <td><input type="text" class="form-control" placeholder="Urgent"></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="text-end">
        <button class="btn btn-success">📤 Submit Requisition</button>
      </div>
    </div>
  </div>
</div>
@endsection