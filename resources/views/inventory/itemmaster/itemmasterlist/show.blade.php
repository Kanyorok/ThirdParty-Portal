@extends('layouts.app')

@section('title', 'Item Details')

@section('content')
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
            <p><strong>Category:</strong> {{  $item->category->Name}}</p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <p><strong>Subcategory:</strong> {{  $item->subcategory->SubCategoryName }}</p>
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
                <img src="{{ asset('storage/' . $item->ImageUpload) }}" class="img-thumbnail" width="150">
            @else
                <p>No image available</p>
            @endif
        </div>
    </div>
    <div class="row mb-3">
    <div class="col-md-6">
        <p><strong>Item Description:</strong> {{ $item->ItemDescription }}</p>
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
        <a href="{{ route('itemmaster.index') }}" class="btn btn-secondary">🔙 Back</a>
        <a href="{{ route('itemmasterlist.edit', $item->Id) }}" class="btn btn-warning">✏️ Edit Item</a>
        <form action="{{ route('itemmasterlist.destroy', $item->Id) }}" method="POST">
        @csrf
        @method('DELETE')
       
        <button type="submit" class="btn btn-danger delete-button">🗑️ Delete Item</button>
       </form>

         <script>
    document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', function(event) {
            if (!confirm('⚠️ Are you sure you want to delete this item?')) {
                event.preventDefault();
            }
        });
    });
  </script>
    </div>
</div>
@endsection
