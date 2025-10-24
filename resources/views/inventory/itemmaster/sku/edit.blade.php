@extends('layouts.app')
@section('title', 'Edit Inventory')
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
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow rounded-4">
            <div class="card-header text-dark rounded-top-4" style="background-color: #add8e6;">
                <h4 class="mb-0">Edit SKU Master</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('sku.update', $item->Id) }}" method="POST" id="inventoryForm">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="skuCode" class="form-label">SKU Code</label>
                            <input type="text" name="SKUCode" class="form-control" id="skuCode" value="{{ $item->SKUCode }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="Category" class="form-label">Category</label>
                            <select name="Category" id="Category" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->Id }}"
                                        {{ (old('Category') == $category->Id) || ($item->item->category->parent ? $item->item->category->parent->Id : $item->item->category->Id) == $category->Id ? 'selected' : '' }}>
                                        {{ $category->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="Subcategory" class="form-label">Subcategory</label>
                            <select name="Subcategory" id="Subcategory" class="form-select">
                                <option value="">-- Select Subcategory --</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="ItemID" class="form-label">Item</label>
                            <select name="ItemID" id="Item" class="form-select" required>
                                <option value="">-- Select Item --</option>
                                {{-- Items will be populated by JavaScript --}}
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="Branch" class="form-label">Branch</label>
                            <select name="Branch" id="Branch" class="form-select" required>
                                <option value="{{ $branch->Id }}" selected>{{ $branch->Name }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="Store" class="form-label">Store</label>
                            <select name="Store" id="Store" class="form-select">
                                <option value="">-- Select Store --</option>
                                {{-- Stores will be populated by JavaScript --}}
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="currentQty" class="form-label">Current Qty</label>
                            <input type="number" name="CurrentQty" class="form-control" id="currentQty" 
                                   value="{{ $item->CurrentQty }}" min="0" step="1" required
                                   oninput="validateQuantity(this)">
                            <div class="invalid-feedback" id="currentQtyError">
                                Quantity cannot be negative
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="minStockLevel" class="form-label">Min Stock Level</label>
                            <input type="number" name="Min" class="form-control" id="minStockLevel" 
                                   value="{{ $item->Min }}" min="0" step="1"
                                   oninput="validateQuantity(this)">
                            <div class="invalid-feedback" id="minStockLevelError">
                                Minimum stock level cannot be negative
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="reorderQty" class="form-label">Reorder Qty</label>
                            <input type="number" name="Reorder" class="form-control" id="reorderQty" 
                                   value="{{ $item->Reorder }}" min="0" step="1"
                                   oninput="validateQuantity(this)">
                            <div class="invalid-feedback" id="reorderQtyError">
                                Reorder quantity cannot be negative
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="lastReceivedDate" class="form-label">Last Received Date</label>
                            <input type="date" name="LastReceived" id="lastReceivedDate" 
                                   class="form-control @error('LastReceived') is-invalid @enderror" 
                                   value="{{ old('LastReceived', $item->LastReceived) }}" 
                                   max="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                            @error('LastReceived')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-5">
                                <input type="hidden" name="Status" value="0">
                                <input class="form-check-input" type="checkbox" name="Status" value="1" id="Status" 
                                    {{ $item->Status ? 'checked' : '' }}>
                                <label class="form-check-label" for="Status">Is Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-success" id="submitBtn">Update Item</button>
                        <a href="{{ route('sku.index') }}" class="btn btn-danger px-4 ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categorySelect = document.getElementById('Category');
            const subcategorySelect = document.getElementById('Subcategory');
            const itemSelect = document.getElementById('Item');
            const branchSelect = document.getElementById('Branch');
            const storeSelect = document.getElementById('Store');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('inventoryForm');

            const initialCategoryId = "{{ old('Category', $item->item->category->parent ? $item->item->category->parent->Id : $item->item->category->Id) }}";
            const initialSubcategoryId = "{{ old('Subcategory', $item->item->category->parent ? $item->item->category->Id : '') }}";
            const initialItemId = "{{ old('ItemID', $item->ItemID) }}";
            const initialBranchId = "{{ old('Branch', $item->Branch) }}";
            const initialStoreId = "{{ old('Store', $item->Store) }}";

            // Function to validate quantity fields
            window.validateQuantity = function(input) {
                const value = parseFloat(input.value);
                const errorDiv = document.getElementById(input.id + 'Error');
                
                if (value < 0) {
                    input.classList.add('is-invalid');
                    if (errorDiv) {
                        errorDiv.style.display = 'block';
                    }
                    return false;
                } else {
                    input.classList.remove('is-invalid');
                    if (errorDiv) {
                        errorDiv.style.display = 'none';
                    }
                    return true;
                }
            };

            // Function to validate all quantity fields before form submission
            function validateAllQuantities() {
                const currentQty = document.getElementById('currentQty');
                const minStockLevel = document.getElementById('minStockLevel');
                const reorderQty = document.getElementById('reorderQty');
                
                const isCurrentQtyValid = validateQuantity(currentQty);
                const isMinStockValid = validateQuantity(minStockLevel);
                const isReorderQtyValid = validateQuantity(reorderQty);
                
                return isCurrentQtyValid && isMinStockValid && isReorderQtyValid;
            }

            // Form submission handler
            form.addEventListener('submit', function(e) {
                if (!validateAllQuantities()) {
                    e.preventDefault();
                    alert('Please fix the validation errors before submitting the form.');
                    return false;
                }
                
                // Disable submit button to prevent double submission
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';
            });

            // Prevent negative input through keyboard
            document.querySelectorAll('input[type="number"]').forEach(input => {
                input.addEventListener('keydown', function(e) {
                    if (e.key === '-' || e.key === 'e' || e.key === 'E') {
                        e.preventDefault();
                    }
                });
                
                // Additional validation on blur
                input.addEventListener('blur', function() {
                    if (this.value < 0) {
                        this.value = 0;
                        validateQuantity(this);
                    }
                });
            });

            function loadSubcategories(categoryId, selectedSubcategoryId = null) {
                subcategorySelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
                if (categoryId) {
                    fetch(`/inventory/get-subcategories?category_id=${categoryId}`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(subcat => {
                                const option = document.createElement('option');
                                option.value = subcat.Id;
                                option.text = subcat.Name;
                                if (subcat.Id == selectedSubcategoryId) {
                                    option.selected = true;
                                }
                                subcategorySelect.appendChild(option);
                            });
                            // After loading subcategories, check if we need to load items
                            if (selectedSubcategoryId) {
                                loadItems(selectedSubcategoryId, selectedSubcategoryId, initialItemId);
                            }
                        });
                }
            }

            function loadItems(categoryId, subcategoryId = null, selectedItemId = null) {
                itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
                const idToFetch = subcategoryId || categoryId;
                if (idToFetch) {
                    fetch(`{{ route('get.items') }}?category_id=${idToFetch}`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(item => {
                                const option = document.createElement('option');
                                option.value = item.Id;
                                option.text = item.ItemName;
                                if (item.Id == selectedItemId) {
                                    option.selected = true;
                                }
                                itemSelect.appendChild(option);
                            });
                        });
                }
            }

            function loadStores(branchId, selectedStoreId = null) {
                storeSelect.innerHTML = '<option value="">-- Select Store --</option>';
                if (branchId) {
                    fetch(`/inventory/get-stores?BranchID=${branchId}`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(store => {
                                const option = document.createElement('option');
                                option.value = store.Id;
                                option.text = store.StoreName;
                                if (store.Id == selectedStoreId) {
                                    option.selected = true;
                                }
                                storeSelect.appendChild(option);
                            });
                        });
                }
            }

            // Initial load sequence
            if (initialCategoryId) {
                loadSubcategories(initialCategoryId, initialSubcategoryId);
            }
            if (!initialSubcategoryId && initialCategoryId) {
                // Only load items from the main category if no subcategory exists
                loadItems(initialCategoryId, null, initialItemId);
            }
            if (initialBranchId) {
                loadStores(initialBranchId, initialStoreId);
            }

            // Event listeners
            categorySelect.addEventListener('change', function () {
                loadSubcategories(this.value);
                loadItems(this.value);
            });

            subcategorySelect.addEventListener('change', function () {
                loadItems(this.value, this.value);
            });

            branchSelect.addEventListener('change', function () {
                loadStores(this.value);
            });

            // Initial validation on page load
            validateAllQuantities();
        });
    </script>

    <style>
        .is-invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6.4.4.4-.4'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        
        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }
        
        .is-invalid ~ .invalid-feedback {
            display: block;
        }
        
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
    </style>
</body>
@endsection