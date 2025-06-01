@extends('layouts.app')
@section('title', 'Add GRN')
@section('content')
<div class="container mt-4">
  <h4 class="mb-3"> Add GRN – Goods Received (Stock / Asset Update)</h4>
  {{-- @if (isset($errors))
    @dd($errors)
  @endif --}}
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
      <select id="poSelect" name="poSelectDisplay" class="form-select" onchange="populatePODetails()">
        <option value="">-- Select PO --</option>
        @foreach($Orders as $po)
              <option value="{{ $po->OrderNo }}"
                      data-OrderNo="{{ $po->OrderNo }}"
                      data-remarks="{{ $po->ExtOrdNum }}"
                      data-account-id="{{ $po->AccountID }}"
                      data-lines='@json($po->OrderLines)'>
                  {{ $po->OrderNo }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">PO Reference No:</label>
      <div class="form-control form-control-lg bg-light" id="poDesc">--</div>
    </div>
  </div>

  <form method="POST" action="{{ route('procurementreceipts.store') }}" id="grnForm" onsubmit="return handleFormSubmit()">
    @csrf
    <input type="hidden" name="GRNID" id="grnNoInput">
    <input type="hidden" name="POID" id="poIDInput">
      <input type="hidden" name="SupplierID" id="supplierIdInput">

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
            <th>Transfer To</th>
            <th>Tag Required?</th>
          </tr>
        </thead>
        <tbody id="itemsBody">
          <!-- Item rows load dynamically -->
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      <button type="button" class="btn btn-secondary me-2" onclick="window.location='{{ route('procurementreceipts.index') }}'">Cancel</button>
      <button type="submit" class="btn btn-primary me-2">Save Receipt</button>
    </div>
  </form>
</div>

<script>
function handleFormSubmit() {
  const select = document.getElementById("poSelect");
  const selectedOption = select.options[select.selectedIndex];

  if (!selectedOption || !selectedOption.value) {
    alert("Please select a PO.");
    return false;
  }

  // Manually set the hidden input with the selected PO RequisitionNo
  document.getElementById("poIDInput").value = selectedOption.value;

  return true;
}

function startNewReceipt() {
  const now = new Date();
  const random = Math.floor(Math.random() * 900 + 100);
  const grnNo = `GRN-${now.getFullYear()}${now.getMonth() + 1}${now.getDate()}-${random}`;

  document.getElementById("grnNo").value = grnNo;
  document.getElementById("grnNoInput").value = grnNo;
  document.getElementById("poSelect").disabled = false;
  document.getElementById("itemsBody").innerHTML = "";
  document.getElementById("poDesc").textContent = "--";
}


function populatePODetails() {
  const select = document.getElementById("poSelect");
  const selectedOption = select.options[select.selectedIndex];
  const poId = selectedOption.value;
  const remarks = selectedOption.getAttribute("data-remarks");
    const accountId = selectedOption.getAttribute("data-account-id"); // <-- new
  const lines = JSON.parse(selectedOption.getAttribute("data-lines"));

  document.getElementById("poIDInput").value = poId;
    document.getElementById("supplierIdInput").value = accountId; // <-- set hidden input
  document.getElementById("poDesc").textContent = remarks || "--";

  const itemsBody = document.getElementById("itemsBody");
  itemsBody.innerHTML = "";

  lines.forEach((item, index) => {
    const row = `
      <tr>
        <td><input type="text" class="form-control" name="items[${index}][ItemNo]" value="${item.iStockCodeID}" readonly></td>
        <td>${item.ItemName}</td>
        <td>${item.ItemDescription}</td>
        <td>${item.Category}</td>
        <td>${item.UOM}</td>
        <td><input type="number" class="form-control" name="items[${index}][POQTY]" value="${item.fQuantity}" readonly></td>
        <td><input type="number" class="form-control" name="items[${index}][ReceivedQTY]" value="${item.fQuantity}"></td>
        <td><input type="text" class="form-control" name="items[${index}][TransferTo]" value="${item.InventoryType}" readonly></td>
        <td><input type="hidden" name="items[${index}][TagRequired]" value="0">
        <input type="checkbox" name="items[${index}][TagRequired]" value="1"></td>
      </tr>
    `;
    itemsBody.insertAdjacentHTML('beforeend', row);
  });
}
</script>
@endsection
