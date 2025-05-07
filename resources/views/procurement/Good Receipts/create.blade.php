@extends('layouts.app')
@section('title', 'New Goods Receipt')
@section('content')
<div class="container mt-5">
  <div class="card shadow rounded-4">
    <div class="card-header bg-success text-white rounded-top-4">
      <h4 class="mb-0">📥 GRN Posting (from PO)</h4>
    </div>
    <div class="card-body">

      <form>
        <!-- GRN & PO Info -->
        <div class="row mb-3">
          <div class="col-md-4">
            <label for="grnNumber" class="form-label">GRN Number</label>
            <input type="text" class="form-control" id="grnNumber" required>
          </div>
          <div class="col-md-4">
            <label for="grnDate" class="form-label">GRN Date</label>
            <input type="date" class="form-control" id="grnDate" required>
          </div>
          <div class="col-md-4">
            <label for="poNumber" class="form-label">Select PO Number</label>
            <select class="form-select" id="poNumber" onchange="loadPOItems()">
              <option value="">-- Select PO --</option>
              <option value="PO1001">PO1001</option>
              <option value="PO1002">PO1002</option>
            </select>
          </div>
        </div>

        <!-- Items Table -->
        <div class="table-responsive mb-4">
          <table class="table table-bordered align-middle text-center" id="itemsTable">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Item Code</th>
                <th>Description</th>
                <th>Item Type</th>
                <th>Qty</th>
                <th>UOM</th>
                <th>Unit Price</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              <!-- Filled via JavaScript -->
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-success px-4">✅ Post GRN</button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- JavaScript for Simulated PO Items -->
<script>
  const poItems = {
    PO1001: [
      { code: 'ITM-001', desc: 'Printer Cartridge', type: 'stock', qty: 10, uom: 'pcs', price: 25 },
      { code: 'ITM-100', desc: 'Office Desk', type: 'asset', qty: 2, uom: 'pcs', price: 150 }
    ],
    PO1002: [
      { code: 'ITM-203', desc: 'Consulting Service', type: 'nonstock', qty: 1, uom: 'job', price: 500 }
    ]
  };

  function loadPOItems() {
    const selectedPO = document.getElementById('poNumber').value;
    const tbody = document.querySelector('#itemsTable tbody');
    tbody.innerHTML = ''; // Clear existing rows

    if (poItems[selectedPO]) {
      poItems[selectedPO].forEach((item, index) => {
        const row = document.createElement('tr');
        const total = item.qty * item.price;
        row.innerHTML = `
          <td>${index + 1}</td>
          <td><input type="text" class="form-control" value="${item.code}" readonly></td>
          <td><input type="text" class="form-control" value="${item.desc}" readonly></td>
          <td><input type="text" class="form-control" value="${item.type}" readonly></td>
          <td><input type="number" class="form-control" value="${item.qty}"></td>
          <td><input type="text" class="form-control" value="${item.uom}" readonly></td>
          <td><input type="number" class="form-control" value="${item.price.toFixed(2)}" step="0.01"></td>
          <td><input type="text" class="form-control" value="${total.toFixed(2)}" readonly></td>
        `;
        tbody.appendChild(row);
      });
    }
  }
</script>
@endsection