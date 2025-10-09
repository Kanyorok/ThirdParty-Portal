@extends('layouts.app')
@section('title', 'Create New Stock Item')
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

    <div class="container mt-5">
        <div class="card shadow rounded-4">
            <div class="card-header text-dark rounded-top-4" style="background-color: #add8e6;">
                <h4 class="mb-0">➕ Add SKU</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('sku.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="Category" class="form-label">Category</label>
                            <select name="Category" id="Category" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->Id }}" {{ old('Category') == $category->Id ? 'selected' : '' }}>
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

                        <div class="col-md-4">
                            <label for="ItemID" class="form-label">Item</label>
                            <select name="ItemID" id="Item" class="form-select" required>
                                <option value="">-- Select Item --</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="UOM" class="form-label">Unit of Measure</label>
                            <input type="text" class="form-control" id="UOMName" value="{{ old('UOMName') }}" readonly>
                            <input type="hidden" name="UOM" id="UOM" value="{{ old('UOM') }}">
                        </div>

                        <div class="col-md-4">
                            <label for="UnitCost" class="form-label">Unit Cost</label>
                            <input type="number" class="form-control" name="UnitCost" id="UnitCost"
                                   value="{{ old('UnitCost') }}" step="0.01" readonly>
                        </div>

                        <div class="col-md-6">
                            <label for="Branch" class="form-label">Branch</label>
                            <select name="Branch" id="Branch" class="form-select" required>
                                <option value="">-- Select Branch --</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->Id }}" {{ old('Branch') == $branch->Id ? 'selected' : '' }}>
                                        {{ $branch->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="Store" class="form-label">Store</label>
                            <select name="Store" id="Store" class="form-select">
                                <option value="">-- Select Store --</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="currentQty" class="form-label">Current Qty</label>
                            <input type="number" class="form-control" name="CurrentQty" id="currentQty"
                                   value="{{ old('CurrentQty', 0) }}" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label for="Min" class="form-label">Min Stock Level</label>
                            <input type="number" class="form-control" name="Min" id="Min"
                                   value="{{ old('Min', 0) }}" min="0">
                        </div>
                        <div class="col-md-4">
                            <label for="Reorder" class="form-label">Reorder Qty</label>
                            <input type="number" class="form-control" name="Reorder" id="Reorder"
                                   value="{{ old('Reorder', 0) }}" min="0">
                        </div>
                        <div class="col-md-6 mt-3">
                            <label for="LastReceived" class="form-label">Last Received Date</label>
                            <input type="date" class="form-control" name="LastReceived" id="LastReceived"
                                   value="{{ old('LastReceived') }}" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success"
                                onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Save Item</button>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary px-4 ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Branch -> Store --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const branchSelect = document.getElementById('Branch');
            const storeSelect = document.getElementById('Store');
            const oldStore = "{{ old('Store') }}";

            branchSelect.addEventListener('change', function () {
                const branchId = this.value;
                storeSelect.innerHTML = '<option value="">-- Select Store --</option>';

                if (branchId) {
                    fetch(`/inventory/get-stores?BranchID=${branchId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.length === 0) {
                                const option = document.createElement('option');
                                option.value = "";
                                option.text = "No stores found for this branch";
                                storeSelect.appendChild(option);
                            } else {
                                data.forEach(store => {
                                    const option = document.createElement('option');
                                    option.value = store.Id;
                                    option.text = store.StoreName;
                                    if (store.Id == oldStore) {
                                        option.selected = true;
                                    }
                                    storeSelect.appendChild(option);
                                });
                            }
                        });
                }
            });

            if (branchSelect.value) {
                branchSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>

    {{-- Category -> Subcategory -> Item --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categorySelect = document.getElementById('Category');
            const subcategorySelect = document.getElementById('Subcategory');
            const itemSelect = document.getElementById('Item');

            const oldCategory = "{{ old('Category') }}";
            const oldSubcategory = "{{ old('Subcategory') }}";
            const oldItem = "{{ old('ItemID') }}";

            // Load subcategories + items when category changes
            categorySelect.addEventListener('change', function () {
                const categoryId = this.value;
                subcategorySelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
                itemSelect.innerHTML = '<option value="">-- Select Item --</option>';

                if (categoryId) {
                    fetch(`/inventory/get-subcategories?category_id=${categoryId}`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(subcat => {
                                const option = document.createElement('option');
                                option.value = subcat.Id;
                                option.text = subcat.Name;
                                if (subcat.Id == oldSubcategory) {
                                    option.selected = true;
                                }
                                subcategorySelect.appendChild(option);
                            });
                        });

                    fetch(`/inventory/get-items?category_id=${categoryId}`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(item => {
                                const option = document.createElement('option');
                                option.value = item.Id;
                                option.text = item.ItemName;
                                if (item.Id == oldItem) {
                                    option.selected = true;
                                }
                                itemSelect.appendChild(option);
                            });
                        });
                }
            });

            // When subcategory changes, load items under it
            subcategorySelect.addEventListener('change', function () {
                const subcategoryId = this.value;
                itemSelect.innerHTML = '<option value="">-- Select Item --</option>';

                if (subcategoryId) {
                    fetch(`/inventory/info/get-items?subcategory_id=${subcategoryId}`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(item => {
                                const option = document.createElement('option');
                                option.value = item.Id;
                                option.text = item.ItemName;
                                if (item.Id == oldItem) {
                                    option.selected = true;
                                }
                                itemSelect.appendChild(option);
                            });
                        });
                }
            });

            // Auto-load old selections on validation error
            if (oldCategory) {
                categorySelect.dispatchEvent(new Event('change'));
                setTimeout(() => {
                    if (oldSubcategory) {
                        subcategorySelect.dispatchEvent(new Event('change'));
                    }
                }, 500);
            }

            // Populate UOM + UnitCost on item change
            itemSelect.addEventListener('change', function () {
                const itemId = this.value;

                if (itemId) {
                    fetch("{{ route('sku.item-details') }}?item_id=" + itemId)
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('UnitCost').value = data.UnitCost ?? '';
                            document.getElementById('UOM').value = data.UOM?.id ?? '';
                            document.getElementById('UOMName').value = data.UOM?.name ?? '';
                        });
                } else {
                    document.getElementById('UnitCost').value = '';
                    document.getElementById('UOM').value = '';
                    document.getElementById('UOMName').value = '';
                }
            });

            // Trigger item details if old item exists
            if (oldItem) {
                itemSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>

    {{-- Restrict LastReceived to today --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('LastReceived').setAttribute('max', today);
        });
    </script>
@endsection
