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
        <form action="{{ route('sku.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
        <div class="col-md-4">
          <label for="itemType" class="form-label">Item Type</label>
          <select class="form-select" name= "ItemType" id="itemType">
            <option selected disabled>Select Item</option>
            <option value="Stapler">Stapler</option>
            <option value="PrinterPaper">Printer Paper</option>
            <option value="GlueStick">glue stick</option>
            <option value="Envelopes">Envelopes</option>
          </select>
        </div>
       
<div class="mb-3">
    <div class="form-check form-check-inline">
    <input type="hidden" name="Batch" value="0">
    <input class="form-check-input" type="checkbox" name="Batch" value="1" {{ isset($item) && $item->Batch ? 'checked' : '' }}>
    <label class="form-check-label">Is Batch Tracked</label>

</div>
    
    <div class="form-check form-check-inline">
        <input type="hidden" name="Serial" value="0">
        <input class="form-check-input" type="checkbox" name="Serial" value="1" {{isset($item) &&  $item->Serial ? 'checked' : '' }}>
        <label class="form-check-label">Is Serial Tracked</label>
    </div>

    <div class="form-check form-check-inline">
          <input type="hidden" name="Perishable" value="0">
          <input class="form-check-input" type="checkbox" name="Perishable" value="1" {{isset($item) &&  $item->Perishable ? 'checked' : '' }}>
          <label class="form-check-label">Is Perishable</label>
    </div>

    <div class="form-check form-check-inline">
          <input type="hidden" name="Saleable" value="0">
          <input class="form-check-input" type="checkbox" name="Saleable" value="1" {{ isset($item) && $item->Saleable ? 'checked' : '' }}>
          <label class="form-check-label">Is Saleable</label>
    </div>

    <div class="form-check form-check-inline">
          <input type="hidden" name="Purchasable" value="0">
          <input class="form-check-input" type="checkbox" name="Purchasable" value="1" {{ isset($item) && $item->Purchasable ? 'checked' : '' }}>
        <label class="form-check-label">Is Purchasable</label>
    </div>
</div>


        <div class="row mb-3">
          <div class="col-md-6">
            <label for="storeID" class="form-label">Store</label>
            <select class="form-select" name= "Store" id="storeID" required>
              <option value="">-- Select Store --</option>
              <option value="HeadStore">Head Store</option>
              <option value="Store1">Store 1</option>
              <option value="Store2">Store 2</option>
              <option value="Store3">Store 3</option>
              <!-- Dynamically populated -->
            </select>
          </div>
          <div class="col-md-6">
            <label for="branchID" class="form-label">Branch</label>
            <select class="form-select" name="Branch" id="branchID" required>
              <option value="">-- Select Branch --</option>
              <option value="Branch1">Branch 1</option>
              <option value="Branch2">Branch 2</option>
              <option value="Branch3">Branch 3</option>
              <!-- Dynamically populated -->
            </select>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-4">
            <label for="currentQty" class="form-label">Current Qty</label>
            <input type="number" class="form-control" name= "CurrentQty" id="currentQty" value="0" required>
          </div>
          <div class="col-md-4">
            <label for="Min" class="form-label">Min Stock Level</label>
            <input type="number" class="form-control" name="Min" id="Min" value="0">
          </div>
          <div class="col-md-4">
            <label for="Reorder" class="form-label">Reorder Qty</label>
            <input type="number" class="form-control" name="Reorder" id="Reorder" value="0">
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label for="Max" class="form-label">Max Stock Level</label>
            <input type="number" class="form-control" name="Max" id="Max" value="0">
          </div>
          <div class="col-md-6">
            <label for="LastReceived" class="form-label">Last Received Date</label>
            <input type="date" class="form-control" name="LastReceived" id="LastReceived">
          </div>
        </div>

       <div class="form-check mb-4">
           <input type="hidden" name="Status" value="0">
           <input class="form-check-input" type="checkbox" name="Status" value="1" id="Status" checked>
           <label class="form-check-label" for="Status">Is Active</label>
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