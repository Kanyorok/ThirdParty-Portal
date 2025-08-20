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
    <body class="bg-light">

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
                                    <option value="{{ $category->Id }}">{{ $category->Name }}</option>
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
                            <input type="text" class="form-control" id="UOMName" value="" readonly>
                            <input type="hidden" name="UOM" id="UOM">
                        </div>

                        <div class="col-md-4">
                            <label for="UnitCost" class="form-label">Unit Cost</label>
                            <input type="number" class="form-control" name="UnitCost" id="UnitCost" value="" step="0.01" readonly>
                        </div>


                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input type="hidden" name="Batch" value="0">
                                <input class="form-check-input" type="checkbox" name="Batch"
                                       value="1" {{ isset($item) && $item->Batch ? 'checked' : '' }}>
                                <label class="form-check-label">Is Batch Tracked</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input type="hidden" name="Serial" value="0">
                                <input class="form-check-input" type="checkbox" name="Serial"
                                       value="1" {{isset($item) &&  $item->Serial ? 'checked' : '' }}>
                                <label class="form-check-label">Is Serial Tracked</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input type="hidden" name="Perishable" value="0">
                                <input class="form-check-input" type="checkbox" name="Perishable"
                                       value="1" {{isset($item) &&  $item->Perishable ? 'checked' : '' }}>
                                <label class="form-check-label">Is Perishable</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input type="hidden" name="Saleable" value="0">
                                <input class="form-check-input" type="checkbox" name="Saleable"
                                       value="1" {{ isset($item) && $item->Saleable ? 'checked' : '' }}>
                                <label class="form-check-label">Is Saleable</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input type="hidden" name="Purchasable" value="0">
                                <input class="form-check-input" type="checkbox" name="Purchasable"
                                       value="1" {{ isset($item) && $item->Purchasable ? 'checked' : '' }}>
                                <label class="form-check-label">Is Purchasable</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="Branch" class="form-label">Branch</label>
                            <select name="Branch" id="Branch" class="form-select" required>
                                <option value="">-- Select Branch --</option>
                                @foreach($branches as $branch)
                                    <option
                                        value="{{ $branch->Id }}" {{ old('Branch') == $branch->Id ? 'selected' : '' }}>
                                        {{ $branch->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="Store" class="form-label">Store</label>
                            <select name="Store" id="Store" class="form-select">
                                <option value="">-- Select Store --</option>
                                {{-- Stores will be loaded dynamically --}}
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="currentQty" class="form-label">Current Qty</label>
                                <input type="number" class="form-control" name="CurrentQty" id="currentQty" value="0"
                                       required>
                            </div>
                            <div class="col-md-4">
                                <label for="Min" class="form-label">Min Stock Level</label>
                                <input type="number" class="form-control" name="Min" id="Min" value="0">
                            </div>
                            <div class="col-md-4">
                                <label for="Reorder" class="form-label">Reorder Qty</label>
                                <input type="number" class="form-control" name="Reorder" id="Reorder" value="0">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="Max" class="form-label">Max Stock Level</label>
                                <input type="number" class="form-control" name="Max" id="Max" value="0">
                            </div>
                            <div class="col-md-6">
                                <label for="LastReceived" class="form-label">Last Received Date</label>
                                <input type="date" class="form-control" name="LastReceived" id="LastReceived" required>
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input type="hidden" name="Status" value="0">
                            <input class="form-check-input" type="checkbox" name="Status" value="1" id="Status" checked>
                            <label class="form-check-label" for="Status">Is Active</label>
                        </div>

                        <div class="d-flex justify-content-end">

                            <button type="submit" class="btn btn-success px-4">Save Item</button>
                            <button type="Cancel" class="btn btn-secondary px-4">Cancel</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const branchSelect = document.getElementById('Branch');
            const storeSelect = document.getElementById('Store');
            const selectedStore = "{{ old('Store') }}";

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
                                    if (store.Id == selectedStore) {
                                        option.selected = true;
                                    }
                                    storeSelect.appendChild(option);
                                });
                            }
                        });
                }
            });

            // If editing and branch is already selected, trigger change
            if (branchSelect.value) {
                branchSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categorySelect = document.getElementById('Category');
            const subcategorySelect = document.getElementById('Subcategory');
            const itemSelect = document.getElementById('Item');

            // When category changes, load subcategories and items directly under the category
            categorySelect.addEventListener('change', function () {
                const categoryId = this.value;
                subcategorySelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
                itemSelect.innerHTML = '<option value="">-- Select Item --</option>';

                if (categoryId) {
                    // Load subcategories
                    fetch(`/inventory/get-subcategories?category_id=${categoryId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.length > 0) {
                                data.forEach(subcat => {
                                    const option = document.createElement('option');
                                    option.value = subcat.Id;
                                    option.text = subcat.Name;
                                    subcategorySelect.appendChild(option);
                                });
                            }
                        });

                    // Load items directly under the category
                    fetch(`/inventory/get-items?category_id=${categoryId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.length > 0) {
                                data.forEach(item => {
                                    const option = document.createElement('option');
                                    option.value = item.Id;
                                    option.text = item.ItemName;
                                    itemSelect.appendChild(option);
                                });
                            }
                        });
                }
            });

            // When subcategory changes, load items for that subcategory
            subcategorySelect.addEventListener('change', function () {
                const subcategoryId = this.value;
                itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
                if (subcategoryId) {
                    fetch(`/inventory/info/get-items?subcategory_id=${subcategoryId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.length > 0) {
                                data.forEach(item => {
                                    const option = document.createElement('option');
                                    option.value = item.Id;
                                    option.text = item.ItemName;
                                    itemSelect.appendChild(option);
                                });
                            }
                        });
                } else {
                    // If subcategory is cleared, reload items for the selected category
                    const categoryId = categorySelect.value;
                    if (categoryId) {
                        fetch(`/inventory/get-items?category_id=${categoryId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.length > 0) {
                                    data.forEach(item => {
                                        const option = document.createElement('option');
                                        option.value = item.Id;
                                        option.text = item.ItemName;
                                        itemSelect.appendChild(option);
                                    });
                                }
                            });
                    }
                }
            });

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


        });

       

        document.addEventListener('DOMContentLoaded', function () {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('LastReceived').setAttribute('max', today);
        });




    </script>
@endsection
