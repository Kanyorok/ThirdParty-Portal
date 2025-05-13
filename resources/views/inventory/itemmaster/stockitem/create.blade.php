@extends('layouts.app')
@section('title', 'Create New Inventory')
@section('content')
<body class="bg-light">

<div class="container mt-5">
  <div class="card shadow rounded-4">
    <div class="card-header bg-primary text-white rounded-top-4">
      <h4 class="mb-0">➕ Add SKU Master</h4>
    </div>
    <div class="card-body">
      <form>

        <div class="mb-3">
        <div class="col-md-4">
          <label for="skuCode" class="form-label">SKU Code</label>
          <input type="text" class="form-control" id="skuCode" required>
        </div>
        <div class="col-md-4">
          <label for="Item" class="form-label">Item Type</label>
          <select class="form-select" id="itemType">
            <option selected disabled>Select Item</option>
            <option value="1">Stapler</option>
            <option value="2">Printer Paper</option>
            <option value="3">glue stick</option>
            <option value="3">Envelopes</option>
          </select>
        </div>
       
        <div class="mb-3">
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="isBatchTracked">
            <label class="form-check-label" for="isBatchTracked">Is Batch Tracked</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="isSerialTracked">
            <label class="form-check-label" for="isSerialTracked">Is Serial Tracked</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="isPerishable">
            <label class="form-check-label" for="isPerishable">Is Perishable</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="isSaleable">
            <label class="form-check-label" for="isSaleable">Is Saleable</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="isPurchasable">
            <label class="form-check-label" for="isPurchasable">Is Purchasable</label>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label for="storeID" class="form-label">Store</label>
            <select class="form-select" id="storeID" required>
              <option value="">-- Select Store --</option>
              <option value="1">Head Store</option>
              <option value="2">Store 1</option>
              <option value="3">Store 2</option>
              <option value="3">Store 3</option>
              <!-- Dynamically populated -->
            </select>
          </div>
          <div class="col-md-6">
            <label for="branchID" class="form-label">Branch</label>
            <select class="form-select" id="branchID" required>
              <option value="">-- Select Branch --</option>
              <option value="2">Branch 1</option>
              <option value="3">Branch 2</option>
              <option value="3">Branch 3</option>
              <!-- Dynamically populated -->
            </select>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-4">
            <label for="currentQty" class="form-label">Current Qty</label>
            <input type="number" class="form-control" id="currentQty" value="0" required>
          </div>
          <div class="col-md-4">
            <label for="minStockLevel" class="form-label">Min Stock Level</label>
            <input type="number" class="form-control" id="minStockLevel" value="0">
          </div>
          <div class="col-md-4">
            <label for="reorderQty" class="form-label">Reorder Qty</label>
            <input type="number" class="form-control" id="reorderQty" value="0">
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label for="maxStockLevel" class="form-label">Max Stock Level</label>
            <input type="number" class="form-control" id="maxStockLevel" value="0">
          </div>
          <div class="col-md-6">
            <label for="lastReceivedDate" class="form-label">Last Received Date</label>
            <input type="date" class="form-control" id="lastReceivedDate">
          </div>
        </div>

        <div class="form-check mb-4">
          <input class="form-check-input" type="checkbox" id="isActive" checked>
          <label class="form-check-label" for="isActive">Is Active</label>
        </div>

        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-success px-4">Save Item</button>
        </div>
        <div class="d-flex justify-content-end">
          <button type="Cancel" class="btn btn-success px-4">Cancel</button>
        </div>

      </form>
    </div>
  </div>
</div>
@endsection