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
        <h4 class="fw-bold mb-3">New Inter-Branch Requisition</h4>

        <form action="{{ route('interbranchrequisition.store') }}" method="POST">
            @csrf

            <div class="card shadow">
                <div class="card-header bg-light fw-bold">➕ Request Stock from Another Branch</div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Requesting Branch</label>
                            <select name="FromBranch" id="from_branch_select" class="form-select" required>
                                <option value="">Select Branch</option>
                                @foreach ($branches as $branch)
                                    <option
                                        value="{{ $branch->Id }}" {{ old('FromBranch') == $branch->Id ? 'selected' : '' }}>
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
                                        value="{{ $branch->Id }}" {{ old('ToBranch') == $branch->Id ? 'selected' : '' }}>
                                        {{ $branch->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="date" name="CreatedOn" class="form-control"
                                       value="{{ old('CreatedOn', now()->toDateString()) }}" required>
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
                        {{-- Categories will be dynamically populated by JS --}}
                        <select name="items[__INDEX__][Category]" class="form-select category-select" data-initial="" required>
                            <option value="">-- Select Category --</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select name="items[__INDEX__][Subcategory]" class="form-select subcategory-select" data-initial="">
                            <option value="">-- Select Subcategory --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Item</label>
                        <select name="items[__INDEX__][Item]" class="form-select item-select" data-initial="" required>
                            <option value="">-- Select Item --</option>
                        </select>
                        {{-- Hidden input for item_name, required for server-side validation error message --}}
                        <input type="hidden" name="items[__INDEX__][item_name]" class="item-name-hidden">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Item Code</label>
                        <input type="text" name="items[__INDEX__][ItemCode]" class="form-control item-code" value="" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">UOM</label>
                        <input type="text" class="form-control item-uom" readonly>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Requested Qty</label>
                        <input type="number" name="items[__INDEX__][RequestedQty]" class="form-control item-qty" value="1" min="1" required>
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

            // New: Function to populate categories based on selected branch's stock
            function populateCategoriesByBranch(entry, selectedCategory = null, callback = null) {
                const categorySelect = entry.querySelector('.category-select');
                const subcategorySelect = entry.querySelector('.subcategory-select');
                const itemSelect = entry.querySelector('.item-select');
                const fromBranchSelect = document.getElementById('from_branch_select');
                const fromBranchId = fromBranchSelect.value;

                categorySelect.innerHTML = ''; // Clear previous options
                categorySelect.disabled = true; // Disable until loaded
                subcategorySelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
                subcategorySelect.disabled = true;
                itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
                itemSelect.disabled = true;
                entry.querySelector('.item-code').value = '';
                entry.querySelector('.item-uom').value = '';
                entry.querySelector('.item-name-hidden').value = '';

                if (!fromBranchId) {
                    categorySelect.innerHTML = '<option value="">Select Requesting Branch First</option>';
                    return callback?.();
                }

                // Using the specific route: /inventory/get-categories-by-branch
                fetch(`/inventory/get-categories-by-branch?from_branch_id=${fromBranchId}`)
                    .then(response => response.json())
                    .then(data => {
                        categorySelect.disabled = false;
                        if (data.categories.length === 0) {
                            categorySelect.innerHTML = `<option value="">${data.message || '-- No Categories Available --'}</option>`;
                            categorySelect.disabled = true;
                        } else {
                            categorySelect.innerHTML = '<option value="">-- Select Category --</option>';
                            data.categories.forEach(cat => {
                                const option = new Option(cat.Name, cat.Id);
                                if (selectedCategory == cat.Id) option.selected = true;
                                categorySelect.add(option);
                            });
                        }
                        callback?.();
                    })
                    .catch(error => {
                        console.error('Error fetching categories by branch:', error);
                        categorySelect.innerHTML = '<option value="">Error loading categories</option>';
                        categorySelect.disabled = true;
                        callback?.();
                    });
            }


            // Modified: Function to populate subcategories by branch and category
            function populateSubcategoriesByBranchAndCategory(entry, selectedSubcat = null, callback = null) {
                const categorySelect = entry.querySelector('.category-select');
                const subcategorySelect = entry.querySelector('.subcategory-select');
                const itemSelect = entry.querySelector('.item-select');
                const fromBranchSelect = document.getElementById('from_branch_select');

                const categoryId = categorySelect.value;
                const fromBranchId = fromBranchSelect.value;

                subcategorySelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
                subcategorySelect.disabled = true;
                itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
                itemSelect.disabled = true;
                entry.querySelector('.item-code').value = '';
                entry.querySelector('.item-uom').value = '';
                entry.querySelector('.item-name-hidden').value = '';


                if (!categoryId || !fromBranchId) {
                    return callback?.();
                }

                // Using the specific route: /inventory/get-subcategories-by-branch-and-category
                fetch(`/inventory/get-subcategories-by-branch-and-category?from_branch_id=${fromBranchId}&category_id=${categoryId}`)
                    .then(response => response.json())
                    .then(data => {
                        subcategorySelect.disabled = false;
                        if (data.subcategories.length === 0) {
                            subcategorySelect.innerHTML = `<option value="">${data.message || '-- No Subcategories Available --'}</option>`;
                            // If no subcategories, load items directly from the parent category
                            populateItemsByBranchAndCategoryOrSubcategory(entry, null, categoryId);
                            subcategorySelect.disabled = true; // Keep subcategory disabled as there are no options
                        } else {
                            subcategorySelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
                            data.subcategories.forEach(subcat => {
                                const option = new Option(subcat.Name, subcat.Id);
                                if (selectedSubcat == subcat.Id) option.selected = true;
                                subcategorySelect.add(option);
                            });
                            itemSelect.innerHTML = '<option value="">Select Subcategory</option>';
                            itemSelect.disabled = true;
                        }
                        callback?.();
                    })
                    .catch(error => {
                        console.error('Error fetching subcategories by branch and category:', error);
                        subcategorySelect.innerHTML = '<option value="">Error loading subcategories</option>';
                        subcategorySelect.disabled = true;
                        callback?.();
                    });
            }

            // Renamed: Function to populate items by branch and category/subcategory
            function populateItemsByBranchAndCategoryOrSubcategory(entry, selectedItem = null, fallbackCategoryId = null) {
                const categorySelect = entry.querySelector('.category-select');
                const subcategorySelect = entry.querySelector('.subcategory-select');
                const itemSelect = entry.querySelector('.item-select');
                const fromBranchSelect = document.getElementById('from_branch_select');

                const categoryId = fallbackCategoryId || categorySelect.value; // Use fallback if provided
                const subcategoryId = subcategorySelect.value;
                const fromBranchId = fromBranchSelect.value;

                itemSelect.innerHTML = '';
                itemSelect.disabled = true;
                entry.querySelector('.item-code').value = '';
                entry.querySelector('.item-uom').value = '';
                entry.querySelector('.item-name-hidden').value = '';

                let fetchUrl = '';
                // Using the specific route: /inventory/get-items
                if (fromBranchId && (subcategoryId || categoryId)) {
                    fetchUrl = `/inventory/get-items?`;
                    if (subcategoryId) {
                        fetchUrl += `subcategory_id=${subcategoryId}`;
                    } else {
                        fetchUrl += `category_id=${categoryId}`;
                    }
                    fetchUrl += `&from_branch_id=${fromBranchId}`;
                } else {
                    itemSelect.innerHTML = '<option value="">Select Category/Subcategory</option>';
                    return;
                }

                fetch(fetchUrl)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        itemSelect.disabled = false;
                        if (data.items.length === 0) {
                            itemSelect.innerHTML = `<option value="">${data.message || '-- No Items Available --'}</option>`;
                            itemSelect.disabled = true;
                        } else {
                            itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
                        }

                        const currentItem = selectedItem || itemSelect.getAttribute('data-initial');
                        let hasCurrent = false;

                        data.items.forEach(item => {
                            const option = new Option(item.ItemName, item.Id);
                            if (item.Id == currentItem) {
                                option.selected = true;
                                hasCurrent = true;
                            }
                            itemSelect.add(option);
                        });

                        if (currentItem && !hasCurrent && data.items.length > 0) {
                             const originalItemName = entry.querySelector('.item-name-hidden').value || '[Original Item]';
                             const option = new Option(originalItemName, currentItem, true, true);
                             itemSelect.add(option);
                             fetchItemCodeAndUom(currentItem, entry);
                         } else if (itemSelect.value) {
                             fetchItemCodeAndUom(itemSelect.value, entry);
                         } else {
                             entry.querySelector('.item-code').value = '';
                             entry.querySelector('.item-uom').value = '';
                             entry.querySelector('.item-name-hidden').value = '';
                         }

                    })
                    .catch(error => {
                        console.error('Error fetching items:', error);
                        itemSelect.innerHTML = '<option value="">Error loading items</option>';
                        itemSelect.disabled = true;
                        entry.querySelector('.item-code').value = 'Error';
                        entry.querySelector('.item-uom').value = 'Error';
                        entry.querySelector('.item-name-hidden').value = '';
                    });
            }

            // Function to fetch Item Code and UOM (uses the exact route format /items/code/{Id})
            function fetchItemCodeAndUom(itemId, entry) {
                if (!itemId) {
                    entry.querySelector('.item-code').value = '';
                    entry.querySelector('.item-uom').value = '';
                    entry.querySelector('.item-name-hidden').value = '';
                    return;
                }
                // Using the specific route: /items/code/{Id}
                fetch(`/items/code/${itemId}`)
                    .then(response => response.json())
                    .then(data => {
                        entry.querySelector('.item-code').value = data.item_code ?? 'N/A';
                        entry.querySelector('.item-uom').value = data.item_uom ?? 'N/A';
                        const selectedText = entry.querySelector('.item-select option:checked')?.textContent;
                        entry.querySelector('.item-name-hidden').value = selectedText || '';
                    })
                    .catch(error => {
                        console.error('Error fetching item data:', error);
                        entry.querySelector('.item-code').value = 'Error';
                        entry.querySelector('.item-uom').value = 'Error';
                        entry.querySelector('.item-name-hidden').value = '';
                    });
            }

            function addItem(oldValues = null) {
                const template = document.getElementById('itemTemplate');
                const clone = template.content.cloneNode(true);
                const newEntry = clone.firstElementChild;

                // Replace __INDEX__ in all names
                newEntry.querySelectorAll('[name]').forEach(el => {
                    el.name = el.name.replace('__INDEX__', itemCounter);
                });

                newEntry.querySelector('.remove-item-btn').addEventListener('click', function () {
                    this.closest('.item-entry').remove();
                });

                document.getElementById('itemsContainer').appendChild(newEntry);

                const categorySelect = newEntry.querySelector('.category-select');
                const subcategorySelect = newEntry.querySelector('.subcategory-select');
                const itemSelect = newEntry.querySelector('.item-select');

                // Set initial state for new rows
                categorySelect.disabled = true;
                subcategorySelect.disabled = true;
                itemSelect.disabled = true;
                categorySelect.innerHTML = '<option value="">Select Requesting Branch First</option>';
                subcategorySelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
                itemSelect.innerHTML = '<option value="">-- Select Item --</option>';

                // Handle old values and trigger initial population
                if (oldValues) {
                    newEntry.querySelector('.item-qty').value = oldValues.RequestedQty || '1';
                    newEntry.querySelector('[name*="[Remarks]"]').value = oldValues.Remarks || '';

                    // Store old values for data-initial attributes for subsequent cascade
                    categorySelect.setAttribute('data-initial', oldValues.Category || '');
                    subcategorySelect.setAttribute('data-initial', oldValues.Subcategory || '');
                    itemSelect.setAttribute('data-initial', oldValues.Item || '');
                    newEntry.querySelector('.item-code').value = oldValues.ItemCode || '';
                    newEntry.querySelector('.item-uom').value = oldValues.item_uom || '';
                    newEntry.querySelector('.item-name-hidden').value = oldValues.item_name || '';

                    // If a branch was already selected (e.g., from old('FromBranch')), initiate population
                    const initialFromBranchId = document.getElementById('from_branch_select').value;
                    if (initialFromBranchId) {
                         populateCategoriesByBranch(newEntry, oldValues.Category, () => {
                             // After categories load, if old category was set, load subcategories
                             if (oldValues.Category) {
                                 populateSubcategoriesByBranchAndCategory(newEntry, oldValues.Subcategory, () => {
                                     // After subcategories load, if old item was set, load items
                                     if (oldValues.Item) {
                                         populateItemsByBranchAndCategoryOrSubcategory(newEntry, oldValues.Item);
                                     }
                                 });
                             } else {
                                 // If no specific category in oldValues, but branch is selected, still try to populate items
                                 populateItemsByBranchAndCategoryOrSubcategory(newEntry, oldValues.Item);
                             }
                         });
                    }
                }

                itemCounter++;
            }

            document.addEventListener('DOMContentLoaded', () => {
                const initialFromBranchValue = document.getElementById('from_branch_select').value;

                @if (old('items'))
                    @foreach (old('items', []) as $index => $oldItem)
                        addItem(@json($oldItem));
                    @endforeach
                @else
                    addItem();
                @endif


                document.getElementById('addItemBtn').addEventListener('click', function () {
                    addItem();
                });

                document.addEventListener('change', function (e) {
                    const target = e.target;
                    const entry = target.closest('.item-entry'); // May be null if event is from #from_branch_select

                    // When From Branch changes (this event target is NOT inside an item-entry normally)
                    if (target.id === 'from_branch_select') {
                        document.querySelectorAll('.item-entry').forEach(itemEntry => {
                            // Reset all dropdowns in this entry
                            itemEntry.querySelector('.category-select').value = '';
                            itemEntry.querySelector('.subcategory-select').value = '';
                            itemEntry.querySelector('.item-select').value = '';
                            itemEntry.querySelector('.category-select').setAttribute('data-initial', '');
                            itemEntry.querySelector('.subcategory-select').setAttribute('data-initial', '');
                            itemEntry.querySelector('.item-select').setAttribute('data-initial', '');

                            itemEntry.querySelector('.item-code').value = '';
                            itemEntry.querySelector('.item-uom').value = '';
                            itemEntry.querySelector('.item-name-hidden').value = '';

                            // Trigger the cascade: populate categories based on new branch
                            populateCategoriesByBranch(itemEntry);
                        });
                        return; // Stop here, no need to proceed to category/subcategory/item specific logic
                    }

                    // For events within an item-entry (category, subcategory, item selects)
                    if (entry) {
                        // When Parent Category changes
                        if (target.classList.contains('category-select')) {
                            const subcategorySelect = entry.querySelector('.subcategory-select');
                            const itemSelect = entry.querySelector('.item-select');
                            subcategorySelect.setAttribute('data-initial', '');
                            itemSelect.setAttribute('data-initial', '');

                            populateSubcategoriesByBranchAndCategory(entry, null, () => {
                                // If no subcategories are available for the selected parent category,
                                // or if the subcategory dropdown becomes disabled, then try to load items
                                // directly based on the parent category.
                                if (subcategorySelect.options.length <= 1 || subcategorySelect.disabled) {
                                    populateItemsByBranchAndCategoryOrSubcategory(entry, null, target.value); // Pass categoryId as fallback
                                }
                            });
                        }

                        // When Subcategory changes
                        if (target.classList.contains('subcategory-select')) {
                            const itemSelect = entry.querySelector('.item-select');
                            itemSelect.setAttribute('data-initial', '');
                            populateItemsByBranchAndCategoryOrSubcategory(entry);
                            entry.querySelector('.item-code').value = '';
                            entry.querySelector('.item-uom').value = '';
                            entry.querySelector('.item-name-hidden').value = '';
                        }

                        // When Item changes
                        if (target.classList.contains('item-select')) {
                            fetchItemCodeAndUom(target.value, entry);
                        }
                    }
                });
            });
        </script>
    @endpush
@endsection