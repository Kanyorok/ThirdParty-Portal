@extends('layouts.app')
@section('title', 'Create New Inventory')
@section('content')
<div class="container bg-white shadow-sm rounded p-4">
    <h4 class="mb-4">📦 Item Master Form</h4>

    <form enctype="multipart/form-data">
      <div class="row mb-3">
        <div class="col-md-4">
          <label for="itemCode" class="form-label">Item Code</label>
          <input type="text" class="form-control" id="itemCode" required>
        </div>
        <div class="col-md-4">
          <label for="barcode" class="form-label">Bar Code</label>
          <input type="text" class="form-control" id="barcode">
        </div>
        <div class="col-md-4">
          <label for="itemName" class="form-label">Item Name</label>
          <input type="text" class="form-control" id="itemName" required>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-4">
          <label for="itemType" class="form-label">Item Type</label>
          <select class="form-select" id="itemType">
            <option selected disabled>Select Type</option>
            <option value="1">Stock</option>
            <option value="2">Asset</option>
            <option value="3">Non-Stock</option>
          </select>
        </div>
        <div class="col-md-4">
          <label for="category" class="form-label">Category</label>
          <select class="form-select" id="category">
            <option selected disabled>Select Category</option>
            <option>Office Supplies</option>
            <option>IT Equipment</option>
            <option>Stationery</option>
          </select>
        </div>
        <div class="col-md-4">
          <label for="subcategory" class="form-label">Subcategory</label>
          <select class="form-select" id="subcategory">
            <option selected disabled>Select Subcategory</option>
            <option>Ink & Toners</option>
            <option>Laptops</option>
            <option>Files & Folders</option>
          </select>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-4">
          <label for="uom" class="form-label">Unit of Measure (UOM)</label>
          <select class="form-select" id="uom">
            <option selected disabled>Select UOM</option>
            <option>pcs</option>
            <option>kg</option>
            <option>litres</option>
          </select>
        </div>
        <div class="col-md-4">
          <label for="inventoryType" class="form-label">Inventory Type</label>
          <select class="form-select" id="inventoryType">
            <option selected disabled>Select Inventory Type</option>
            <option>Consumable</option>
            <option>Durable</option>
            <option>Perishable</option>
          </select>
        </div>
        <div class="col-md-4">
          <label for="imageUpload" class="form-label">Item Image</label>
          <input class="form-control" type="file" id="imageUpload" accept="image/*">
        </div>
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">Item Description</label>
        <textarea class="form-control" id="description" rows="3"></textarea>
      </div>

      <div class="mb-3">
        <label for="documentUpload" class="form-label">Upload Documentation</label>
        <input class="form-control" type="file" id="documentUpload" accept=".pdf,.doc,.docx">
      </div>

      <button type="submit" class="btn btn-primary">✅ Save Item</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  @endSection