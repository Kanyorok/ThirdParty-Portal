@extends('layouts.app')

@section('title', 'Edit Item')

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
    <h4 class="mb-4">✏️ Edit Item Master</h4>

    <form action="{{ route('itemmasterlist.update', $item->Id) }}" method="POST" enctype="multipart/form-data" id="itemMasterListForm">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="BarCode" class="form-label">Bar Code</label>
                <input type="text" name="BarCode" class="form-control" value="{{ old('BarCode', $item->BarCode) }}" required>
            </div>
            <div class="col-md-4">
                <label for="ItemName" class="form-label">Item Name</label>
                <input type="text" name="ItemName" class="form-control" value="{{ old('ItemName', $item->ItemName) }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="ItemType" class="form-label">Item Type</label>
                <select name="ItemType" class="form-select" required>
                    <option disabled>Select Type</option>
                    <option value="Stock" {{ old('ItemType', $item->ItemType) == 'Stock' ? 'selected' : '' }}>Stock</option>
                    <option value="Asset" {{ old('ItemType', $item->ItemType) == 'Asset' ? 'selected' : '' }}>Asset</option>
                    <option value="Non-Stock" {{ old('ItemType', $item->ItemType) == 'Non-Stock' ? 'selected' : '' }}>Non-Stock</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="Category" class="form-label">Category</label>
                <select name="Category" id="category" class="form-select" required>
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->Id }}" {{ optional($item->category->parent)->Id == $category->Id ? 'selected' : '' }}>
                            {{ $category->Name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="SubCategory" class="form-label">Subcategory</label>
                <select name="SubCategory" id="subcategory" class="form-select">
                    <option value="">-- Select SubCategory --</option>
                    @foreach($subcategories as $subcategory)
                        <option value="{{ $subcategory->Id }}" {{ $item->Category == $subcategory->Id ? 'selected' : '' }}>
                            {{ $subcategory->Name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="UOM" class="form-label">Unit of Measure (UOM)</label>
                <select name="UOM" class="form-select" required>
                    <option disabled>Select UOM</option>
                    <option value="pcs" {{ old('UOM', $item->UOM) == 'pcs' ? 'selected' : '' }}>pcs</option>
                    <option value="kg" {{ old('UOM', $item->UOM) == 'kg' ? 'selected' : '' }}>kg</option>
                    <option value="litres" {{ old('UOM', $item->UOM) == 'litres' ? 'selected' : '' }}>litres</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="InventoryType" class="form-label">Inventory Type</label>
                <select name="InventoryType" class="form-select" required>
                    <option disabled>Select Inventory Type</option>
                    <option value="Consumable" {{ old('InventoryType', $item->InventoryType) == 'Consumable' ? 'selected' : '' }}>Consumable</option>
                    <option value="Durable" {{ old('InventoryType', $item->InventoryType) == 'Durable' ? 'selected' : '' }}>Durable</option>
                    <option value="Perishable" {{ old('InventoryType', $item->InventoryType) == 'Perishable' ? 'selected' : '' }}>Perishable</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label for="ItemDescription" class="form-label">Item Description</label>
            <textarea name="ItemDescription" class="form-control" rows="3">{{ old('ItemDescription', $item->ItemDescription) }}</textarea>
        </div>

        <div class="mb-3">
            <label for="ImageUpload" class="form-label">Item Image</label>
            <input type="file" name="ImageUpload" class="form-control">
            @if($item->ImageUpload)
                <img src="{{ asset('storage/' . $item->ImageUpload) }}" class="img-thumbnail mt-2" style="max-height: 100px;">
            @endif
        </div>

        <button type="submit" class="btn btn-success">💾 Update Item</button>
    </form>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $('#category').change(function () {
            let categoryId = $(this).val();
            $('#subcategory').html('<option value="">Loading...</option>');

            $.ajax({
                url: "{{ route('get.subcategories') }}",
                type: 'GET',
                data: { category_id: categoryId },
                success: function (data) {
                    $('#subcategory').html('<option value="">-- Select SubCategory --</option>');
                    $.each(data, function (key, value) {
                        $('#subcategory').append(`<option value="${value.Id}">${value.Name}</option>`);
                    });
                },
                error: function () {
                    $('#subcategory').html('<option value="">No subcategories found</option>');
                }
            });
        });
    });
</script>
@endsection
