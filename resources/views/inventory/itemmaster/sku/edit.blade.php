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
                <input type="text" name="SKUCode" class="form-control" id="skuCode" value="{{ $item->SKUCode }}" required>
            </div>
            <div class="col-md-4">
                <label for="itemType" class="form-label">Item Type</label>
                <select class="form-select" name="ItemType" id="itemType">
                    <option disabled>Select Item</option>
                    <option value="Stapler" {{ $item->ItemType == 'Stapler' ? 'selected' : '' }}>Stapler</option>
                    <option value="PrinterPaper" {{ $item->ItemType == 'PrinterPaper' ? 'selected' : '' }}>Printer Paper</option>
                    <option value="GlueStick" {{ $item->ItemType == 'GlueStick' ? 'selected' : '' }}>Glue Stick</option>
                    <option value="Envelopes" {{ $item->ItemType == 'Envelopes' ? 'selected' : '' }}>Envelopes</option>
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
            <div class="col-md-6">
                <label for="storeID" class="form-label">Store</label>
                <select class="form-select" name="Store" id="storeID" required>
                    <option value="">-- Select Store --</option>
                    <option value="HeadStore" {{ $item->Store == 'HeadStore' ? 'selected' : '' }}>Head Store</option>
                    <option value="Store" {{ $item->Store == 'Store1' ? 'selected' : '' }}>Store 1</option>
                    <option value="Store2" {{ $item->Store == 'Store2' ? 'selected' : '' }}>Store 2</option>
                    <option value="Store3" {{ $item->Store == 'Store3' ? 'selected' : '' }}>Store 3</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="branchID" class="form-label">Branch</label>
                <select class="form-select" name="Branch" id="branchID" required>
                    <option value="">-- Select Branch --</option>
                    <option value="1" {{ $item->Branch == 'Branch1' ? 'selected' : '' }}>Branch 1</option>
                    <option value="2" {{ $item->Branch == 'Branch2' ? 'selected' : '' }}>Branch 2</option>
                    <option value="3" {{ $item->Branch == 'Branch3' ? 'selected' : '' }}>Branch 3</option>
                </select>
            </div>
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
@endsection
