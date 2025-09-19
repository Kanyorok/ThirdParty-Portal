@extends('layouts.app')
@section('title', 'Tender Initiation Form')
@section('content')
<style>
    .is-invalid,
    .form-control.is-invalid,
    .form-select.is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, .25) !important;
    }

    .invalid-feedback.d-block {
        color: #dc3545;
        font-weight: bold;
        font-size: 0.95em;
    }
</style>
<div class="container mt-4">
    <h4 class="mb-4">Tender Initiation Form</h4>
    <form action="{{ route('initiatetender.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('POST')

        <!-- Title -->
        <div class="mb-3">
            <label for="tenderTitle" class="form-label fw-bold">Tender Title: <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" id="tenderTitle" name="title" placeholder="Enter tender title" value="{{ old('title') }}" required>
            @error('title')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Tender Type -->
        <div class="mb-3">
            <label class="form-label fw-bold">Tender Type: <span class="text-danger">*</span></label>
            <div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input @error('tender_type') is-invalid @enderror" type="radio" name="tender_type" id="openTender" value="op" {{ old('tender_type') == 'op' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="openTender">Open Tender (Public posting)</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input @error('tender_type') is-invalid @enderror" type="radio" name="tender_type" id="restrictedTender" value="rs" {{ old('tender_type') == 'rs' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="restrictedTender">Restricted Tender (Selected vendors only)</label>
                </div>
            </div>
            @error('tender_type')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Category and Requisition -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="tenderCategory" class="form-label fw-bold">Tender Category: <span class="text-danger">*</span></label>
                <select class="form-select @error('tender_category_id') is-invalid @enderror" id="tenderCategory" name="tender_category_id" required>
                    <option selected disabled>-- Select Category --</option>
                    @foreach ($tenderCategories as $item)
                    <option value="{{$item->Id}}" {{ old('tender_category_id') == $item->Id ? 'selected' : '' }}>{{$item->TenderCategory}}</option>
                    @endforeach
                </select>
                @error('tender_category_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="itemCategory" class="form-label fw-bold">Item Category <span class="text-danger">*</span></label>
                <select class="form-select @error('item_category_id') is-invalid @enderror" id="itemCategory" name="item_category_id" required>
                    <option selected disabled>-- Item Categories --</option>
                    @foreach ($allItemsCategories as $item)
                    <option value="{{$item->Id}}" {{ old('item_category_id') == $item->Id ? 'selected' : '' }}>{{$item->Name}}</option>
                    @endforeach
                </select>
                @error('item_category_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="currencyType" class="form-label fw-bold">Currency <span class="text-danger">*</span></label>
                <select class="form-select @error('currency_id') is-invalid @enderror" id="currencyType" name="currency_id" required>
                    <option selected disabled>-- Select Your Currency --</option>
                    @foreach ($allCurrency as $item)
                    <option value="{{$item->Id}}" {{ old('currency_id') == $item->Id ? 'selected' : '' }}>{{$item->Name}} ({{$item->Code}})</option>
                    @endforeach
                </select>
                @error('currency_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Tender Items Tabs -->
        <ul class="nav nav-tabs mt-4" id="itemEntryTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="fromPlan-tab" data-bs-toggle="tab" data-bs-target="#fromPlan"
                    type="button" role="tab">From Procurement Plan
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manualEntry"
                    type="button" role="tab">Manual Entry
                </button>
            </li>
        </ul>

        <div class="tab-content p-3 border border-top-0" id="itemEntryTabsContent">

            <!-- From Procurement Plan -->
            <div class="tab-pane fade show active" id="fromPlan" role="tabpanel">
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Procurement Plan:</label>
                    <select class="form-select" id="selectedProcurementPlan" name="procurement_plan_id"
                        onchange="loadPlanItemsForPlan()">
                        <option selected disabled>-- Choose Procurement Plan --</option>
                        @foreach ($procurementPlan as $item)
                        <option value="{{$item->PlanID}}">{{$item->Title}} - {{$item->ReferenceNumber}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="row mb-3">
                    <div class="col-md-9">
                        <label class="form-label fw-bold">Select Procurement Plan Item:</label>
                        <select class="form-select" id="planItemSelect">
                            <option selected disabled>-- Select Item --</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" onclick="addPlanItemToGrid()">➕ Add to
                            Grid
                        </button>
                    </div>
                </div>

                <!-- Dynamic Plan Items Grid -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Selected Items</label>
                    <table class="table table-bordered" id="planItemsGrid">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Planned Qty</th>
                                <th>Qty to Tender</th>
                                <th>Specs</th>
                                <th>PR Ref (optional)</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody name="plan_items">
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Manual Entry -->
            <div class="tab-pane fade" id="manualEntry" role="tabpanel">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Item Description</th>
                            <th>Qty</th>
                            <th>Specs</th>
                            <th>PR Ref (optional)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="manualItemsBody">
                        <!-- No default row; rows will be added dynamically -->
                    </tbody>
                </table>
                <button type="button" class="btn btn-sm btn-primary" onclick="addManualItemRow()">➕ Add Item</button>
            </div>
        </div>

        <!-- Scope -->
        <div class="mb-3">
            <label for="scopeOfWork" class="form-label fw-bold">Scope of Work <span class="text-danger">*</span></label>
            <textarea class="form-control @error('scope_of_work') is-invalid @enderror" id="scopeOfWork" name="scope_of_work" rows="3" required>{{ old('scope_of_work') }}</textarea>
            @error('scope_of_work')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Instructions -->
        <div class="mb-3">
            <label for="instructions" class="form-label fw-bold">Instructions to Bidders: <span class="text-danger">*</span></label>
            <textarea class="form-control @error('instructions') is-invalid @enderror" id="instructions" name="instructions" rows="3" required>{{ old('instructions') }}</textarea>
            @error('instructions')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Dates -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="submissionDeadline" class="form-label fw-bold">Submission Deadline: <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('submission_deadline') is-invalid @enderror" id="submissionDeadline" name="submission_deadline" value="{{ old('submission_deadline') }}" required>
                @error('submission_deadline')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="openingDate" class="form-label fw-bold">Opening Date: <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('opening_date') is-invalid @enderror" id="openingDate" name="opening_date" value="{{ old('opening_date') }}" required>
                @error('opening_date')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Upload -->
        <div class="mb-3">
            <label for="tenderDocuments" class="form-label fw-bold">Attach Tender Document:</label>
            <input class="form-control" type="file" id="tenderDocuments" name="documents[]" multiple>
        </div>

        <!-- Restricted Suppliers -->
        <div class="mb-3" id="restrictedSuppliersSection" style="display: none;">
            <label for="suppliersList" class="form-label fw-bold">Add Suppliers to Invite:</label>
            <select class="form-select" id="suppliersList" name="suppliers[]" multiple>
                <!-- Filled dynamically -->
            </select>
        </div>

        <!-- Buttons -->
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">Save Tender</button>
        </div>
    </form>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const openTender = document.getElementById('openTender');
        const restricted = document.getElementById('restrictedTender');
        const section = document.getElementById('restrictedSuppliersSection');

        openTender.addEventListener('change', () => section.style.display = 'none');
        restricted.addEventListener('change', () => section.style.display = 'block');

        updateManualItemSelects();
    });

    const allItemsWithCategoryIds = @json($allItemsWithCategoryIds);

    let selectedItemCategory = document.getElementById('itemCategory').value;

    function getFilteredItemOptions(categoryId) {
        let html = '<option selected disabled>-- Select Item --</option>';
        allItemsWithCategoryIds.forEach(item => {
            if (String(item.Category) === String(categoryId)) {
                html += `<option value="${item.Id}" data-item-category="${item.Category}">${item.ItemName}</option>`;
            }
        });
        return html;
    }

    let manualItemIndex = 1;

    function addManualItemRow() {
        const tableBody = document.getElementById('manualItemsBody');
        const row = document.createElement('tr');
        row.innerHTML = `
        <td>
            <select class="form-select manual-item-select" name="manual_items[${manualItemIndex}][item_id]" required>
                ${getFilteredItemOptions(selectedItemCategory)}
            </select>
        </td>
        <td>
            <input type="number" class="form-control" name="manual_items[${manualItemIndex}][qty]" value="1" min="1" required>
        </td>
        <td>
            <input type="file" class="form-control" name="manual_items[${manualItemIndex}][specs_file]">
        </td>
        <td>
            <input type="text" class="form-control" name="manual_items[${manualItemIndex}][pr_ref]" placeholder="PR/2025/XXX">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">🗑</button>
        </td>
    `;
        tableBody.appendChild(row);
        manualItemIndex++;
    }

    function updateManualItemSelects() {
        selectedItemCategory = document.getElementById('itemCategory').value;
        const selects = document.querySelectorAll('.manual-item-select');
        selects.forEach(select => {
            const prevValue = select.value;
            select.innerHTML = getFilteredItemOptions(selectedItemCategory);
            if ([...select.options].some(opt => opt.value === prevValue)) {
                select.value = prevValue;
            }
        });
    }

    document.getElementById('itemCategory').addEventListener('change', updateManualItemSelects);

    const procurementPlans = @json($procurementPlansOutput);
    const planItemData = @json($planItemData);
    const addedPlanItems = new Set();

    function loadPlanItemsForPlan() {
        const planId = document.getElementById('selectedProcurementPlan').value;
        const select = document.getElementById('planItemSelect');
        select.innerHTML = `<option selected disabled>-- Select Item --</option>`;

        if (procurementPlans[planId]) {
            procurementPlans[planId].forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.itemId;
                opt.textContent = `${item.name} (${item.plannedQty})`;
                select.appendChild(opt);
            });
        }
    }

    function addPlanItemToGrid() {
        const planId = document.getElementById('selectedProcurementPlan').value;
        const select = document.getElementById('planItemSelect');
        const itemId = select.value;
        const tbody = document.querySelector('#planItemsGrid tbody');

        if (!planId || !itemId) {
            alert('Please select both a plan and an item.');
            return;
        }

        const uniqueKey = `${planId}-${itemId}`;

        if (addedPlanItems.has(uniqueKey)) {
            alert('Item already added for this plan.');
            return;
        }

        const itemList = planItemData[planId] || [];
        const item = itemList.find(obj => String(obj.itemId) === String(itemId));

        if (!item) {
            alert('Item not found in plan data.');
            return;
        }

        const row = `
        <tr data-id="${uniqueKey}">
            <td>${item.name}</td>
            <td>${item.plannedQty}</td>
            <td>
                <input type="hidden" name="plan_items[${uniqueKey}][item_id]" value="${itemId}">
                <input type="number" class="form-control" name="plan_items[${uniqueKey}][qty]" value="${item.plannedQty}" min="1" max="${item.plannedQty}" required>
            </td>
            <td>
                <input type="file" class="form-control" name="plan_items[${uniqueKey}][file]">
            </td>
            <td>
                <input type="text" class="form-control" name="plan_items[${uniqueKey}][pr_ref]" placeholder="PR/2025/XXX">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="removePlanItemFromGrid('${uniqueKey}')">🗑</button>
            </td>
        </tr>
    `;

        tbody.insertAdjacentHTML('beforeend', row);
        addedPlanItems.add(uniqueKey);
        select.selectedIndex = 0;
    }

    function removePlanItemFromGrid(uniqueKey) {
        const row = document.querySelector(`#planItemsGrid tr[data-id="${uniqueKey}"]`);
        if (row) row.remove();
        addedPlanItems.delete(uniqueKey);
    }
</script>

<script>
    const suppliers = @json($suppliers);
    const openTender = document.getElementById('openTender');
    const restrictedTender = document.getElementById('restrictedTender');
    const suppliersSection = document.getElementById('restrictedSuppliersSection');
    const suppliersList = document.getElementById('suppliersList');
    const itemCategory = document.getElementById('itemCategory');

    function populateSuppliers(categoryId = null) {
        suppliersList.innerHTML = '';
        const filteredSuppliers = categoryId ?
            suppliers.filter(supplier => {
                // Check if supplier can serve this item category (parent) or any of its subcategories
                // The supplier's ItemCategoryIds should include both parent and subcategory IDs
                return supplier.ItemCategoryIds && supplier.ItemCategoryIds.includes(parseInt(categoryId));
            }) :
            suppliers;

        if (filteredSuppliers.length === 0) {
            const option = document.createElement('option');
            option.disabled = true;
            option.textContent = categoryId ? 'No suppliers available for this category' : 'No suppliers available';
            suppliersList.appendChild(option);
            return;
        }

        filteredSuppliers.forEach(supplier => {
            const option = document.createElement('option');
            option.value = supplier.Id;
            option.textContent = supplier.ThirdPartyName;
            suppliersList.appendChild(option);
        });
    }

    // Remove duplicate event handlers - already handled in first script block

    itemCategory.addEventListener('change', () => {
        if (restrictedTender.checked) {
            suppliersSection.style.display = 'block';
            populateSuppliers(itemCategory.value);
        } else {
            suppliersSection.style.display = 'none';
            suppliersList.innerHTML = '';
        }
    });
</script>

<script>
    const originalItemOptions = [];
    const categoryData = @json($categoryData ?? []);

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.manual-item-select option').forEach(opt => {
            if (opt.value !== "" && opt.value !== "-- Select Item --") {
                originalItemOptions.push(opt.cloneNode(true));
            }
        });
    });

    document.getElementById('itemCategory').addEventListener('change', (e) => {
        const selectedCategoryId = e.target.value;

        const subCategoryIds = categoryData
            .filter(cat => String(cat.parentId) === String(selectedCategoryId))
            .map(cat => String(cat.id));

        document.querySelectorAll('.manual-item-select').forEach(select => {
            const defaultOption = select.querySelector('option:first-child');
            select.innerHTML = '';
            select.appendChild(defaultOption.cloneNode(true));

            originalItemOptions.forEach(opt => {
                const itemCat = opt.dataset.itemCategory;
                if (String(itemCat) === String(selectedCategoryId) || subCategoryIds.includes(String(itemCat))) {
                    select.appendChild(opt.cloneNode(true));
                }
            });
        });
    });
</script>

@endsection