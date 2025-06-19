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
                    @foreach($itemTypes as $itemType)
                        <option value="{{ $itemType->Id }}" {{ old('ItemType') == $itemType->Id ? 'selected' : '' }}>
                            {{ $itemType->TypeName }}
                        </option>
                    @endforeach

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
                    @foreach($uoms as $uom)
                        <option value="{{ $uom->Id }}" {{ old('UOM') == $uom->Id ? 'selected' : '' }}>
                            {{ $uom->Code }}
                        </option>
                    @endforeach

                </select>
            </div>
            <div class="col-md-4">
                <label for="InventoryType" class="form-label">Inventory Type</label>
                <select name="InventoryType" class="form-select" required>
                    <option disabled>Select Inventory Type</option>
                    @foreach($inventoryTypes as $inventoryType)
                        <option
                            value="{{ $inventoryType->Id }}" {{ old('InventoryType') == $inventoryType->Id ? 'selected' : '' }}>
                            {{ $inventoryType->Type }}
                        </option>
                    @endforeach

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
            @if($item->image)
                <div class="mt-2" id="current-image-section">
                    <img src="data:{{ $item->image->MIMEType }};base64,{{ $item->image->Image }}" alt="Item Image"
                         style="max-width:200px;">
                    <button type="button" class="btn btn-danger btn-sm ms-2" id="remove-image-btn">Remove Image</button>
                </div>
                <input type="hidden" name="remove_image" id="remove-image" value="0">
            @endif

            <div class="mb-3">
                <label for="DocumentUpload" class="form-label">Attached Document</label>
                @if($item->DocumentUpload)
                    <div class="mb-2">
                        <a href="{{ asset('storage/' . $item->DocumentUpload) }}" target="_blank">View Existing
                            Document</a>
                    </div>
                @endif
                <input type="file" name="DocumentUpload" id="DocumentUpload" class="form-control"
                       accept=".pdf,.doc,.docx,.xls,.xlsx">
            </div>
            <div class="col-md-4">
                <label for="Status" class="form-label">Active?</label>
                <input type="hidden" name="Status" value="0">
                <input class="form-check-input" type="checkbox" name="Status" value="1" id="Status"
                    {{ old('Status', 1) == 1 ? 'checked' : '' }}>

            </div>
        </div>

        <button type="submit" class="btn btn-success">Update Item</button>
    </form>
</div>

@endsection



@section('scripts')

    <script>
        $(document).ready(function () {
            // ...existing category/subcategory code...

            $('#remove-image-btn').on('click', function () {
                $('#current-image-section').hide();
                $('#remove-image').val('1');
            });
        });
    </script>
<script>
    $(document).ready(function () {
        $('#category').change(function () {
            let categoryId = $(this).val();
            $('#subcategory').html('<option value="">Loading...</option>');

            $.ajax({
                url: "{{ route('inventory.getSubcategories') }}",
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
