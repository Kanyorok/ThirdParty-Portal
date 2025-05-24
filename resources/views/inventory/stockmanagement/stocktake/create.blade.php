@extends('layouts.app')
@section('title', 'Physical Stock Take')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📝 Physical Stock Take</h4>

  <!-- Header Info -->
  <div class="row mb-3">
    <div class="col-md-3">
      <label class="form-label">📍 Branch</label>
      <select class="form-select" id="branchSelect">
        <option value="">Select Branch</option>
        <option value="branchA">Branch A</option>
        <option value="branchB">Branch B</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">🏢 Store</label>
      <select class="form-select" id="storeSelect">
        <option value="">Select Store</option>
        <option value="store1">Main Store</option>
        <option value="store2">Back Store</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">🧑‍💼 Counted By</label>
      <input type="text" class="form-control" id="countedBy" placeholder="Enter name">
    </div>
    <div class="col-md-3">
      <label class="form-label">📅 Count Date</label>
      <input type="date" class="form-control" id="countedDate" value="2025-05-02">
    </div>
  </div>

  <!-- Items Grid -->
  <div class="table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Item Code</th>
          <th>Item Name</th>
          <th>System Qty</th>
          <th>Counted Qty</th>
          <th>Variance</th>
          <th>Remarks</th>
        </tr>
      </thead>
      <tbody id="stockTakeBody">
        <tr>
          <td>1</td>
          <td>ITM-001</td>
          <td>A4 Paper</td>
          <td><span class="system-qty">120</span></td>
          <td><input type="number" class="form-control counted-qty" value="120"></td>
          <td><span class="variance fw-bold text-danger">0</span></td>
          <td><input type="text" class="form-control" placeholder="Optional"></td>
        </tr>
        <tr>
          <td>2</td>
          <td>ITM-002</td>
          <td>Toner Cartridge</td>
          <td><span class="system-qty">10</span></td>
          <td><input type="number" class="form-control counted-qty" value="10"></td>
          <td><span class="variance fw-bold text-danger">0</span></td>
          <td><input type="text" class="form-control" placeholder="Optional"></td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="text-end">
    <button class="btn btn-success mt-3">✅ Submit Stock Count</button>
  </div>
</div>

<script>
  document.querySelectorAll('.counted-qty').forEach((input, index) => {
    input.addEventListener('input', function () {
      const row = input.closest('tr');
      const systemQty = parseFloat(row.querySelector('.system-qty').innerText) || 0;
      const countedQty = parseFloat(input.value) || 0;
      const variance = countedQty - systemQty;
      row.querySelector('.variance').innerText = variance;
    });
  });
</script>
@endsection