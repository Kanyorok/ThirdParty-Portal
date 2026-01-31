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
  @canWrite('tender')
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
            {{-- @if (!$item->isUsed()) --}}
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
        <label for="submissionDeadlineInput" class="form-label fw-bold">Submission Deadline: <span class="text-danger">*</span></label>
        <input type="date" name="submission_deadline" id="submissionDeadlineInput" value="{{ old('submission_deadline') }}"
          min="{{ now()->toDateString() }}"
          class="form-control @error('submission_deadline') is-invalid @enderror" required>
        @error('submission_deadline')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>
      <div class="col-md-6 mb-3">
        <label for="openingDateInput" class="form-label fw-bold">Opening Date: <span class="text-danger">*</span></label>
        <input type="date" name="opening_date" id="openingDateInput" value="{{ old('opening_date') }}"
          min="{{ now()->toDateString() }}"
          class="form-control @error('opening_date') is-invalid @enderror" required>
        @error('opening_date')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <!-- Upload -->
    <div class="mb-3">
      <label for="tenderDocuments" class="form-label fw-bold">Attach Tender Document:</label>
      <input class="form-control" type="file" id="tenderDocuments" name="documents[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx" multiple required>
      <small class="text-muted d-block mb-1">Allowed file types: .pdf, .jpg, .jpeg, .png, .docx, .xlsx | Max size: 25MB</small>
    </div>
    <!-- Restricted Suppliers -->
    <div class="mb-3" id="restrictedSuppliersSection" style="display: none;">
      <label for="suppliersList" class="form-label fw-bold">Add Suppliers to Invite:</label>
      <select class="form-select" id="suppliersList" name="suppliers[]" multiple>
        <!-- Filled dynamically -->
      </select>
      <small id="supplierMatchCount" class="text-muted d-block mt-1"></small>
    </div>

    <!-- Buttons -->
    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary">Save Tender</button>
    </div>
  </form>
  @endcanWrite

