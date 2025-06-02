@extends('layouts.app')

@section('title', 'New Inter-Branch Requisition')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container mt-4">
        <h4 class="fw-bold mb-3">🔄 New Inter-Branch Requisition</h4>

        <form action="{{ route('interbranchrequisition.store') }}" method="POST">
            @csrf

            <div class="card shadow">
                <div class="card-header bg-light fw-bold">➕ Request Stock from Another Branch</div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Requesting Branch</label>
                            <select name="FromBranch" class="form-select" required>
                                <option value="">Select Branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">To Branch</label>
                            <select name="ToBranch" class="form-select" required>
                                <option value="">Select Branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="date" name="CreatedOn" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary" id="addItemBtn">➕ Add Item</button>
                    </div>

                    <div id="itemsContainer"></div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success">📤 Submit Requisition</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <template id="itemTemplate">
        <div class="card mb-3 item-entry">
            <div class="card-body border">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select name="Category" class="form-select category-select" required>
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->Id }}">{{ $category->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Subcategory</label>
                        <select name="Subcategory" class="form-select subcategory-select">
                            <option value="">-- Select Subcategory --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Item</label>
                        <select name="Item" class="form-select item-select" required>
                            <option value="">-- Select Item --</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Item Code</label>
                        <input type="text" id="ItemCodeInput" name="ItemCode" class="form-control item-code" readonly>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">UOM</label>
                        <select name="UOM" class="form-select" required>
                            <option value="">Select UOM</option>
                            @foreach ($uoms as $uom)
                                <option value="{{ $uom->Id }}">{{ $uom->Code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Requested Qty</label>
                        <input type="number" name="RequestedQty" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="Remarks" class="form-control">
                    
                </div>
            </div>
        </div>
    </template>

    @push('scripts')
        <script>
            let itemCounter = 0;

            document.getElementById('addItemBtn').addEventListener('click', function () {
                const template = document.getElementById('itemTemplate');
                const clone = template.content.cloneNode(true);
                
                // Update the name attributes to include unique indices
                const selects = clone.querySelectorAll('select, input');
                selects.forEach(element => {
                    if (element.name && element.name.includes('items[]')) {
                        element.name = element.name.replace('items[]', `items[${itemCounter}]`);
                    }
                });
                
                document.getElementById('itemsContainer').appendChild(clone);
                itemCounter++;
            });

            // Event delegation for dynamic elements
            document.addEventListener('change', function (e) {
                // Handle category selection
                if (e.target.classList.contains('category-select')) {
                    const categoryId = e.target.value;
                    const entry = e.target.closest('.item-entry');
                    const subcategorySelect = entry.querySelector('.subcategory-select');
                    const itemSelect = entry.querySelector('.item-select');
                    const itemCodeInput = entry.querySelector('.item-code');

                    // Reset dependent fields
                    subcategorySelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
                    subcategorySelect.disabled = false;
                    itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
                    itemSelect.disabled = false;
                    itemCodeInput.value = '';

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
                            })
                            .catch(error => {
                                console.error('Error fetching subcategories:', error);
                            });

                        // Load items directly under the category
                        fetch(`/inventory/get-items?category_id=${categoryId}`)
                            .then(response => response.json())
                            .then(data => {
                                console.log('Full API response:', data); // Keep this one debug log
                                if (data && data.length > 0) {
                                    data.forEach(item => {
                                        console.log('Single item object:', item); // Keep this one debug log
                                        const option = document.createElement('option');
                                        option.value = item.Id;
                                        option.text = item.ItemName;
                                        option.setAttribute('data-code', item.ItemCode);
                                        itemSelect.appendChild(option);
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching items:', error);
                            });
                    }
                }

                // Handle subcategory selection
                if (e.target.classList.contains('subcategory-select')) {
                    const subcategoryId = e.target.value;
                    const entry = e.target.closest('.item-entry');
                    const itemSelect = entry.querySelector('.item-select');
                    const itemCodeInput = entry.querySelector('.item-code');
                    const categorySelect = entry.querySelector('.category-select');

                    // Reset item field
                    itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
                    itemCodeInput.value = '';

                    if (subcategoryId) {
                        // Load items for the selected subcategory
                        fetch(`/inventory/get-items?subcategory_id=${subcategoryId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data && data.length > 0) {
                                    data.forEach(item => {
                                        const option = document.createElement('option');
                                        option.value = item.Id;
                                        option.text = item.ItemName;
                                        option.setAttribute('data-code', item.ItemCode);
                                        itemSelect.appendChild(option);
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching items:', error);
                            });
                    } else {
                        // If subcategory is cleared, reload items for the selected category
                        const categoryId = categorySelect.value;
                        if (categoryId) {
                            fetch(`/inventory/get-items?category_id=${categoryId}`)
                                .then(response => response.json())
                                .then(data => {
                                    if (data && data.length > 0) {
                                        data.forEach(item => {
                                            const option = document.createElement('option');
                                            option.value = item.Id;
                                            option.text = item.ItemName;
                                            option.setAttribute('data-code', item.ItemCode);
                                            itemSelect.appendChild(option);
                                        });
                                    }
                                })
                                .catch(error => {
                                    console.error('Error fetching items:', error);
                                });
                        }
                    }
                }

                // Handle item selection
                if (e.target.classList.contains('item-select')) {
                    const selectedOption = e.target.selectedOptions[0];
                    const itemCode = selectedOption ? selectedOption.getAttribute('data-code') || '' : '';
                    const entry = e.target.closest('.item-entry');
                    const itemCodeInput = entry.querySelector('.item-code');
                    itemCodeInput.value = itemCode;
                }
            });

            // Add first item automatically
            document.getElementById('addItemBtn').click();
        </script>
    @endpush
@endsection