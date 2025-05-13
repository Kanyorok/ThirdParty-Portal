@extends('layouts.app')
@section('title', 'Create Tranfer')
@section('content')
<div class="container bg-white shadow rounded p-4">
    <h4 class="mb-4">🛠️ Stock Adjustment Form</h4>

    <form>
      <div class="row mb-3">
        <div class="col-md-4">
          <label for="adjustmentDate" class="form-label">Adjustment Date</label>
          <input type="date" class="form-control" id="adjustmentDate" required>
        </div>
        <div class="col-md-4">
          <label for="store" class="form-label">Store</label>
          <select class="form-select" id="store" required>
            <option selected disabled>Select Store</option>
            <option>Central Warehouse</option>
            <option>Branch A</option>
            <option>Branch B</option>
          </select>
        </div>
        <div class="col-md-4">
          <label for="reason" class="form-label">Adjustment Reason</label>
          <select class="form-select" id="reason" required>
            <option>Damage</option>
            <option>Expired</option>
            <option>Shrinkage</option>
            <option>Stock Found</option>
            <option>Other</option>
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
              <th>Adjustment Qty</th>
              <th>UOM</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td><input type="text" class="form-control" placeholder="ITM-101"></td>
              <td><input type="text" class="form-control" placeholder="Item Name"></td>
              <td><input type="number" class="form-control" placeholder="+/-"></td>
              <td>
                <select class="form-select">
                  <option>pcs</option>
                  <option>kg</option>
                  <option>litres</option>
                </select>
              </td>
              <td><input type="text" class="form-control" placeholder="Optional remarks"></td>
            </tr>
            <!-- More rows can be added dynamically -->
          </tbody>
        </table>
      </div>

      <div class="mb-3">
        <label for="adjustedBy" class="form-label">Adjusted By</label>
        <input type="text" class="form-control" id="adjustedBy" placeholder="e.g. Daniel Mbugua" required>
      </div>

      <button type="submit" class="btn btn-primary">✅ Submit Adjustment</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  @endSection