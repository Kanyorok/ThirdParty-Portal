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
                <form action="{{ route('sku.update', $item->Id) }}" method="POST">
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
                            <input type="number" name="CurrentQty" class="form-control" id="currentQty" value="{{ $item->CurrentQty }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="minStockLevel" class="form-label">Min Stock Level</label>
                            <input type="number" name="Min" class="form-control" id="minStockLevel" value="{{ $item->Min }}">
                        </div>
                        <div class="col-md-4">
                            <label for="reorderQty" class="form-label">Reorder Qty</label>
                            <input type="number" name="Reorder" class="form-control" id="reorderQty" value="{{ $item->Reorder }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="lastReceivedDate" class="form-label">Last Received Date</label><input type="date" name="LastReceived" id="lastReceivedDate" class="form-control @error('LastReceived') is-invalid @enderror" value="{{ old('LastReceived', $item->LastReceived) }}" max="{{ \Carbon\Carbon::today()->toDateString() }}" required>

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


                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Update Item</button>
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

            const initialCategoryId = "{{ old('Category', $item->item->category->parent ? $item->item->category->parent->Id : $item->item->category->Id) }}";
            const initialSubcategoryId = "{{ old('Subcategory', $item->item->category->parent ? $item->item->category->Id : '') }}";
            const initialItemId = "{{ old('ItemID', $item->ItemID) }}";
            const initialBranchId = "{{ old('Branch', $item->Branch) }}";
            const initialStoreId = "{{ old('Store', $item->Store) }}";

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
        });
    </script>
</body>
@endsection