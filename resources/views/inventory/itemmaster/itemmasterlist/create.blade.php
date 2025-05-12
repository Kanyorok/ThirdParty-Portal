@extends('layouts.app')

@section('title', 'Create New Inventory')

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="container bg-white shadow-sm rounded p-4">
    <h4 class="mb-4">📦 Item Master Form</h4>

    <form action="{{ route('itemmaster.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="itemCode" class="form-label">Item Code</label>
                <input type="text" name="ItemCode" id="itemCode" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label for="barcode" class="form-label">Bar Code</label>
                <input type="text" name="BarCode" id="barcode" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="itemName" class="form-label">Item Name</label>
                <input type="text" name="ItemName" id="itemName" class="form-control" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="itemType" class="form-label">Item Type</label>
                <select name="ItemType" id="itemType" class="form-select">
                    <option selected disabled>Select Type</option>
                    <option value="Stock">Stock</option>
                    <option value="Asset">Asset</option>
                    <option value="Non-Stock">Non-Stock</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="category" class="form-label">Category</label>
                <select name="Category" id="category" class="form-select">
                    <option selected disabled>Select Category</option>
                    <option>Office Supplies</option>
                    <option>IT Equipment</option>
                    <option>Stationery</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="subcategory" class="form-label">Subcategory</label>
                <select name="SubCategory" id="subcategory" class="form-select">
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
                <select name="UOM" id="uom" class="form-select">
                    <option selected disabled>Select UOM</option>
                    <option>pcs</option>
                    <option>kg</option>
                    <option>litres</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="inventoryType" class="form-label">Inventory Type</label>
                <select name="InventoryType" id="inventoryType" class="form-select">
                    <option selected disabled>Select Inventory Type</option>
                    <option>Consumable</option>
                    <option>Durable</option>
                    <option>Perishable</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="imageUpload" class="form-label">Item Image</label>
                <input type="file" name="imageUpload" id="imageUpload" class="form-control" accept="image/*">
            </div>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Item Description</label>
            <textarea name="Description" id="description" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label for="documentUpload" class="form-label">Upload Documentation</label>
            <input type="file" name="DocumentUpload" id="documentUpload" class="form-control" accept=".pdf,.doc,.docx">
        </div>

        <button type="submit" class="btn btn-primary">✅ Save Item</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@endsection
