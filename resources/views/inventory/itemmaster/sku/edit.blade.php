@extends('layouts.app')
@section('title', 'Edit Inventory')
@section('content')
<body class="bg-light">

<div class="container mt-5">
  <div class="card shadow rounded-4">
    <div class="card-header text-dark rounded-top-4" style="background-color: #add8e6;">
      <h4 class="mb-0">Edit SKU Master</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('sku.update', $item->Id) }}" method="POST">
        @csrf 
        @method('PUT')

        <div class="mb-3 row">
            <div class="col-md-4">
                <label for="skuCode" class="form-label">SKU Code</label>
                <input type="text" name="SKUCode" class="form-control" id="skuCode" value="{{ $item->SKUCode }}" readonly>
            </div>

<div class="row mb-3">
    <div class="col-md-4">
        <label for="Category" class="form-label">Category</label>
        <select name="Category" id="Category" class="form-select" required>
            <option value="">-- Select Category --</option>
            @foreach($categories as $category)
                <option value="{{ $category->Id }}"
                    {{ old('Category', $item->item->category->parent ? $item->item->category->parent->Id : $item->item->category->Id) == $category->Id ? 'selected' : '' }}>
                    {{ $category->Name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label for="Subcategory" class="form-label">Subcategory</label>
        <select name="Subcategory" id="Subcategory" class="form-select">
            <option value="">-- Select Subcategory --</option>
            @if($item->item->category->parent)
                @foreach($item->item->category->parent->children as $subcat)
                    <option value="{{ $subcat->Id }}"
                        {{ old('Subcategory', $item->item->category->Id) == $subcat->Id ? 'selected' : '' }}>
                        {{ $subcat->Name }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>
<div class="col-md-4">
    <label for="ItemID" class="form-label">Item</label>
    <select name="ItemID" id="Item" class="form-select" required>
        <option value="">-- Select Item --</option>
        @foreach($items as $itm)
            <option value="{{ $itm->Id }}" {{ old('ItemID', $item->ItemID) == $itm->Id ? 'selected' : '' }}>
                {{ $itm->ItemName }}
            </option>
        @endforeach
    </select>
</div>

    <div class="row mb-3">
    <div class="col-md-6">
        <label for="Branch" class="form-label">Branch</label>
        <select name="Branch" id="Branch" class="form-select" required>
            <option value="">-- Select Branch --</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->Id }}" {{ old('Branch', $item->Branch) == $branch->Id ? 'selected' : '' }}>
                    {{ $branch->Name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label for="Store" class="form-label">Store</label>
        <select name="Store" id="Store" class="form-select" required>
            <option value="">-- Select Store --</option>
            @foreach($stores as $store)
                <option value="{{ $store->Id }}" {{ old('Store', $item->Store) == $store->Id ? 'selected' : '' }}>
                    {{ $store->StoreName }}
                </option>
            @endforeach
        </select>
    </div>
</div>

        <div class="mb-3">
            @foreach(['Batch', 'Serial', 'Perishable', 'Saleable', 'Purchasable'] as $field)
            <div class="form-check form-check-inline">
                <input type="hidden" name="{{ $field }}" value="0">
                <input class="form-check-input" type="checkbox" name="{{ $field }}" value="1" {{ $item->$field ? 'checked' : '' }}>
                <label class="form-check-label">Is {{ ucfirst($field) }}</label>
        </div>
            @endforeach
        </div>

       
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="currentQty" class="form-label">Current Qty</label>
                <input type="number" name="CurrentQty" class="form-control" id="currentQty" value="{{ $item->CurrentQty }}" required>
            </div>
            <div class="col-md-4">
                <label for="minStockLevel" class="form-label">Min Stock Level</label>
                <input type="number" name="Min" class="form-control" id="minStockLevel" value="{{ $item->Min }}">
            </div>
            <div class="col-md-4">
                <label for="reorderQty" class="form-label">Reorder Qty</label>
                <input type="number" name="Reorder" class="form-control" id="reorderQty" value="{{ $item->Reorder }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="maxStockLevel" class="form-label">Max Stock Level</label>
                <input type="number" name="Max" class="form-control" id="maxStockLevel" value="{{ $item->Max }}">
            </div>
            <div class="col-md-6">
                <label for="lastReceivedDate" class="form-label">Last Received Date</label>
                <input type="date" name="LastReceived" class="form-control" id="lastReceivedDate" value="{{ $item->LastReceived }}">
            </div>
        </div>

<div class="form-check mb-4">
  <input type="hidden" name="Status" value="0">
  <input class="form-check-input" type="checkbox" name="Status" value="1" id="Status" {{ $item->Status ? 'checked' : '' }}>
  <label class="form-check-label" for="Status">Is Active</label>
</div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-warning px-4">Update Item</button>
            <a href="{{ route('sku.index') }}" class="btn btn-danger px-4 ms-2">Cancel</a>
        </div>

      </form>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const branchSelect = document.getElementById('Branch');
    const storeSelect = document.getElementById('Store');
    const selectedStore = "{{ old('Store', $item->Store) }}";

    function loadStores(branchId, selectedStoreId = null) {
        storeSelect.innerHTML = '<option value="">-- Select Store --</option>';
        if (branchId) {
            fetch(`/inventory/get-stores?BranchID=${branchId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        const option = document.createElement('option');
                        option.value = "";
                        option.text = "No stores found for this branch";
                        storeSelect.appendChild(option);
                    } else {
                        data.forEach(store => {
                            const option = document.createElement('option');
                            option.value = store.Id;
                            option.text = store.StoreName;
                            if (store.Id == (selectedStoreId ?? selectedStore)) {
                                option.selected = true;
                            }
                            storeSelect.appendChild(option);
                        });
                    }
                });
        }
    }

    branchSelect.addEventListener('change', function () {
        loadStores(this.value);
    });

    // On page load, trigger loading if branch is already selected
    if (branchSelect.value) {
        loadStores(branchSelect.value, selectedStore);
    }
});
</script>
@endsection
