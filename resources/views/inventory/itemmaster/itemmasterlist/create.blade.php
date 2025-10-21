@extends('layouts.app')

@section('title', 'Create New Item')

@section('content')

<div class="container mt-4">
    <div class="card shadow rounded-4">
        <div class="card-header text-dark rounded-top-4" style="background-color: #add8e6;">
            <h4 class="mb-0">📦 Item Master Form</h4>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('itemmasterlist.store') }}" method="POST" enctype="multipart/form-data" id="itemMasterListForm">
                @csrf

                {{-- Row 1 --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="BarCode" class="form-label">Bar Code <span class="text-danger">*</span></label>
                        <input type="text" name="BarCode" id="BarCode" class="form-control @error('BarCode') is-invalid @enderror" 
                               value="{{ old('BarCode') }}" required>
                        @error('BarCode')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="ItemName" class="form-label">Item Name <span class="text-danger">*</span></label>
                        <input type="text" name="ItemName" id="ItemName" class="form-control @error('ItemName') is-invalid @enderror" 
                               value="{{ old('ItemName') }}" required>
                        @error('ItemName')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="ItemType" class="form-label">Item Type <span class="text-danger">*</span></label>
                        <select name="ItemType" id="ItemType" class="form-select @error('ItemType') is-invalid @enderror" required>
                            <option value="" selected disabled>Select Type</option>
                            @foreach($itemTypes as $itemType)
                                <option value="{{ $itemType->Id }}" {{ old('ItemType') == $itemType->Id ? 'selected' : '' }}>
                                    {{ $itemType->TypeName }}
                                </option>
                            @endforeach
                        </select>
                        @error('ItemType')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                {{-- Row 2 --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="Category" class="form-label">Parent Category <span class="text-danger">*</span></label>
                        <select name="Category" id="category" class="form-select @error('Category') is-invalid @enderror" required>
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->Id }}" {{ old('Category') == $category->Id ? 'selected' : '' }}>
                                    {{ $category->Name }}
                                </option>
                            @endforeach
                        </select>
                        @error('Category')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="SubCategory" class="form-label">Category</label>
                        <select name="SubCategory" id="subcategory" class="form-select @error('SubCategory') is-invalid @enderror">
                            <option value="">-- Select SubCategory --</option>
                        </select>
                        @error('SubCategory')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="UOM" class="form-label">Unit of Measure (UOM) <span class="text-danger">*</span></label>
                        <select name="UOM" id="UOM" class="form-select @error('UOM') is-invalid @enderror" required>
                            <option value="" selected disabled>Select UOM</option>
                            @foreach($uoms as $uom)
                                <option value="{{ $uom->Id }}" {{ old('UOM') == $uom->Id ? 'selected' : '' }}>
                                    {{ $uom->Code }}
                                </option>
                            @endforeach
                        </select>
                        @error('UOM')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                {{-- Row 3 --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="InventoryType" class="form-label">Inventory Type <span class="text-danger">*</span></label>
                        <select name="InventoryType" id="InventoryType" class="form-select @error('InventoryType') is-invalid @enderror" required>
                            <option value="" selected disabled>Select Inventory Type</option>
                            @foreach($inventoryTypes as $inventoryType)
                                <option value="{{ $inventoryType->Id }}" {{ old('InventoryType') == $inventoryType->Id ? 'selected' : '' }}>
                                    {{ $inventoryType->Type }}
                                </option>
                            @endforeach
                        </select>
                        @error('InventoryType')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="ImageUpload" class="form-label">Item Image</label>
                        <input type="file" name="ImageUpload" id="ImageUpload" class="form-control @error('ImageUpload') is-invalid @enderror">
                        @error('ImageUpload')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="DocumentUpload" class="form-label">Upload Document (PDF, DOCX, XLSX, etc.)</label>
                        <input type="file" name="DocumentUpload" id="DocumentUpload" class="form-control @error('DocumentUpload') is-invalid @enderror"
                               accept=".pdf,.doc,.docx,.xls,.xlsx">
                        @error('DocumentUpload')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                {{-- Full-width Row --}}
                <div class="mb-3">
                    <label for="ItemDescription" class="form-label">Item Description</label>
                    <textarea name="ItemDescription" id="ItemDescription" class="form-control @error('ItemDescription') is-invalid @enderror" rows="3">{{ old('ItemDescription') }}</textarea>
                    @error('ItemDescription')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success"
                            onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                        ✅ Save Item
                    </button>
                    
                </div>
            </form>
        </div>
    </div>
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

        // Set old subcategory value if exists
        @if(old('SubCategory'))
            setTimeout(function() {
                $('#subcategory').val('{{ old('SubCategory') }}');
            }, 500);
        @endif
    });
</script>

<style>
.text-danger {
    font-weight: bold;
}
.form-label {
    font-weight: 500;
}
</style>
@endsection