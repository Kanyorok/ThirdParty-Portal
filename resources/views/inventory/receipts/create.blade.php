@extends('layouts.app')
@section('title', 'Create New Inventory')
@section('content')
<div class="container bg-white shadow rounded p-4">
    <h4 class="mb-4">Goods Receipt Form</h4>

    <form>
      <div class="row mb-3">
        <div class="col-md-4">
          <label for="receiptDate" class="form-label">Receipt Date</label>
          <input type="date" class="form-control" id="receiptDate" required>
        </div>
        <div class="col-md-4">
          <label for="supplier" class="form-label">Supplier</label>
          <input type="text" class="form-control" id="supplier" placeholder="Supplier Name" required>
        </div>
        <div class="col-md-4">
          <label for="poNumber" class="form-label">PO Number</label>
          <input type="text" class="form-control" id="poNumber" placeholder="PO-12345">
        </div>
      </div>

      <div class="table-responsive mb-3">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Item Code</th>
              <th>Item Name</th>
              <th>Quantity</th>
              <th>UOM</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td><input type="text" class="form-control" placeholder="ITM-001"></td>
              <td><input type="text" class="form-control" placeholder="Item Description"></td>
              <td><input type="number" class="form-control" placeholder="0"></td>
              <td>
                <select class="form-select">
                  <option>pcs</option>
                  <option>kg</option>
                  <option>litres</option>
                </select>
              </td>
              <td><input type="text" class="form-control" placeholder="Optional"></td>
            </tr>
            <!-- More rows can be added dynamically -->
          </tbody>
        </table>
      </div>

      <div class="mb-3">
        <label for="receivedBy" class="form-label">Received By</label>
        <input type="text" class="form-control" id="receivedBy" placeholder="e.g. Daniel Mbugua" required>
      </div>

      <button type="submit" class="btn btn-primary">✅ Submit Receipt</button>
    </form>
  </div>
@endsection