@extends('layouts.app')

@section('title', 'Create New Inventory')

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
    <h4 class="mb-4">📦 Item Master Form</h4>

    <form action="{{ route('itemmasterlist.store') }}" method="POST" enctype="multipart/form-data" id="itemMasterListForm">
        @csrf

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="BarCode" class="form-label">Bar Code</label>
                <input type="text" name="BarCode" id="BarCode" class="form-control" value="{{ old('BarCode') }}" required>
            </div>
            <div class="col-md-4">
                <label for="ItemName" class="form-label">Item Name</label>
                <input type="text" name="ItemName" id="ItemName" class="form-control" value="{{ old('ItemName') }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="ItemType" class="form-label">Item Type</label>
                <select name="ItemType" id="ItemType" class="form-select" required>
                    <option selected disabled>Select Type</option>
                    <option value="Stock">Stock</option>
                    <option value="Asset">Asset</option>
                    <option value="Non-Stock">Non-Stock</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="Category" class="form-label">Category</label>
                <select name="Category" id="category" class="form-select" required>
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->Id }}">{{ $category->Name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="SubCategory" class="form-label">Subcategory</label>
                <select name="SubCategory" id="subcategory" class="form-select">
                    <option value="">-- Select SubCategory --</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="UOM" class="form-label">Unit of Measure (UOM)</label>
                <select name="UOM" id="UOM" class="form-select" required>
                    <option selected disabled>Select UOM</option>
                    <option value="pcs">pcs</option>
                    <option value="kg">kg</option>
                    <option value="litres">litres</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="InventoryType" class="form-label">Inventory Type</label>
                <select name="InventoryType" id="InventoryType" class="form-select" required>
                    <option selected disabled>Select Inventory Type</option>
                    <option value="Consumable">Consumable</option>
                    <option value="Durable">Durable</option>
                    <option value="Perishable">Perishable</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label for="ItemDescription" class="form-label">Item Description</label>
            <textarea name="ItemDescription" id="ItemDescription" class="form-control" rows="3">{{ old('ItemDescription') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="ImageUpload" class="form-label">Item Image</label>
            <input type="file" name="ImageUpload" id="ImageUpload" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">✅ Save Item</button>
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
