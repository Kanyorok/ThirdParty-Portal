@extends('layouts.app')
@section('title', 'Create Tranfer')
@section('content')
<div class="container bg-white shadow rounded p-4">
    <h4 class="mb-4">🔁 Stock Transfer Form</h4>

    <form>
      <div class="row mb-3">
        <div class="col-md-4">
          <label for="transferDate" class="form-label">Transfer Date</label>
          <input type="date" class="form-control" id="transferDate" required>
        </div>
        <div class="col-md-4">
          <label for="fromStore" class="form-label">From Store</label>
          <select class="form-select" id="fromStore" required>
            <option selected disabled>Select Source Store</option>
            <option>Central Warehouse</option>
            <option>Branch A</option>
            <option>Branch B</option>
          </select>
        </div>
        <div class="col-md-4">
          <label for="toStore" class="form-label">To Store</label>
          <select class="form-select" id="toStore" required>
            <option selected disabled>Select Destination Store</option>
            <option>Branch A</option>
            <option>Branch B</option>
            <option>Central Warehouse</option>
          </select>
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
              <td><input type="text" class="form-control" placeholder="Item Name"></td>
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
            <!-- Add JS to dynamically insert more rows -->
          </tbody>
        </table>
      </div>

      <div class="mb-3">
        <label for="transferredBy" class="form-label">Transferred By</label>
        <input type="text" class="form-control" id="transferredBy" placeholder="e.g. Daniel Mbugua" required>
      </div>

      <button type="submit" class="btn btn-primary">✅ Submit Transfer</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@endSection