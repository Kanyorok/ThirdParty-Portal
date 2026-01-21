@extends('layouts.app')

@section('title', 'Edit Item')

@section('content')

<div class="container mt-4">
    <div class="card shadow rounded-4">
        <div class="card-header text-dark rounded-top-4" style="background-color: #add8e6;">
            <h4 class="mb-0">✏️ Edit Item Master</h4>
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

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('itemmasterlist.update', $item->Id) }}" method="POST" enctype="multipart/form-data" id="itemMasterListForm">
                @csrf
                @method('PUT')

                {{-- Row 1 --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="ItemName" class="form-label">Item Name <span class="text-danger">*</span></label>
                        <input type="text" name="ItemName" id="ItemName" 
                               class="form-control @error('ItemName') is-invalid @enderror" 
                               value="{{ old('ItemName', $item->ItemName) }}" required>
                        @error('ItemName')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="BarCode" class="form-label">Bar Code <span class="text-danger">*</span></label>
                        <input type="text" name="BarCode" id="BarCode" 
                               class="form-control @error('BarCode') is-invalid @enderror" 
                               value="{{ old('BarCode', $item->BarCode) }}" required>
                        @error('BarCode')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="ItemType" class="form-label">Item Type <span class="text-danger">*</span></label>
                        <select name="ItemType" id="ItemType" 
                                class="form-select @error('ItemType') is-invalid @enderror" required>
                            <option value="" selected disabled>-- Select Type --</option>
                            @foreach($itemTypes as $itemType)
                                <option value="{{ $itemType->Id }}" 
                                    {{ old('ItemType', $item->ItemType) == $itemType->Id ? 'selected' : '' }}>
                                    {{ $itemType->type->Description ?? $itemType->Id }}
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
                        <select name="Category" id="category" 
                                class="form-select @error('Category') is-invalid @enderror" required>
                            <option value="">-- Select Parent Category --</option>
                            @foreach($categories as $category)
                                @php
                                    // Get the parent category ID from the current item's category
                                    $parentId = $item->category ? ($item->category->ParentId ?? $item->category->Id) : null;
                                @endphp
                                <option value="{{ $category->Id }}" 
                                    {{ old('Category', $parentId) == $category->Id ? 'selected' : '' }}>
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
                        <select name="SubCategory" id="subcategory" 
                                class="form-select @error('SubCategory') is-invalid @enderror">
                            <option value="">-- Select Category --</option>
                            @foreach($subcategories as $subcategory)
                                <option value="{{ $subcategory->Id }}" 
                                    {{ old('SubCategory', $item->Category) == $subcategory->Id ? 'selected' : '' }}>
                                    {{ $subcategory->Name }}
                                </option>
                            @endforeach
                        </select>
                        @error('SubCategory')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="UOM" class="form-label">Unit of Measure (UOM) <span class="text-danger">*</span></label>
                        <select name="UOM" id="UOM" 
                                class="form-select @error('UOM') is-invalid @enderror" required>
                            <option value="" disabled>-- Select UOM --</option>
                            @foreach($uoms as $uom)
                                <option value="{{ $uom->Id }}" 
                                    {{ old('UOM', $item->UOM) == $uom->Id ? 'selected' : '' }}>
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
                        <select name="InventoryType" id="InventoryType" 
                                class="form-select @error('InventoryType') is-invalid @enderror" required>
                            <option value="" disabled>-- Select Inventory Type --</option>
                            @foreach($inventoryTypes as $inventoryType)
                                <option value="{{ $inventoryType->Id }}" 
                                    {{ old('InventoryType', $item->InventoryType) == $inventoryType->Id ? 'selected' : '' }}>
                                    {{ $inventoryType->type->Description ?? $inventoryType->Id }}
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
                        <label for="Status" class="form-label">Item Status <span class="text-danger">*</span></label>
                        <select name="Status" id="Status" 
                                class="form-select @error('Status') is-invalid @enderror">
                            <option value="">-- Select Status --</option>
                            @foreach($status as $stat)
                                <option value="{{ $stat->ID }}" 
                                    {{ old('Status', $item->Status) == $stat->ID ? 'selected' : '' }}>
                                    {{ $stat->Description }}
                                </option>
                            @endforeach
                        </select>
                        @error('Status')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="ImageUpload" class="form-label">Item Image</label>
                        <input type="file" name="ImageUpload" id="ImageUpload" 
                               class="form-control @error('ImageUpload') is-invalid @enderror">
                        @error('ImageUpload')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        
                        @if($item->image)
                            <div class="mt-2" id="current-image-section">
                                <img src="data:{{ $item->image->MIMEType }};base64,{{ $item->image->Image }}" 
                                     alt="Item Image" style="max-width:200px;" class="img-thumbnail">
                                <div class="mt-2">
                                    <button type="button" class="btn btn-danger btn-sm" id="remove-image-btn">
                                        Remove Image
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="remove_image" id="remove-image" value="0">
                        @endif
                    </div>
                </div>

                {{-- Document Upload --}}
                <div class="mb-3">
                    <label class="form-label">Supporting Documents</label>
                    
                    {{-- Existing documents --}}
                    <div class="card bg-light p-3 mb-3">
                        <h6 class="fw-bold mb-2">📄 Existing Documents</h6>
                        @forelse($item->documents()->get(['t_Documents.Id', 't_Documents.DocumentId','MimeType','Name']) as $document)
                            {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                        @empty
                            <p class="text-muted mb-0">No documents uploaded.</p>
                        @endforelse
                    </div>
                    
                    {{-- Upload new document --}}
                    <div class="mb-3">
                        <label class="form-label">Upload Supporting Document</label>
                        <input type="file" name="Document" 
                               class="form-control @error('Document') is-invalid @enderror">
                        <small class="text-muted">Attach inspection sheet, photos, or related files (Max: 5MB)</small>
                        @error('Document')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                {{-- Full-width Description --}}
                <div class="mb-4">
                    <label for="ItemDescription" class="form-label">Item Description <span class="text-danger">*</span></label>
                    <textarea name="ItemDescription" id="ItemDescription" 
                              class="form-control @error('ItemDescription') is-invalid @enderror" 
                              rows="3" required>{{ old('ItemDescription', $item->ItemDescription) }}</textarea>
                    <div class="invalid-feedback" id="description-error" style="display: none;">
                        Please enter a description for the item.
                    </div>
                    @error('ItemDescription')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('itemmaster.index') }}" class="btn btn-secondary">
                        ← Back to List
                    </a>
                    <button type="submit" class="btn btn-success" id="submitBtn">
                        Update Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
 @include('snippets.actions.preview-files')
@section('scripts')
<script>
    $(document).ready(function () {
        // Handle image removal
        $('#remove-image-btn').on('click', function () {
            $('#current-image-section').hide();
            $('#remove-image').val('1');
            $(this).hide();
        });

        // Category change for subcategories
        $('#category').change(function () {
            let categoryId = $(this).val();
            if (!categoryId) {
                $('#subcategory').html('<option value="">-- Select Category --</option>');
                return;
            }
            
            $('#subcategory').html('<option value="">Loading...</option>');

            $.ajax({
                url: "{{ route('get.subcategories') }}",
                type: 'GET',
                data: { category_id: categoryId },
                success: function (data) {
                    $('#subcategory').html('<option value="">-- Select Category --</option>');
                    $.each(data, function (key, value) {
                        $('#subcategory').append(`<option value="${value.Id}">${value.Name}</option>`);
                    });
                    
                    // Set old value if exists
                    @if(old('SubCategory'))
                        $('#subcategory').val('{{ old('SubCategory') }}');
                    @endif
                },
                error: function () {
                    $('#subcategory').html('<option value="">No categories found</option>');
                }
            });
        });

        // Form validation
        $('#itemMasterListForm').on('submit', function(e) {
            let isValid = true;
            
            // Reset error states
            $(this).find('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').hide();
            
            // Check required fields
            $('#ItemName, #BarCode, #ItemType, #Category, #UOM, #InventoryType, #ItemDescription').each(function() {
                if (!$(this).val() || $(this).val().trim() === '') {
                    isValid = false;
                    $(this).addClass('is-invalid');
                    
                    // Show specific error for description
                    if ($(this).is('#ItemDescription')) {
                        $('#description-error').show();
                    }
                }
            });
            
            // Check if description is not just whitespace
            const description = $('#ItemDescription').val().trim();
            if (!description) {
                isValid = false;
                $('#ItemDescription').addClass('is-invalid');
                $('#description-error').show();
            }
            
            if (!isValid) {
                e.preventDefault();
                // Show alert message
                alert('Please fill in all required fields (marked with *) before submitting.');
                return false;
            }
            
            // Disable submit button to prevent double submission
            $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');
            
            return true;
        });

        // Real-time validation for description
        $('#ItemDescription').on('input', function() {
            const value = $(this).val().trim();
            if (value) {
                $(this).removeClass('is-invalid');
                $('#description-error').hide();
            } else {
                $(this).addClass('is-invalid');
                $('#description-error').show();
            }
        });
    });
</script>

<style>
.text-danger {
    font-weight: bold;
}
.form-label {
    font-weight: 500;
}
/* Style for required field labels */
.form-label span.text-danger {
    color: #dc3545 !important;
    font-weight: bold;
}
/* Style for invalid fields */
.is-invalid {
    border-color: #dc3545 !important;
}
.img-thumbnail {
    padding: 0.25rem;
    background-color: #fff;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}
</style>
@endsection
