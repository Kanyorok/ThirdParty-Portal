@extends('layouts.app')

@section('title', 'Edit Item')

@section('content')
<div class="container bg-white shadow-sm rounded p-4">
    <h4>✏️ Edit Item: {{ $item->ItemName }}</h4>

<form action="{{ route('itemmasterlist.update', $item->Id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')


        <div class="row mb-3">
            <div class="col-md-4">
                <label for="ItemCode" class="form-label">Item Code</label>
                <input type="text" name="ItemCode" value="{{ $item->ItemCode }}" class="form-control" re>
            </div>
            <div class="col-md-4">
                <label for="BarCode" class="form-label">Bar Code</label>
                <input type="text" name="BarCode" value="{{ $item->BarCode }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label for="ItemName" class="form-label">Item Name</label>
                <input type="text" name="ItemName" value="{{ $item->ItemName }}" class="form-control" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="ItemType" class="form-label">Item Type</label>
                <select name="ItemType" class="form-select" required>
                    <option value="Stock" {{ $item->ItemType == 'Stock' ? 'selected' : '' }}>Stock</option>
                    <option value="Asset" {{ $item->ItemType == 'Asset' ? 'selected' : '' }}>Asset</option>
                    <option value="Non-Stock" {{ $item->ItemType == 'Non-Stock' ? 'selected' : '' }}>Non-Stock</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="Category" class="form-label">Category</label>
                <input type="text" name="Category" value="{{ $item->Category }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label for="SubCategory" class="form-label">Subcategory</label>
                <input type="text" name="SubCategory" value="{{ $item->SubCategory }}" class="form-control" required>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <label for="UOM" class="form-label">Unit of Measure (UOM)</label>
                <input type="text" name="UOM" value="{{ $item->UOM }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="InventoryType" class="form-label">Inventory Type</label>
                <input type="text" name="InventoryType" value="{{ $item->InventoryType }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label for="ImageUpload" class="form-label">Upload New Image</label>
                <input type="file" name="ImageUpload" class="form-control">
                @if($item->ImageUpload)
                    <img src="{{ asset('storage/' . $item->ImageUpload) }}" class="img-thumbnail mt-2" width="150">
                @endif
            </div>
            <div class="row mb-3">
    <div class="col-md-4">
        <label for="ItemDescription" class="form-label">Item Description</label>
        <textarea name="ItemDescription" class="form-control">{{ $item->ItemDescription }}</textarea>
    </div>
    <div class="col-md-4">
        <label for="DocumentUpload" class="form-label">Upload Document</label>
        <input type="file" name="DocumentUpload" class="form-control">
        @if($item->DocumentUpload)
            <a href="{{ asset('storage/' . $item->DocumentUpload) }}" class="btn btn-link" target="_blank">📄 View Document</a>
        @endif
    </div>
</div>

        </div>
        <button type="submit" class="btn btn-primary">✅ Save Changes</button>
        <a href="{{ route('itemmaster.index') }}" class="btn btn-secondary">🔙 Cancel</a>
    </form>
</div>
@endsection
