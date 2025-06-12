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
        <h4 class="fw-bold mb-3"> New Inter-Branch Requisition</h4>

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
                                    <option value="{{ $branch->Id }}" {{ old('FromBranch') == $branch->Id ? 'selected' : '' }}>
                                        {{ $branch->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">To Branch</label>
                            <select name="ToBranch" class="form-select" required>
                                <option value="">Select Branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->Id }}" {{ old('ToBranch') == $branch->Id ? 'selected' : '' }}>
                                        {{ $branch->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="date" name="CreatedOn" class="form-control" value="{{ old('CreatedOn', now()->toDateString()) }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary" id="addItemBtn">➕ Add Item</button>
                    </div>

                    <div id="itemsContainer"></div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success">Submit Requisition</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Item Template -->
    <template id="itemTemplate">
        <div class="card mb-3 item-entry">
            <div class="card-body border">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Parent Category</label>
                        <select name="items[__INDEX__][Category]" class="form-select category-select" required>
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->Id }}">{{ $category->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select name="items[__INDEX__][Subcategory]" class="form-select subcategory-select">
                            <option value="">-- Select Subcategory --</option>
                        </select>
                    </div>
<div class="col-md-3">
    <label class="form-label">Item</label>
    <select name="items[__INDEX__][Item]" class="form-select item-select" required>
        <option value="">-- Select Item --</option>
    </select>
</div>

<div class="col-md-2">
    <label class="form-label">Item Code</label>
    <input type="text" name="items[__INDEX__][ItemCode]" class="form-control item-code" value="" readonly>
</div>



    <div class="col-md-2">
    <label class="form-label"> UOM </label>
    <input type="text" class="form-control item-uom" readonly>
</div>
                    <div class="col-md-1">
                        <label class="form-label">Requested Qty</label>
                        <input type="number" name="items[__INDEX__][RequestedQty]" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="items[__INDEX__][Remarks]" class="form-control">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-item-btn">✖</button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    @push('scripts')
        <script>
            let itemCounter = 0;

            function addItem(values = {}) {
                const template = document.getElementById('itemTemplate');
                const clone = template.content.cloneNode(true);

                // Replace __INDEX__ in all names
                const fields = clone.querySelectorAll('[name]');
                fields.forEach(element => {
                    element.name = element.name.replace('__INDEX__', itemCounter);
                    // Set old value if present
                    if (values[element.name]) {
                        element.value = values[element.name];
                    }
                });

                clone.querySelector('.remove-item-btn').addEventListener('click', function () {
                    this.closest('.item-entry').remove();
                });

                document.getElementById('itemsContainer').appendChild(clone);
                itemCounter++;
            }

            document.getElementById('addItemBtn').addEventListener('click', function () {
                addItem();
            });

            document.addEventListener('change', function (e) {
                if (e.target.classList.contains('category-select')) {
                    const categoryId = e.target.value;
                    const entry = e.target.closest('.item-entry');
                    const subcategorySelect = entry.querySelector('.subcategory-select');
                    const itemSelect = entry.querySelector('.item-select');

                    subcategorySelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
                    subcategorySelect.disabled = false;
                    itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
                    itemSelect.disabled = false;

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

                        // Load items for the category
                        fetch(`/inventory/get-items?category_id=${categoryId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data && data.length > 0) {
                                    data.forEach(item => {
                                        const option = document.createElement('option');
                                        option.value = item.Id;
                                        option.text = item.ItemName;
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
                    const categorySelect = entry.querySelector('.category-select');

                    itemSelect.innerHTML = '<option value="">-- Select Item --</option>';

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
            });

            // Fetch and display Item Code based on selected Item
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('item-select')) {
        const itemSelect = e.target;
        const itemId = itemSelect.value;
        const entry = itemSelect.closest('.item-entry');

        const itemCodeInput = entry.querySelector('.item-code');
        const itemUomInput = entry.querySelector('.item-uom');

        if (itemCodeInput) itemCodeInput.value = '';
        if (itemUomInput) itemUomInput.value = '';

        if (itemId) {
            fetch(`/inventory/items/code/${itemId}`)
                .then(response => response.json())
                .then(data => {
                    if (itemCodeInput) itemCodeInput.value = data.item_code ?? 'N/A';
                    if (itemUomInput) itemUomInput.value = data.item_uom ?? 'N/A';
                })
                .catch(error => {
                    console.error('Error fetching item data:', error);
                    if (itemCodeInput) itemCodeInput.value = 'Error';
                    if (itemUomInput) itemUomInput.value = 'Error';
                });
        }
    }
});

$('select.item-dropdown').on('change', function () {
    let itemId = $(this).val();
    let row = $(this).closest('tr');

    $.get('/interbranchrequisition/getItemCode/' + itemId, function (response) {
        row.find('input.item-code').val(response.item_code);
        row.find('.uom-display').text(response.item_uom); // if you're showing UOM as text
    });
});



            // Add first item automatically if no old inputs (fresh form)
            @if (!old('items'))
                addItem();
            @else
                @foreach (old('items', []) as $index => $oldItem)
                    addItem(@json($oldItem));
                @endforeach
            @endif
        </script>
    @endpush
@endsection