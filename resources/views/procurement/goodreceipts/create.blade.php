@extends('layouts.app')
@section('title', 'Add GRN')
@section('content')
<div class="container mt-4">
  <h4 class="mb-3">🧾 Add GRN – Goods Received (Stock / Asset Update)</h4>

  <!-- GRN & PO Details -->
  <div class="row mb-3">
  <div class="col-md-2 mb-2 d-grid">
    <button class="btn btn-primary btn-sm" onclick="startNewReceipt()">New Receipt</button>
  </div>
  <div class="col-md-3 mb-2">
    <label class="form-label">GRN No.</label>
    <input type="text" id="grnNo" class="form-control" readonly placeholder="Auto-generated">
  </div>
  <div class="col-md-3 mb-2">
    <label class="form-label">PO No.</label>
    <select id="poSelect" class="form-select" disabled onchange="populatePODetails()">
      <option value="">-- Select PO --</option>
      <option value="PO-1001">PO-1001</option>
      <option value="PO-1002">PO-1002</option>
    </select>
  </div>
  <div class="col-md-4 mb-2">
    <label class="form-label">PO Description</label>
    <div class="form-control form-control-lg bg-light" id="poDesc" >--</div>
  </div>
</div>

  <!-- Items Table -->
  <div class="table-responsive">
    <table class="table table-bordered" id="itemsTable">
      <thead class="table-light">
        <tr>
          <th>Item No</th>
          <th>Item Name</th>
          <th>Description</th>
          <th>Category</th>
          <th>UOM</th>
          <th>PO Qty</th>
          <th>Received Qty</th>
          <th>Accepted Qty</th>
          <th>Transfer To</th>
          <th>Tag Required?</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="itemsBody">
        <!-- Item rows load dynamically -->
      </tbody>
    </table>
  </div>

  <!-- Links & Options -->
  <div class="mb-3">
    <a href="#" class="me-4">🔍 View Inspection Report</a>
    <a href="#">📄 View PO Details</a>
  </div>

  <div class="form-check form-check-inline">
    <input class="form-check-input" type="checkbox" id="markInventory" checked>
    <label class="form-check-label" for="markInventory">Mark for Inventory Update</label>
  </div>
  <div class="form-check form-check-inline">
    <input class="form-check-input" type="checkbox" id="markAsset" checked>
    <label class="form-check-label" for="markAsset">Mark for Asset Register Update</label>
  </div>

  <!-- Footer Buttons -->
  <div class="mt-4">
    <button class="btn btn-secondary me-2">Cancel</button>
    <button class="btn btn-primary me-2">Save Receipt</button>
    <button class="btn btn-success">Post and Transfer</button>
  </div>
</div>

<script>
// Simulated PO Data
const poData = {
  'PO-1001': {
    description: 'HP Laptops & Office Furniture',
    items: [
      { no: '1', name: 'Laptop', desc: 'HP 840 G5', cat: 'Computer', uom: 'Pieces', qty: 3, transfer: 'Asset' },
      { no: '2', name: 'Table', desc: 'Office Table', cat: 'Furniture', uom: 'Pieces', qty: 10, transfer: 'Inventory' }
    ]
  },
  'PO-1002': {
    description: 'Stationery Supplies',
    items: [
      { no: '1', name: 'Printer Paper', desc: 'A4, 500 Sheets', cat: 'Consumables', uom: 'Reams', qty: 20, transfer: 'Inventory' }
    ]
  }
};

// Start a new receipt
function startNewReceipt() {
  const grnField = document.getElementById("grnNo");
  const poSelect = document.getElementById("poSelect");
  const now = new Date();
  const random = Math.floor(Math.random() * 900 + 100);
  const grnNo = `GRN-${now.getFullYear()}${now.getMonth() + 1}${now.getDate()}-${random}`;

  grnField.value = grnNo;
  poSelect.disabled = false;
  poSelect.focus();
  document.getElementById("itemsBody").innerHTML = "";
  document.getElementById("poDesc").textContent = "--";
}

// Load PO details + items
function populatePODetails() {
  const poNumber = document.getElementById("poSelect").value;
  const descBox = document.getElementById("poDesc");
  const itemsBody = document.getElementById("itemsBody");
  itemsBody.innerHTML = "";

  if (!poNumber || !poData[poNumber]) {
    descBox.textContent = "--";
    return;
  }

  const po = poData[poNumber];
  descBox.textContent = po.description;

  po.items.forEach(item => {
    const row = `
      <tr>
        <td>Item ${item.no}</td>
        <td>${item.name}</td>
        <td>${item.desc}</td>
        <td>${item.cat}</td>
        <td>${item.uom}</td>
        <td>${item.qty}</td>
        <td><input class="form-control" type="number" value="${item.qty}"></td>
        <td><input class="form-control" type="number" value="${item.qty}"></td>
        <td><span class="badge bg-${item.transfer === 'Asset' ? 'info' : 'success'}">${item.transfer}</span></td>
        <td><input type="checkbox"></td>
        <td><a href="#">Enter Tags</a> | <a href="#">View Details</a></td>
      </tr>
    `;
    itemsBody.insertAdjacentHTML('beforeend', row);
  });
}
</script>
@endsection