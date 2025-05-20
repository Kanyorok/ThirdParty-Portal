@extends('layouts.app')

@section('title', 'Item Details')

@section('content')

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="container bg-white shadow-sm rounded p-4">
    <h4>📦 Item Details: {{ $item->ItemName }}</h4>

    <div class="row mb-3">
        <div class="col-md-6">
            <p><strong>Item Code:</strong> {{ $item->ItemCode }}</p>
        </div>
        <div class="col-md-6">
            <p><strong>Bar Code:</strong> {{ $item->BarCode }}</p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <p><strong>Item Type:</strong> {{ $item->ItemType }}</p>
        </div>
        <div class="col-md-6">
            <p><strong>Category:</strong> {{ $item->category?->Name ?? 'N/A' }}</p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <p><strong>Parent Category:</strong> {{ $item->category->parent?->Name ?? 'N/A' }}</p>
        </div>
        <div class="col-md-6">
            <p><strong>Unit of Measure (UOM):</strong> {{ $item->UOM }}</p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <p><strong>Inventory Type:</strong> {{ $item->InventoryType }}</p>
        </div>
        <div class="col-md-6">
            <p><strong>Item Image:</strong></p>
            @if($item->ImageUpload)
                <img src="{{ asset('storage/' . $item->ImageUpload) }}" class="img-thumbnail" width="150" alt="Item Image">
            @else
                <p>No image available</p>
            @endif
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <p><strong>Item Description:</strong> {{ $item->ItemDescription ?? 'N/A' }}</p>
        </div>
        <div class="col-md-6">
            <p><strong>Document:</strong></p>
            @if($item->DocumentUpload)
                <a href="{{ asset('storage/' . $item->DocumentUpload) }}" class="btn btn-link" target="_blank">📄 View Document</a>
            @else
                <p>No document uploaded</p>
            @endif
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <a href="{{ route('itemmasterlist.index') }}" class="btn btn-secondary">Back</a>
        <a href="{{ route('itemmasterlist.edit', $item->Id) }}" class="btn btn-warning">Edit Item</a>
        <form action="{{ route('itemmasterlist.destroy', $item->Id) }}" method="POST" onsubmit="return confirm('⚠️ Are you sure you want to delete this item?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete Item</button>
        </form>
    </div>
</div>

@endsection
