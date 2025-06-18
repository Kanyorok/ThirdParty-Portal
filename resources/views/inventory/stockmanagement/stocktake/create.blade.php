@extends('layouts.app')
@section('title', 'Physical Stock Take')
@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📝 Physical Stock Take</h4>
  <!-- Header Info -->
   <form action="{{ route('stocktake.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
  <div class="row mb-3">
    <div class="col-md-3">
      <label class="form-label">📍 Branch</label>
            <select name="BranchId" id="branch-select" class="form-select" required>
              <option value="">-- Select Branch --</option>
              @foreach ($branches as $branch)
                <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
              @endforeach
            </select>
</div>
    <div class="col-md-3">
      <label class="form-label">🏢 Store</label>
            <select name="StoreId" id="store-select" class="form-select" required>
              <option value="">-- Select Store --</option>
            </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">🧑‍💼 Counted By</label>
      <input type="text" class="form-control" id="countedBy" placeholder="Enter name"name="CountedBy">
    </div>
    <div class="col-md-3">
      <label class="form-label">📅 Count Date</label>
      <input type="date" class="form-control" id="countedDate" value="2025-05-02"name="CountDate">
    </div>
  </div>
</form>

   <!-- Items Table -->
<div id="items-container" class="mt-4" style="display:none;">
  <h5 class="mb-3">📦 Store Items</h5>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Item Name</th>
        <th>System Quantity</th>
        <th>Counted Quantity</th>
        <th>Variance</th>
        <th>Remarks</th>
      </tr>
    </thead>
    <tbody id="items-table-body">
      <!-- Items will be inserted here dynamically -->
    </tbody>
  </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const branchSelect = document.getElementById('branch-select');
    const storeSelect = document.getElementById('store-select');
    const itemsContainer = document.getElementById('items-container');
    const itemsTableBody = document.getElementById('items-table-body');
    const errorMessage = document.getElementById('error-message');

    branchSelect.addEventListener('change', function () {
        const branchId = this.value;
        storeSelect.innerHTML = '<option value="">-- Select a Store --</option>';
        itemsTableBody.innerHTML = '';
        itemsContainer.style.display = 'none';
        if (errorMessage) errorMessage.textContent = '';

        if (branchId) {
            const url = `{{ route('getstores', ':Id') }}`.replace(':Id', branchId);
            fetch(url)
                .then(response => response.json())
                .then(stores => {
                    if (stores.length === 0) {
                        if (errorMessage) errorMessage.textContent = 'No stores found for this branch.';
                    }
                    stores.forEach(store => {
                        const option = document.createElement('option');
                        option.value = store.Id;
                        option.textContent = store.StoreName;
                        storeSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    if (errorMessage) errorMessage.textContent = 'Error loading stores.';
                    console.error('Error loading stores:', error);
                });
        }
    });

    storeSelect.addEventListener('change', function () {
        const storeId = this.value;
        itemsTableBody.innerHTML = '';
        itemsContainer.style.display = 'none';
        if (errorMessage) errorMessage.textContent = '';

        if (storeId) {
            const url = `{{ route('getstoreitems', ':storeId') }}`.replace(':storeId', storeId);
            fetch(url)
                .then(response => response.json())
                .then(items => {
                    if (items.length === 0) {
                        if (errorMessage) errorMessage.textContent = 'No items found for this store.';
                        return;
                    }
                    items.forEach(item => {
                        const row = document.createElement('tr');

                        // Create cells
                        const nameCell = document.createElement('td');
                        nameCell.textContent = item.ItemName;

                        const qtyCell = document.createElement('td');
                        qtyCell.className = 'system-qty';
                        qtyCell.textContent = item.Quantity;

                        const countedCell = document.createElement('td');
                        const countedInput = document.createElement('input');
                        countedInput.type = 'number';
                        countedInput.className = 'form-control counted-qty';
                        countedInput.name = `CountedQuantity[${item.Id}]`;
                        countedInput.step = 'any';
                        countedCell.appendChild(countedInput);

                        const varianceCell = document.createElement('td');
                        varianceCell.className = 'variance';
                        varianceCell.textContent = '0';

                        const remarksCell = document.createElement('td');
                        const remarksInput = document.createElement('input');
                        remarksInput.type = 'text';
                        remarksInput.className = 'form-control';
                        remarksInput.name = `Remarks[${item.Id}]`;
                        remarksCell.appendChild(remarksInput);

                        // Append cells to row
                        row.appendChild(nameCell);
                        row.appendChild(qtyCell);
                        row.appendChild(countedCell);
                        row.appendChild(varianceCell);
                        row.appendChild(remarksCell);

                        itemsTableBody.appendChild(row);

                        // Add real-time variance calculation
                        countedInput.addEventListener('input', function () {
                            const actualQty = parseFloat(qtyCell.textContent) || 0;
                            const countedQty = parseFloat(countedInput.value) || 0;
                            const variance = countedQty - actualQty;
                            varianceCell.textContent = variance;
                        });
                    });

                    itemsContainer.style.display = 'block';
                })
                .catch(error => {
                    if (errorMessage) errorMessage.textContent = 'Error loading store items.';
                    console.error('Error loading store items:', error);
                });
        }
    });
});
</script>
<!-- Add this somewhere in your HTML for error messages -->
<div id="error-message" style="color:red;"></div>

@endsection