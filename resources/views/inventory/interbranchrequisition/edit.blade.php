@extends('layouts.app')

@section('title', 'Edit Inter-Branch Requisition')

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
        <h4 class="fw-bold mb-3"> Edit Inter-Branch Requisition</h4>

        <form action="{{ route('interbranchrequisition.update', $item->Id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card shadow">
                <div class="card-header bg-light fw-bold">✏️ Edit Stock Request</div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Requesting Branch</label>
                            <select name="FromBranch" class="form-select" required>
                                <option value="">Select Branch</option>
                                @foreach ($branches as $branch)
                                    <option
                                        value="{{ $branch->Id }}" {{ old('FromBranch', $item->FromBranch) == $branch->Id ? 'selected' : '' }}>
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
                                    <option
                                        value="{{ $branch->Id }}" {{ old('ToBranch', $item->ToBranch) == $branch->Id ? 'selected' : '' }}>
                                        {{ $branch->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="date" name="CreatedOn" class="form-control"
                                   value="{{ old('CreatedOn', \Carbon\Carbon::parse($item->CreatedOn)->format('Y-m-d')) }}"
                                   required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary" id="addItemBtn">➕ Add Item</button>
                    </div>

                    <div id="itemsContainer">
                        @foreach($item->items as $index => $line)
                            @php
                                $selectedItem = $line->item;
                                $selectedSubcategory = $selectedItem->category ?? null;
                                $selectedCategory = $selectedSubcategory && $selectedSubcategory->parent ? $selectedSubcategory->parent : null;
                                $selectedCategoryId = $selectedCategory->Id ?? '';
                                $selectedSubcategoryId = $selectedSubcategory->Id ?? '';
                            @endphp
                            <div class="card mb-3 item-entry">
                                <div class="card-body border">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-2">
                                            <label class="form-label">Parent Category</label>
                                            <select name="items[{{ $index }}][Category]"
                                                    class="form-select category-select" required>
                                                <option value="">-- Select Category --</option>
                                                @foreach($categories as $category)
                                                    <option
                                                        value="{{ $category->Id }}" {{ $selectedCategoryId == $category->Id ? 'selected' : '' }}>
                                                        {{ $category->Name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Category</label>
                                            <select name="items[{{ $index }}][Subcategory]"
                                                    class="form-select subcategory-select"
                                                    data-initial="{{ $selectedSubcategoryId }}">
                                                <option value="">-- Select Subcategory --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Item</label>
                                            <select name="items[{{ $index }}][Item]" class="form-select item-select"
                                                    data-initial="{{ $selectedItem->Id ?? '' }}" required>
                                                <option value="">-- Select Item --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label">UOM</label>
                                            <select name="items[{{ $index }}][UOM]" class="form-select" required>
                                                <option value="">Select UOM</option>
                                                @foreach ($uoms as $uom)
                                                    <option
                                                        value="{{ $uom->Id }}" {{ $line->UOM == $uom->Id ? 'selected' : '' }}>
                                                        {{ $uom->Code }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label">Requested Qty</label>
                                            <input type="number" name="items[{{ $index }}][RequestedQty]"
                                                   class="form-control" value="{{ $line->RequestedQty }}" min="1"
                                                   required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Remarks</label>
                                            <input type="text" name="items[{{ $index }}][Remarks]" class="form-control"
                                                   value="{{ $line->Remarks }}">
                                        </div>
                                        <div class="col-md-1 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger btn-sm remove-item-btn">✖
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success">Update Requisition</button>
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
                        <select name="items[__INDEX__][Category]" class="form-select category-select" required>
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->Id }}">{{ $category->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Subcategory</label>
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
                    <div class="col-md-1">
                        <label class="form-label">UOM</label>
                        <select name="items[__INDEX__][UOM]" class="form-select" required>
                            <option value="">Select UOM</option>
                            @foreach ($uoms as $uom)
                                <option value="{{ $uom->Id }}">{{ $uom->Code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Requested Qty</label>
                        <input type="number" name="items[__INDEX__][RequestedQty]" class="form-control" value="1"
                               min="1" required>
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
            let itemCounter = {{ count($item->items) }};

            function populateSubcategories(categorySelect, subcategorySelect, selectedSubcat = null, callback = null) {
                const categoryId = categorySelect.value;
                if (!categoryId) {
                    subcategorySelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
                    if (callback) callback();
                    return;
                }
                fetch(`/inventory/get-subcategories?category_id=${categoryId}`)
                    .then(response => response.json())
                    .then(data => {
                        subcategorySelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
                        data.forEach(subcat => {
                            const option = document.createElement('option');
                            option.value = subcat.Id;
                            option.text = subcat.Name;
                            if (selectedSubcat && selectedSubcat == subcat.Id) {
                                option.selected = true;
                            }
                            subcategorySelect.appendChild(option);
                        });
                        if (callback) callback();
                    });
            }

            function populateItems(subcategorySelect, itemSelect, selectedItem = null) {
                const subcatId = subcategorySelect.value;
                if (!subcatId) {
                    itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
                    return;
                }
                fetch(`/inventory/get-items?subcategory_id=${subcatId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Always include the originally selected item if not present in the new list
                        let currentItem = selectedItem || itemSelect.getAttribute('data-initial');
                        let hasCurrent = data.some(item => item.Id == currentItem);

                        itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.Id;
                            option.text = item.ItemName;
                            if (currentItem && currentItem == item.Id) {
                                option.selected = true;
                            }
                            itemSelect.appendChild(option);
                        });

                        // If initial/current item is not in data, add it as an option (retain it)
                        if (currentItem && !hasCurrent) {
                            const option = document.createElement('option');
                            option.value = currentItem;
                            option.text = '[Original Item]';
                            option.selected = true;
                            itemSelect.appendChild(option);
                        }
                    });
            }

            // Initial population for subcategory and item selects for existing rows
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.item-entry').forEach(function (entry) {
                    const categorySelect = entry.querySelector('.category-select');
                    const subcategorySelect = entry.querySelector('.subcategory-select');
                    const itemSelect = entry.querySelector('.item-select');
                    const selectedCategory = categorySelect.value;
                    const selectedSubcategory = subcategorySelect.getAttribute('data-initial');
                    const selectedItem = itemSelect.getAttribute('data-initial');
                    if (selectedCategory) {
                        populateSubcategories(categorySelect, subcategorySelect, selectedSubcategory, function () {
                            if (selectedSubcategory) {
                                populateItems(subcategorySelect, itemSelect, selectedItem);
                            }
                        });
                    }
                });
            });

            document.getElementById('addItemBtn').addEventListener('click', function () {
                const template = document.getElementById('itemTemplate');
                const clone = template.content.cloneNode(true);

                // Replace __INDEX__ in all names
                const fields = clone.querySelectorAll('[name]');
                fields.forEach(element => {
                    element.name = element.name.replace('__INDEX__', itemCounter);
                });

                // Add remove button event
                clone.querySelector('.remove-item-btn').addEventListener('click', function () {
                    this.closest('.item-entry').remove();
                });

                document.getElementById('itemsContainer').appendChild(clone);
                itemCounter++;
            });

            document.addEventListener('change', function (e) {
                // Category changed
                if (e.target.classList.contains('category-select')) {
                    const entry = e.target.closest('.item-entry');
                    const subcategorySelect = entry.querySelector('.subcategory-select');
                    const itemSelect = entry.querySelector('.item-select');
                    populateSubcategories(e.target, subcategorySelect, null, function () {
                        itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
                    });
                }
                // Subcategory changed
                if (e.target.classList.contains('subcategory-select')) {
                    const entry = e.target.closest('.item-entry');
                    const itemSelect = entry.querySelector('.item-select');
                    populateItems(e.target, itemSelect);
                }
            });

            // When user focuses the item select, always repopulate with current subcategory
            document.addEventListener('focusin', function (e) {
                if (e.target.classList.contains('item-select')) {
                    const entry = e.target.closest('.item-entry');
                    const subcategorySelect = entry.querySelector('.subcategory-select');
                    populateItems(subcategorySelect, e.target, e.target.value);
                }
            });
        </script>
    @endpush
@endsection