</div>
<script>
  (function() {
    'use strict';

    // ============================================================================
    // DOM REFERENCES
    // ============================================================================
    const tenderCatSel = document.getElementById('tenderCategory');
    const itemCatSel = document.getElementById('itemCategory');
    const openTender = document.getElementById('openTender');
    const restricted = document.getElementById('restrictedTender');
    const suppliersSection = document.getElementById('restrictedSuppliersSection');
    const suppliersList = document.getElementById('suppliersList');
    const supplierMatchCount = document.getElementById('supplierMatchCount');
    const DEBUG = Boolean(@json(config('app.debug', false)));

    // ============================================================================
    // DATA FROM BACKEND
    // ============================================================================
    const suppliers = @json($suppliers ?? []);
    const allItemsWithCategoryIds = @json($allItemsWithCategoryIds ?? []);
    const planItemsByPlan = @json($procurementPlansOutput ?? []);
    @php
    try {
        $availablePlansData = ($procurementPlan ?? collect())->map(function($p) {
          return [
            'PlanID' => $p->PlanID ?? null,
            'Title' => $p->Title ?? '',
            'ReferenceNumber' => $p->ReferenceNumber ?? ''
          ];
        })->values();
    } catch (\Exception $e) {
        $availablePlansData = collect([]);
    }
    @endphp

    const availablePlans = @json($availablePlansData);

    // : Log data on page load
    if (DEBUG) {
      console.log('=== TENDER FORM DEBUG ===');
      console.log('Available Plans:', availablePlans);
      console.log('Plan Items by Plan:', planItemsByPlan);
      console.log('All Items with Categories:', allItemsWithCategoryIds.length);
      console.log('Suppliers:', suppliers.length);
    }

    // ============================================================================
    // PLAN ITEMS: Load items for selected plan
    // ============================================================================
    function loadPlanItemsForPlan() {
      const planSel = document.getElementById('selectedProcurementPlan');
      const itemSel = document.getElementById('planItemSelect');
      const itemCatSel = document.getElementById('itemCategory');

      if (!planSel || !itemSel) {
        console.error('Plan or item select not found');
        return;
      }

      const planId = planSel.value;
      itemSel.innerHTML = '<option selected disabled>-- Select Item --</option>';

      if (!planId) {
        if (DEBUG) console.log('No plan selected');
        return;
      }

      //  Ensure planId is treated as string for object key lookup
      const items = planItemsByPlan[String(planId)] || [];
      const selectedCategory = itemCatSel ? itemCatSel.value : '';

      if (DEBUG) {
        console.log('Loading items for plan:', planId);
        console.log('Selected Category:', selectedCategory);
        console.log('Found items (total):', items.length);
      }

      let visibleCount = 0;

      items.forEach(it => {
        // Filter by Item Category
        if (selectedCategory && String(it.categoryId) !== String(selectedCategory)) {
            return;
        }

        visibleCount++;
        const opt = document.createElement('option');
        opt.value = String(it.planLineItemId);

        //  Show remaining quantity instead of planned quantity
        const qtyDisplay = it.remainingQty !== undefined ? it.remainingQty : it.plannedQty;
        opt.textContent = `${it.name}${it.needId ? ' - ' + it.needId : ''} (Available: ${qtyDisplay})`;

        opt.dataset.planId = planId;
        opt.dataset.planLineItemId = it.planLineItemId;
        opt.dataset.itemId = it.itemId;
        opt.dataset.plannedQty = it.plannedQty || 0;
        opt.dataset.remainingQty = it.remainingQty || it.plannedQty || 0;
        opt.dataset.needId = it.needId || '';

        itemSel.appendChild(opt);
      });
      
      if (visibleCount === 0) {
        const opt = document.createElement('option');
        opt.disabled = true;
        opt.textContent = selectedCategory ? 'No items match the selected category' : 'No available items in this plan';
        itemSel.appendChild(opt);
      }

      if (DEBUG) console.log('Populated item select with', visibleCount, 'items (filtered)');
    }

    // ============================================================================
    // PLAN ITEMS: Add selected item to grid
    // ============================================================================
    function addPlanItemToGrid() {
      const planSel = document.getElementById('selectedProcurementPlan');
      const itemSel = document.getElementById('planItemSelect');
      const tbody = document.querySelector('#planItemsGrid tbody[name="plan_items"]');

      if (!planSel || !itemSel || !tbody) {
        console.error('Required elements not found');
        return;
      }

      const opt = itemSel.options[itemSel.selectedIndex];
      if (!opt || !opt.dataset.planLineItemId) {
        alert('Please select a plan item first.');
        return;
      }

      const planId = opt.dataset.planId;
      const pli = opt.dataset.planLineItemId;
      const itemId = opt.dataset.itemId;
      const needId = opt.dataset.needId || '—';
      const plannedQty = Number(opt.dataset.plannedQty || 0);
      const remainingQty = Number(opt.dataset.remainingQty || plannedQty);
      const label = opt.textContent || 'Item';

      // Check for duplicates
      const compositeKey = `plan-${planId}-${pli}`;
      if (tbody.querySelector(`tr[data-key="${compositeKey}"]`)) {
        alert('This plan item is already added.');
        return;
      }

      //  Default qty to remainingQty, not plannedQty
      const defaultQty = Math.max(1, Math.floor(remainingQty));

      const row = document.createElement('tr');
      row.dataset.key = compositeKey;
      row.innerHTML = `
      <td>${label}</td>
      <td>${needId}</td>
      <td>${plannedQty}</td>
      <td>
        <input type="number" class="form-control form-control-sm" 
               min="1" max="${remainingQty}" step="1"
               name="plan_items[${compositeKey}][qty]" 
               value="${defaultQty}" required>
        <small class="text-muted">Max: ${remainingQty}</small>
      </td>
      <td>
        <input type="file" class="form-control form-control-sm" 
               name="plan_items[${compositeKey}][specs]">
      </td>
      <td>
        <input type="text" class="form-control form-control-sm" 
               name="plan_items[${compositeKey}][pr_ref]" 
               placeholder="Optional">
      </td>
      <td>
        <button type="button" class="btn btn-sm btn-outline-danger remove-row">Remove</button>
      </td>
      <input type="hidden" name="plan_items[${compositeKey}][item_id]" value="${itemId}">
      <input type="hidden" name="plan_items[${compositeKey}][plan_line_item_id]" value="${pli}">
      <input type="hidden" name="plan_items[${compositeKey}][plan_id]" value="${planId}">
    `;

      tbody.appendChild(row);

      if (DEBUG) console.log('Added plan item to grid:', compositeKey);

      // Update tab status after adding
      updateTabStatus();

      // Reset select
      itemSel.selectedIndex = 0;
    }

    // ============================================================================
    // MANUAL ITEMS: Add new row
    // ============================================================================
    let manualRowSeq = 0;

    function addManualItemRow() {
      const tbody = document.getElementById('manualItemsBody');
      if (!tbody) {
        console.error('Manual items tbody not found');
        return;
      }

      manualRowSeq += 1;
      const key = `m${Date.now()}_${manualRowSeq}`;
      const selectedItemCategory = itemCatSel ? itemCatSel.value : '';

      //  Build options based on selected category
      let optionsHtml = '<option selected disabled>-- Select Item --</option>';

      if (selectedItemCategory) {
        // Filter items by selected category
        allItemsWithCategoryIds
          .filter(item => String(item.Category) === String(selectedItemCategory))
          .forEach(item => {
            optionsHtml += `<option value="${item.Id}" data-item-category="${item.Category}">${item.ItemName}</option>`;
          });
      } else {
        // Show all items if no category selected
        allItemsWithCategoryIds.forEach(item => {
          optionsHtml += `<option value="${item.Id}" data-item-category="${item.Category}">${item.ItemName}</option>`;
        });
      }

      const tr = document.createElement('tr');
      tr.dataset.key = key;
      tr.innerHTML = `
      <td>
        <select class="form-select form-select-sm manual-item-select"
                name="manual_items[${key}][item_id]" required>
          ${optionsHtml}
        </select>
      </td>
      <td>
        <input type="number" class="form-control form-control-sm" 
               min="1" step="1"
               name="manual_items[${key}][qty]" 
               value="1" required>
      </td>
      <td>
        <input type="file" class="form-control form-control-sm"
               name="manual_items[${key}][specs]">
      </td>
      <td>
        <input type="text" class="form-control form-control-sm"
               name="manual_items[${key}][pr_ref]" 
               placeholder="Optional">
      </td>
      <td>
        <button type="button" class="btn btn-sm btn-outline-danger remove-row">Remove</button>
      </td>
    `;

      tbody.appendChild(tr);

      if (DEBUG) console.log('Added manual item row:', key);

      // Update tab status
      updateTabStatus();
    }

    // ============================================================================
    // TAB LOCKING: Prevent mixing plan and manual items
    // ============================================================================
    function updateTabStatus() {
      const planBody = document.querySelector('#planItemsGrid tbody[name="plan_items"]');
      const manualBody = document.getElementById('manualItemsBody');
      const manualTabBtn = document.getElementById('manual-tab');
      const planTabBtn = document.getElementById('fromPlan-tab');

      const planCount = planBody ? planBody.children.length : 0;
      const manualCount = manualBody ? manualBody.children.length : 0;

      // Reset
      if (manualTabBtn) {
        manualTabBtn.classList.remove('disabled');
        manualTabBtn.removeAttribute('disabled');
        manualTabBtn.removeAttribute('title');
      }

      if (planTabBtn) {
        planTabBtn.classList.remove('disabled');
        planTabBtn.removeAttribute('disabled');
        planTabBtn.removeAttribute('title');
      }

      // Apply locking logic
      if (planCount > 0 && manualTabBtn) {
        manualTabBtn.classList.add('disabled');
        manualTabBtn.setAttribute('disabled', 'disabled');
        manualTabBtn.title = "Cannot add manual items while plan items exist";
      } else if (manualCount > 0 && planTabBtn) {
        planTabBtn.classList.add('disabled');
        planTabBtn.setAttribute('disabled', 'disabled');
        planTabBtn.title = "Cannot add plan items while manual items exist";
      }
    }

    // ============================================================================
    // ROW REMOVAL: Remove from grid
    // ============================================================================
    document.addEventListener('click', function(e) {
      if (e.target && e.target.classList.contains('remove-row')) {
        const tr = e.target.closest('tr');
        if (tr) {
          tr.remove();
          updateTabStatus();
        }
      }
    });

    // ============================================================================
    // ITEM CATEGORY: Update manual item selects when category changes
    // ============================================================================
    function updateManualItemSelects() {
      const selectedItemCategory = itemCatSel ? itemCatSel.value : '';

      document.querySelectorAll('.manual-item-select').forEach(select => {
        const prevValue = select.value;

        let optionsHtml = '<option selected disabled>-- Select Item --</option>';

        if (selectedItemCategory) {
          allItemsWithCategoryIds
            .filter(item => String(item.Category) === String(selectedItemCategory))
            .forEach(item => {
              optionsHtml += `<option value="${item.Id}" data-item-category="${item.Category}">${item.ItemName}</option>`;
            });
        } else {
          allItemsWithCategoryIds.forEach(item => {
            optionsHtml += `<option value="${item.Id}" data-item-category="${item.Category}">${item.ItemName}</option>`;
          });
        }

        select.innerHTML = optionsHtml;

        // Restore previous value if still available
        if ([...select.options].some(opt => opt.value === prevValue)) {
          select.value = prevValue;
        }
      });
    }

    // ============================================================================
    // TENDER CATEGORY: Refresh item categories via AJAX
    // ============================================================================
    async function refreshItemCategories() {
      const catId = tenderCatSel && tenderCatSel.value ? tenderCatSel.value : '';
      if (!catId || isNaN(catId) || !itemCatSel) return;

      const url = `{{ route('initiatetender.allowedCategories') }}?tender_category_id=${encodeURIComponent(catId)}`;

      try {
        const res = await fetch(url, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          credentials: 'same-origin'
        });

        if (!res.ok) {
          console.warn('allowedCategories HTTP error', res.status);
          return;
        }

        const data = await res.json();
        const previous = itemCatSel.value;

        itemCatSel.innerHTML = '<option value="" disabled selected>-- Item Categories --</option>';

        if (data && data.ok && Array.isArray(data.categories)) {
          data.categories.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.Id;
            opt.textContent = c.Name;
            if (String(c.Id) === String(previous)) opt.selected = true;
            itemCatSel.appendChild(opt);
          });
        }

        // Update manual items and suppliers
        updateManualItemSelects();
        if (restricted && restricted.checked) {
          populateSuppliers(itemCatSel.value);
        }

      } catch (e) {
        console.warn('allowedCategories fetch failed', e);
      }
    }

    // ============================================================================
    // SUPPLIERS: Populate supplier list for restricted tenders
    // ============================================================================
    async function populateSuppliers(categoryId = null) {
      if (!suppliersList) {
        console.error('suppliersList element not found');
        return;
      }

      console.log('populateSuppliers called with categoryId:', categoryId);
      suppliersList.innerHTML = '';

      // Validate categoryId is present AND is a number
      if (!categoryId || isNaN(categoryId)) {
        const opt = document.createElement('option');
        opt.disabled = true;
        opt.textContent = 'Select an Item Category to see eligible suppliers';
        suppliersList.appendChild(opt);
        if (supplierMatchCount) supplierMatchCount.textContent = '';
        console.warn('Invalid categoryId, skipping supplier fetch');
        return;
      }

      try {
        const url = `{{ url('procurement/initiatetender/prequalified-suppliers') }}/${encodeURIComponent(categoryId)}`;
        console.log('Fetching suppliers from:', url);
        
        const res = await fetch(url, {
          credentials: 'same-origin'
        });

        if (!res.ok) {
          throw new Error(`HTTP ${res.status}`);
        }

        const {
          success,
          data
        } = await res.json();
        
        console.log('API Response:', { success, data });
        
        const rows = Array.isArray(data) ? data : [];
        console.log('Rows to process:', rows.length);

        if (supplierMatchCount) {
          supplierMatchCount.textContent = `Matching suppliers: ${rows.length}`;
        }

        if (!rows.length) {
          const opt = document.createElement('option');
          opt.disabled = true;
          opt.textContent = 'No prequalified suppliers for this category';
          suppliersList.appendChild(opt);
          console.warn('No suppliers found for category:', categoryId);
          return;
        }

        const mappedRows = rows
          .map(r => {
            const mapped = {
              value: r.SupplierId || r.ThirdPartyId || r.ThirdPartyID || r.Id || '',
              label: r.SupplierName || r.ThirdPartyName || `Supplier #${r.SupplierId || r.ThirdPartyId || r.ThirdPartyID || r.Id || ''}`
            };
            console.log('Mapped row:', r, '->', mapped);
            return mapped;
          })
          .filter(r => {
            const hasValue = String(r.value).length > 0;
            if (!hasValue) console.warn('Filtered out row with no value:', r);
            return hasValue;
          })
          .sort((a, b) => a.label.toLowerCase().localeCompare(b.label.toLowerCase()));
          
        console.log('Final mapped rows:', mappedRows);

        mappedRows.forEach(({
            value,
            label
          }) => {
            const opt = document.createElement('option');
            opt.value = value;
            opt.textContent = label;
            suppliersList.appendChild(opt);
            console.log('Added option:', value, label);
          });
          
        console.log('✓ Suppliers populated successfully');

      } catch (e) {
        console.error('Failed to load suppliers', e);
        const opt = document.createElement('option');
        opt.disabled = true;
        opt.textContent = 'Failed to load suppliers';
        suppliersList.appendChild(opt);
        if (supplierMatchCount) supplierMatchCount.textContent = '';
      }
    }

    // ============================================================================
    // EVENT LISTENERS
    // ============================================================================

    // Tender category change
    if (tenderCatSel) {
      tenderCatSel.addEventListener('change', refreshItemCategories);
    }

    // Item category change
    if (itemCatSel) {
      itemCatSel.addEventListener('change', () => {
        updateManualItemSelects();

        if (restricted && restricted.checked) {
          suppliersSection.style.display = 'block';
          populateSuppliers(itemCatSel.value);
        }

        // Enable plan select after category selection AND filter available plans
        const planSel = document.getElementById('selectedProcurementPlan');
        if (planSel) {
            const selectedCategory = itemCatSel.value;
            const hasCategory = Boolean(selectedCategory);
            planSel.disabled = !hasCategory;
            planSel.innerHTML = '<option selected disabled>-- Choose Procurement Plan --</option>';

            if (hasCategory) {
               let planCount = 0;
               availablePlans.forEach(p => {
                    // Check if this plan has ANY items matching the category
                    const planItems = planItemsByPlan[String(p.PlanID)] || [];
                    const hasMatchingItems = planItems.some(it => String(it.categoryId) === String(selectedCategory));

                    if (hasMatchingItems) {
                        const opt = document.createElement('option');
                        opt.value = p.PlanID;
                        opt.textContent = `${p.Title} - ${p.ReferenceNumber}`;
                        planSel.appendChild(opt);
                        planCount++;
                    }
               });
               
               if (planCount === 0) {
                   const opt = document.createElement('option');
                   opt.disabled = true;
                   opt.textContent = '-- No Plans found for this Category --';
                   planSel.appendChild(opt);
               }
            }
            // Clear items dropdown since plan might have changed or been cleared
            const planItemSel = document.getElementById('planItemSelect');
            if (planItemSel) {
                planItemSel.innerHTML = '<option selected disabled>-- Select Item (Tender-method, not already used) --</option>';
            }
        }
      });
    }

    // Tender type: Open
    if (openTender) {
      openTender.addEventListener('change', () => {
        if (suppliersSection) suppliersSection.style.display = 'none';
        if (suppliersList) suppliersList.innerHTML = '';
      });
    }

    // Tender type: Restricted
    if (restricted) {
      restricted.addEventListener('change', () => {
        if (suppliersSection) suppliersSection.style.display = 'block';
        if (itemCatSel && itemCatSel.value) {
          populateSuppliers(itemCatSel.value);
        }
      });
    }

    // Manual tab: Auto-add first row when opened
    const manualTabBtn = document.getElementById('manual-tab');
    if (manualTabBtn) {
      manualTabBtn.addEventListener('shown.bs.tab', () => {
        const body = document.getElementById('manualItemsBody');
        if (body && body.children.length === 0) {
          addManualItemRow();
        }
      });
    }

    // ============================================================================
    // EXPOSE FUNCTIONS TO GLOBAL SCOPE (for onclick handlers)
    // ============================================================================
    window.loadPlanItemsForPlan = loadPlanItemsForPlan;
    window.addPlanItemToGrid = addPlanItemToGrid;
    window.addManualItemRow = addManualItemRow;

    // ============================================================================
    // INITIALIZATION
    // ============================================================================

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
      // 1. Initialize Flatpickr for Submission Deadline
      flatpickr("#submissionDeadlineInput", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y",
        allowInput: false, // Set to false so they cannot type past dates manually
        minDate: "today" // This prevents clicking past dates
      });

      // 2. Initialize Flatpickr for Opening Date
      flatpickr("#openingDateInput", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y",
        allowInput: false, // Set to false so they cannot type past dates manually
        minDate: "today"
      });

      // Refresh categories if tender category is preselected
      if (tenderCatSel && tenderCatSel.value) {
        refreshItemCategories();
      }

      // Disable plan select until item category is chosen
      const planSel = document.getElementById('selectedProcurementPlan');
      if (planSel) {
        const hasCategory = itemCatSel && itemCatSel.value;
        planSel.disabled = !hasCategory;
        if (!hasCategory) {
          planSel.innerHTML = '<option selected disabled>-- Choose Procurement Plan (select Item Category first) --</option>';
        }
      }

      // Show supplier section if Restricted is preselected
      if (restricted && restricted.checked) {
        if (suppliersSection) suppliersSection.style.display = 'block';
        if (itemCatSel && itemCatSel.value) {
          populateSuppliers(itemCatSel.value);
        }
      }

      // Initialize tab status
      updateTabStatus();

      if (DEBUG) console.log('Tender form initialized');
    });

  })();
</script>
@endsection