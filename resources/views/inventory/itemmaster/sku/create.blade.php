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
                            <label for="Category" class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="Category" id="Category" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $category)
                                    <option
                                        value="{{ $category->Id }}" {{ old('Category') == $category->Id ? 'selected' : '' }}>
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
                            <label for="ItemID" class="form-label">Item <span class="text-danger">*</span></label>
                            <select name="ItemID" id="Item" class="form-select" required>
                                <option value="">-- Select Item --</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="UOM" class="form-label">Unit of Measure <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="UOMName" value="{{ old('UOMName') }}" readonly>
                            <input type="hidden" name="UOM" id="UOM" value="{{ old('UOM') }}">
                        </div>

                        <div class="col-md-4">
                            <label for="UnitCost" class="form-label">Unit Cost <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="UnitCost" id="UnitCost"
                                   value="{{ old('UnitCost') }}" step="0.01" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Branch <span class="text-danger">*</span></label>
                            <input type="text" class="form-control"
                                   value="{{ $branch->Name ?? 'N/A' }}" readonly>
                            <input type="hidden" name="Branch" value="{{ $branch->Id ?? '' }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="Store" class="form-label">Store <span class="text-danger">*</span></label>
                            <select name="Store" id="Store" class="form-select" required>
                                <option value="">-- Select Store --</option>
                                @foreach($stores as $store)
                                    <option
                                        value="{{ $store->Id }}" {{ old('Store') == $store->Id ? 'selected' : '' }}>
                                        {{ $store->StoreName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="currentQty" class="form-label">Current Qty <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="CurrentQty" id="currentQty"
                                   value="{{ old('CurrentQty', 0) }}" min="0" step="0.01" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="Min" class="form-label">Min Stock Level</label>
                            <input type="number" class="form-control" name="Min" id="Min"
                                   value="{{ old('Min', 0) }}" min="0" step="0.01">
                        </div>
                        <div class="col-md-4">
                            <label for="Reorder" class="form-label">Reorder Qty</label>
                            <input type="number" class="form-control" name="Reorder" id="Reorder"
                                   value="{{ old('Reorder', 0) }}" min="0" step="0.01">
                        </div>
                        <div class="col-md-4">
                            <label for="LastReceived" class="form-label">Last Received Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="LastReceived" id="LastReceived"
                                   value="{{ old('LastReceived', now()->format('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success"
                                onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Save
                            Item
                        </button>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary px-4 ms-2">Cancel</a>
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

            const oldCategory = "{{ old('Category') }}";
            const oldSubcategory = "{{ old('Subcategory') }}";
            const oldItem = "{{ old('ItemID') }}";

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

            if (oldCategory) {
                categorySelect.dispatchEvent(new Event('change'));
                setTimeout(() => {
                    if (oldSubcategory) {
                        subcategorySelect.dispatchEvent(new Event('change'));
                    }
                }, 500);
            }

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

            if (oldItem) {
                itemSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('LastReceived').setAttribute('max', today);
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const storeSelect = document.getElementById('Store');
            const userBranchId = {{ $branch->Id ?? 'null' }};

            if (userBranchId) {
                const storeOptions = storeSelect.querySelectorAll('option');
                let hasStores = false;

                storeOptions.forEach(option => {
                    if (option.value !== '') {
                        hasStores = true;
                    }
                });

                if (!hasStores) {
                    storeSelect.innerHTML = '<option value="">No stores available for your branch</option>';
                }
            }
        });
    </script>

    <style>
        .form-label {
            font-weight: 500;
        }

        #UOMName {
            background-color: #f8f9fa;
        }

        .card-header {
            border-bottom: none;
        }

        .text-danger {
            font-weight: bold;
        }
    </style>
@endsection