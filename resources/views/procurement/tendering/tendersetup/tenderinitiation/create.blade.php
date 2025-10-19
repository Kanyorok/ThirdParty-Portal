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
    <div class="alert alert-info" role="alert" style="background:#eef6ff;border:1px solid #cfe2ff;color:#084298;">
        <i class="fa fa-info-circle me-2"></i>
        <span title="Open: all suppliers can bid. Restricted: only invited based on selected item category. Use 'Add to Grid' to add items.">
            <strong>Guidance:</strong> Tender Initiation supports two types: Open (all suppliers can bid) and Restricted (only invited suppliers based on the selected item category). Add items to the tender by clicking Add to Grid.
        </span>
    </div>
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
                            <option selected disabled>-- Select Item (Tender-method, not already used) --</option>
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
                                <th>Need ID</th>
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
(function () {
  // ---- DOM refs
  const tenderCatSel    = document.getElementById('tenderCategory');
  const itemCatSel      = document.getElementById('itemCategory');
  const openTender      = document.getElementById('openTender');
  const restricted      = document.getElementById('restrictedTender');
  const suppliersSection= document.getElementById('restrictedSuppliersSection');
  const suppliersList   = document.getElementById('suppliersList');

  // ---- Data injected from Blade
  const suppliers = @json($suppliers);
  const allItemsWithCategoryIds = @json($allItemsWithCategoryIds);
  const planItemsByPlan = @json($procurementPlansOutput ?? []);

  // ---- Plan -> Plan Item population
  function loadPlanItemsForPlan() {
    const planSel = document.getElementById('selectedProcurementPlan');
    const itemSel = document.getElementById('planItemSelect');
    if (!planSel || !itemSel) return;

    const planId = planSel.value;
    itemSel.innerHTML = '<option selected disabled>-- Select Item (Tender-method, not already used) --</option>';

    const items = (planItemsByPlan && planItemsByPlan[planId]) ? planItemsByPlan[planId] : [];
    items.forEach(it => {
      // expected shape: { id: planId, planLineItemId, itemId, name, plannedQty, needId }
      const opt = document.createElement('option');
      opt.value = String(it.planLineItemId);
      opt.textContent = `${it.name}${it.needId ? ' - ' + it.needId : ''} (Planned: ${it.plannedQty})`;
      opt.dataset.planId = planId;
      opt.dataset.planLineItemId = it.planLineItemId;
      opt.dataset.itemId = it.itemId;
      opt.dataset.plannedQty = it.plannedQty;
      opt.dataset.needId = it.needId || '';
      itemSel.appendChild(opt);
    });
  }

  function addPlanItemToGrid() {
    const planSel = document.getElementById('selectedProcurementPlan');
    const itemSel = document.getElementById('planItemSelect');
    const tbody   = document.querySelector('#planItemsGrid tbody[name="plan_items"]');

    if (!planSel || !itemSel || !tbody) return;

    const opt = itemSel.options[itemSel.selectedIndex];
    if (!opt || !opt.dataset.planLineItemId) {
      alert('Please select a plan item first.');
      return;
    }

    const planId = opt.dataset.planId;
    const pli    = opt.dataset.planLineItemId;
    const itemId = opt.dataset.itemId;
    const needId = opt.dataset.needId || '—';
    const plannedQty = Number(opt.dataset.plannedQty || 0);
    const label = opt.textContent || 'Item';

    // Avoid duplicates
    const compositeKey = `plan-${planId}-${pli}`;
    if (tbody.querySelector(`tr[data-key="${compositeKey}"]`)) {
      alert('This plan item is already added.');
      return;
    }

    const row = document.createElement('tr');
    row.dataset.key = compositeKey;
    row.innerHTML = `
      <td>${label}</td>
      <td>${needId}</td>
      <td>${plannedQty}</td>
      <td>
        <input type="number" class="form-control form-control-sm" min="1" step="1"
               name="plan_items[${compositeKey}][qty]" value="${Math.max(1, plannedQty)}" required>
      </td>
      <td>
        <input type="file" class="form-control form-control-sm" name="plan_items[${compositeKey}][specs]">
      </td>
      <td>
        <input type="text" class="form-control form-control-sm" name="plan_items[${compositeKey}][pr_ref]" placeholder="Optional">
      </td>
      <td>
        <button type="button" class="btn btn-sm btn-outline-danger remove-row">Remove</button>
      </td>
      <input type="hidden" name="plan_items[${compositeKey}][item_id]" value="${itemId}">
      <input type="hidden" name="plan_items[${compositeKey}][plan_line_item_id]" value="${pli}">
      <input type="hidden" name="plan_items[${compositeKey}][plan_id]" value="${planId}">
    `;

    tbody.appendChild(row);
  }

  // Remove row handler for plan grid
  document.addEventListener('click', function (e) {
    if (e.target && e.target.classList.contains('remove-row')) {
      const tr = e.target.closest('tr');
      if (tr) tr.remove();
    }
  });

  // Expose functions to be callable from inline handlers
  window.loadPlanItemsForPlan = loadPlanItemsForPlan;
  window.addPlanItemToGrid = addPlanItemToGrid;

  // ---- Helpers
  async function refreshItemCategories() {
    const catId = tenderCatSel && tenderCatSel.value ? tenderCatSel.value : '';
    if (!catId || !itemCatSel) return;

    const url = `{{ route('initiatetender.allowedCategories') }}?tender_category_id=${encodeURIComponent(catId)}`;

    try {
      const res = await fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
      });
      if (!res.ok) {
        console.warn('allowedCategories HTTP error', res.status, res.statusText);
        return;
      }
      const data = await res.json();
      const previous = itemCatSel.value;

      itemCatSel.innerHTML = '<option value="" disabled selected>-- Item Categories --</option>';
      if (data && data.ok && Array.isArray(data.categories)) {
        data.categories.forEach(c => {
          const opt = document.createElement('option');
          opt.value = c.Id; opt.textContent = c.Name;
          if (String(c.Id) === String(previous)) opt.selected = true;
          itemCatSel.appendChild(opt);
        });
      }

      // When item categories change, refresh manual-items options and suppliers (if restricted)
      updateManualItemSelects();
      if (restricted.checked) populateSuppliers(itemCatSel.value);

    } catch (e) {
      console.warn('allowedCategories fetch failed', e);
    }
  }

  function getFilteredItemOptions(categoryId) {
    let html = '<option selected disabled>-- Select Item --</option>';
    (allItemsWithCategoryIds || []).forEach(item => {
      if (String(item.Category) === String(categoryId)) {
        html += `<option value="${item.Id}" data-item-category="${item.Category}">${item.ItemName}</option>`;
      }
    });
    return html;
  }

  function updateManualItemSelects() {
    const selectedItemCategory = itemCatSel.value;
    document.querySelectorAll('.manual-item-select').forEach(select => {
      const prevValue = select.value;
      select.innerHTML = getFilteredItemOptions(selectedItemCategory);
      // keep previous if still valid
      if ([...select.options].some(opt => opt.value === prevValue)) {
        select.value = prevValue;
      }
    });
  }

  function populateSuppliers(categoryId = null) {
    suppliersList.innerHTML = '';
    let filtered = suppliers || [];
    if (categoryId) {
      const catNum = Number(categoryId);
      filtered = filtered.filter(s =>
        Array.isArray(s.ItemCategoryIds) && s.ItemCategoryIds.map(Number).includes(catNum)
      );
    }
    if (!filtered.length) {
      const opt = document.createElement('option');
      opt.disabled = true;
      opt.textContent = categoryId ? 'No suppliers available for this category' : 'No suppliers available';
      suppliersList.appendChild(opt);
      return;
    }
    filtered.forEach(s => {
      const opt = document.createElement('option');
      opt.value = s.Id;
      opt.textContent = s.ThirdPartyName || s.SupplierName || `Supplier #${s.Id}`;
      suppliersList.appendChild(opt);
    });
  }

  // ---- Events
  if (tenderCatSel) {
    tenderCatSel.addEventListener('change', refreshItemCategories);
  }
  if (itemCatSel) {
    itemCatSel.addEventListener('change', () => {
      updateManualItemSelects();
      if (restricted && restricted.checked) {
        suppliersSection.style.display = 'block';
        populateSuppliers(itemCatSel.value);
      }
    });
  }
  if (openTender) {
    openTender.addEventListener('change', () => {
      suppliersSection.style.display = 'none';
      suppliersList.innerHTML = '';
    });
  }
  if (restricted) {
    restricted.addEventListener('change', () => {
      suppliersSection.style.display = 'block';
      if (itemCatSel.value) populateSuppliers(itemCatSel.value);
    });
  }

  // ---- Initial load
  // Populate categories if a tender category is preselected
  if (tenderCatSel && tenderCatSel.value) {
    refreshItemCategories(); // call immediately (don’t rely on DOMContentLoaded timing)
  }
  // Show supplier section if Restricted was preselected
  if (restricted && restricted.checked) {
    suppliersSection.style.display = 'block';
    if (itemCatSel && itemCatSel.value) populateSuppliers(itemCatSel.value);
  }
})();
</script>

@endsection
