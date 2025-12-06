@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'Requisitions')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    .select2-container {
        width: 100% !important;
    }

    /* CRITICAL: Force DataTables controls to always be visible */
    div.dataTables_wrapper {
        width: 100% !important;
        overflow: visible !important;
    }

    div.dataTables_wrapper div.dataTables_length,
    div.dataTables_wrapper div.dataTables_filter {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        position: relative !important;
        width: auto !important;
        height: auto !important;
        margin: 0 0 1rem 0 !important;
    }

    div.dataTables_wrapper div.dataTables_filter {
        text-align: right !important;
        float: none !important;
    }

    div.dataTables_wrapper div.dataTables_filter label {
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.5rem !important;
    }

    div.dataTables_wrapper div.dataTables_filter input {
        display: inline-block !important;
        width: auto !important;
        min-width: 200px !important;
        margin-left: 0.5rem !important;
    }

    div.dataTables_wrapper div.dataTables_length label {
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.5rem !important;
    }

    div.dataTables_wrapper div.dataTables_length select {
        display: inline-block !important;
        width: auto !important;
        margin: 0 0.5rem !important;
    }

    /* Fix for Bootstrap grid system conflicts */
    .dataTables_wrapper .row {
        display: flex !important;
        flex-wrap: wrap !important;
        margin-right: -0.75rem !important;
        margin-left: -0.75rem !important;
    }

    .dataTables_wrapper .col-sm-12,
    .dataTables_wrapper .col-md-6,
    .dataTables_wrapper .col-md-5,
    .dataTables_wrapper .col-md-7 {
        padding-right: 0.75rem !important;
        padding-left: 0.75rem !important;
        position: relative !important;
        width: 100% !important;
    }

    @media (min-width: 768px) {
        .dataTables_wrapper .col-md-6 {
            flex: 0 0 50% !important;
            max-width: 50% !important;
        }
        .dataTables_wrapper .col-md-5 {
            flex: 0 0 41.666667% !important;
            max-width: 41.666667% !important;
        }
        .dataTables_wrapper .col-md-7 {
            flex: 0 0 58.333333% !important;
            max-width: 58.333333% !important;
        }
    }

    /* Responsive adjustments */
    @media (max-width: 767px) {
        div.dataTables_wrapper div.dataTables_filter {
            text-align: left !important;
            margin-top: 0.5rem !important;
        }

        div.dataTables_wrapper div.dataTables_filter input {
            min-width: 150px !important;
            width: 100% !important;
            max-width: 100% !important;
        }
    }

    /* Ensure table stays within card */
    .card-body {
        overflow-x: auto !important;
        overflow-y: visible !important;
    }

    /* Prevent sidebar transitions from affecting DataTables */
    .main-sidebar {
        transition: margin-left 0.3s ease-in-out, left 0.3s ease-in-out !important;
    }

    body:not(.sidebar-collapse) .content-wrapper,
    body:not(.sidebar-collapse) .main-header,
    body:not(.sidebar-collapse) .main-footer {
        transition: margin-left 0.3s ease-in-out !important;
    }
</style>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-md-12 text-end">
        @can('create', \App\Models\Procurement\Requisitions::class)
        <button class="btn btn-primary modal-create-item" type="button">
            <i class="fas fa-plus-circle"></i> New Requisition
        </button>
        @endcan
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="requisitionTable" class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Requisition No</th>
                                <th>Procurement Plan</th>
                                <th>Requisition Date</th>
                                <th>Branch</th>
                                <th>Department</th>
                                <th>Remarks</th>
                                <th>Total Items</th>
                                <th>Total Cost</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($details as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->RequisitionNo }}</td>
                                <td>{{ $item->PlanTitle ?? 'N/A' }}</td>
                                <td data-order="{{ $item->CreatedOn ? Carbon::parse($item->CreatedOn)->format('Y-m-d H:i:s') : '' }}">
                                    {{ $item->CreatedOn ? Carbon::parse($item->CreatedOn)->format('d M Y') : '' }}
                                </td>
                                <td>{{ $item->BranchID }}</td>
                                <td>{{ $item->DepartmentID }}</td>
                                <td>{{ $item->Remarks }}</td>
                                <td>{{ $item->itemcount }}</td>
                                <td data-order="{{ (float)($item->ExpectedPrice ?? 0) }}">{{ number_format((float)($item->ExpectedPrice ?? 0), 2) }}</td>
                                <td>{{ $item->Status }}</td>
                                <td>
                                    <a href="{{ route('requisition.show', [$item->Id]) }}"
                                        class="btn btn-info btn-sm">View</a>
                                    <a href="{{ route('requisition.approval', [$item->Id]) }}"
                                        class="btn btn-success btn-sm">Approve</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center">No requisition items found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="RequisitionItemModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">..</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content with-gradient d-none modal-item" id="createRequisition">
                    @can('create', \App\Models\Procurement\Requisitions::class)
                    <form action="{{ route('requisition.store') }}" method="post" id="createRequisitionForm">
                        @csrf

                        <!-- Procurement Plan -->
                        <div class="mb-3">
                            <label class="form-label" for="ProcurementPlan">Procurement Plan</label>
                            <select class="form-control" name="ProcurementPlan" id="ProcurementPlan">
                                <option selected value="">Select Procurement Plan</option>
                                @foreach ($procurementPlans as $procurementPlan)
                                <option value="{{ $procurementPlan->PlanID }}">
                                    {{ $procurementPlan->ReferenceNumber }}
                                    -{{ $procurementPlan->Title }}
                                </option>
                                @endforeach
                            </select>
                            <p id="ProcurementPlan_error" class="invalid-feedback d-none error col-12"
                                role="alert"></p>
                        </div>

                        <!-- Branch -->
                        <div class="mb-3">
                            <label class="form-label" for="Branch">Branch <span class="text-danger">*</span></label>
                            <select class="form-control" name="Branch" id="Branch" required>
                                @if (isset($branchId))
                                <option value="{{ $branchId }}"
                                    selected>{{ session('LoginBranchName') }}</option>
                                @else
                                <option selected disabled>Select Branch</option>
                                @endif
                            </select>
                            <p id="Branch_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>

                        <!-- Department -->
                        <div class="mb-3">
                            <label class="form-label" for="Department">Department <span class="text-danger">*</span></label>
                            <select class="form-control" name="Department" id="Department" required>

                                @if (isset($departmentId))
                                <option value="{{ $departmentId }}" selected>Department
                                    #{{ $departmentName ?? 'Department #' . $departmentId }}</option>
                                @else
                                <option selected disabled>Select Department</option>
                                @endif
                            </select>
                            <p id="Department_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>

                        <!-- Remarks -->
                        <div class="mb-3">
                            <label class="form-label" for="Remarks">Remarks <span
                                    class="text-danger">*</span></label>
                            <textarea name="Remarks" id="Remarks" rows="3" class="form-control" maxlength="1000"
                                required></textarea>
                            <p id="Remarks_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>

                        <hr>
                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary float-start" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button class="btn btn-primary float-end" id="createRequisitionBtn" type="submit">
                                <i class="fas fa-save"></i> Add Requisition
                            </button>
                        </div>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/datatables.js') }}"></script>

<script>
(function () {
  // ---- DOM refs
  const tenderCatSel    = document.getElementById('tenderCategory');
  const itemCatSel      = document.getElementById('itemCategory');
  const openTender      = document.getElementById('openTender');
  const restricted      = document.getElementById('restrictedTender');
  const suppliersSection= document.getElementById('restrictedSuppliersSection');
  const suppliersList   = document.getElementById('suppliersList');
  const supplierMatchCount = document.getElementById('supplierMatchCount');
  const submissionDeadline = document.getElementById('submissionDeadline');
  const openingDate = document.getElementById('openingDate');
  const DEBUG = Boolean(@json(config('app.debug')));

  // ---- Data injected from Blade
  const suppliers = @json($suppliers ?? []);
  const allItemsWithCategoryIds = @json($allItemsWithCategoryIds);
  const planItemsByPlan = @json($procurementPlansOutput ?? []);

  // ---- Date Validation Setup
  const today = new Date().toISOString().split('T')[0];

  function setupDateValidation() {
    if (!submissionDeadline || !openingDate) return;

    // Set minimum dates to today
    submissionDeadline.setAttribute('min', today);
    openingDate.setAttribute('min', today);

    // Update opening date minimum when submission deadline changes
    submissionDeadline.addEventListener('change', function() {
      const selectedDate = this.value;

      // Validate submission deadline is not in the past
      if (selectedDate < today) {
        this.value = '';
        alert('Submission deadline cannot be in the past. Please select today or a future date.');
        this.classList.add('is-invalid');
        return;
      }

      this.classList.remove('is-invalid');

      // Update opening date minimum to match submission deadline
      if (selectedDate) {
        openingDate.setAttribute('min', selectedDate);

        // If opening date is already set and is before the new submission deadline, clear it
        if (openingDate.value && openingDate.value < selectedDate) {
          openingDate.value = '';
          alert('Opening date must be on or after the submission deadline. Please select a new opening date.');
        }
      }
    });

    // Validate opening date
    openingDate.addEventListener('change', function() {
      const selectedOpeningDate = this.value;
      const selectedSubmissionDate = submissionDeadline.value;

      // Validate opening date is not in the past
      if (selectedOpeningDate < today) {
        this.value = '';
        alert('Opening date cannot be in the past. Please select today or a future date.');
        this.classList.add('is-invalid');
        return;
      }

      // Validate opening date is not before submission deadline
      if (selectedSubmissionDate && selectedOpeningDate < selectedSubmissionDate) {
        this.value = '';
        alert('Opening date must be on or after the submission deadline (' + selectedSubmissionDate + ').');
        this.classList.add('is-invalid');
        return;
      }

      this.classList.remove('is-invalid');
    });

    // Trigger validation on page load if there are old values
    if (submissionDeadline.value) {
      submissionDeadline.dispatchEvent(new Event('change'));
    }
  }

  // ---- Form Submission Validation
  function setupFormValidation() {
    const form = document.querySelector('form[action="{{ route('initiatetender.store') }}"]');
    if (!form) return;

    form.addEventListener('submit', function(event) {
      let isValid = true;
      const submissionValue = submissionDeadline.value;
      const openingValue = openingDate.value;

      // Check submission deadline
      if (!submissionValue) {
        submissionDeadline.classList.add('is-invalid');
        isValid = false;
      } else if (submissionValue < today) {
        alert('Submission deadline cannot be in the past.');
        submissionDeadline.classList.add('is-invalid');
        isValid = false;
      } else {
        submissionDeadline.classList.remove('is-invalid');
      }

      // Check opening date
      if (!openingValue) {
        openingDate.classList.add('is-invalid');
        isValid = false;
      } else if (openingValue < today) {
        alert('Opening date cannot be in the past.');
        openingDate.classList.add('is-invalid');
        isValid = false;
      } else if (submissionValue && openingValue < submissionValue) {
        alert('Opening date must be on or after the submission deadline.');
        openingDate.classList.add('is-invalid');
        isValid = false;
      } else {
        openingDate.classList.remove('is-invalid');
      }

      if (!isValid) {
        event.preventDefault();
        return false;
      }
    });
  }

  // ---- Plan -> Plan Item population
  function loadPlanItemsForPlan() {
    const planSel = document.getElementById('selectedProcurementPlan');
    const itemSel = document.getElementById('planItemSelect');
    if (!planSel || !itemSel) return;

    const planId = planSel.value;
    itemSel.innerHTML = '<option selected disabled>-- Select Item (Tender-method, not already used) --</option>';

    const items = (planItemsByPlan && planItemsByPlan[planId]) ? planItemsByPlan[planId] : [];
    items.forEach(it => {
      const opt = document.createElement('option');
      opt.value = String(it.planLineItemId);

      // Show remaining quantity instead of planned quantity
      const qtyDisplay = it.remainingQty !== undefined ? it.remainingQty : it.plannedQty;
      opt.textContent = `${it.name}${it.needId ? ' - ' + it.needId : ''} (Available: ${qtyDisplay})`;

      opt.dataset.planId = planId;
      opt.dataset.planLineItemId = it.planLineItemId;
      opt.dataset.itemId = it.itemId;
      opt.dataset.plannedQty = it.plannedQty || 0;
      opt.dataset.remainingQty = qtyDisplay;
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
    const remainingQty = Number(opt.dataset.remainingQty || 0);
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
      <td>${remainingQty}</td>
      <td>
        <input type="number" class="form-control form-control-sm" min="1" max="${remainingQty}" step="1"
               name="plan_items[${compositeKey}][qty]" value="${Math.max(1, Math.min(remainingQty, 1))}" required>
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

  // ---- Manual Entry: add rows with Item Master select
  let manualRowSeq = 0;
  function addManualItemRow() {
    const tbody = document.getElementById('manualItemsBody');
    if (!tbody) return;

    manualRowSeq += 1;
    const key = `m${Date.now()}_${manualRowSeq}`;
    const selectedItemCategory = itemCatSel ? itemCatSel.value : '';

    let optionsHtml = '';
    if (selectedItemCategory) {
      optionsHtml = getFilteredItemOptions(selectedItemCategory);
    } else {
      optionsHtml = '<option selected disabled>-- Select Item (choose Item Category first) --</option>';
      (allItemsWithCategoryIds || []).forEach(item => {
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
        <input type="number" class="form-control form-control-sm" min="1" step="1"
               name="manual_items[${key}][qty]" value="1" required>
      </td>
      <td>
        <input type="file" class="form-control form-control-sm"
               name="manual_items[${key}][specs]">
      </td>
      <td>
        <input type="text" class="form-control form-control-sm"
               name="manual_items[${key}][pr_ref]" placeholder="Optional">
      </td>
      <td>
        <button type="button" class="btn btn-sm btn-outline-danger remove-row">Remove</button>
      </td>
    `;

    tbody.appendChild(tr);
    updateManualItemSelects();
  }

  window.addManualItemRow = addManualItemRow;

  const manualTabBtn = document.getElementById('manual-tab');
  if (manualTabBtn) {
    manualTabBtn.addEventListener('shown.bs.tab', () => {
      const body = document.getElementById('manualItemsBody');
      if (body && body.children.length === 0) addManualItemRow();
    });
    manualTabBtn.addEventListener('click', () => {
      const body = document.getElementById('manualItemsBody');
      if (body && body.children.length === 0) addManualItemRow();
    });
  }

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

  function getAllItemOptions() {
    let html = '<option selected disabled>-- Select Item --</option>';
    (allItemsWithCategoryIds || []).forEach(item => {
      html += `<option value="${item.Id}" data-item-category="${item.Category}">${item.ItemName}</option>`;
    });
    return html;
  }

  function updateManualItemSelects() {
    const selectedItemCategory = itemCatSel ? itemCatSel.value : '';
    document.querySelectorAll('.manual-item-select').forEach(select => {
      const prevValue = select.value;
      select.innerHTML = selectedItemCategory ? getFilteredItemOptions(selectedItemCategory) : getAllItemOptions();
      if ([...select.options].some(opt => opt.value === prevValue)) {
        select.value = prevValue;
      }
    });
  }

  async function populateSuppliers(categoryId = null) {
    suppliersList.innerHTML = '';

    if (!categoryId) {
      const opt = document.createElement('option');
      opt.disabled = true;
      opt.textContent = 'Select an Item Category to see eligible suppliers';
      suppliersList.appendChild(opt);
      if (supplierMatchCount) supplierMatchCount.textContent = '';
      return;
    }

    try {
      const url = `{{ url('procurement/purchaseOrder/prequalified-suppliers') }}/${encodeURIComponent(categoryId)}`;
      const res = await fetch(url, { credentials: 'same-origin' });
      if (!res.ok) {
        if (DEBUG) console.warn('prequalified-suppliers HTTP error', res.status, res.statusText);
        const opt = document.createElement('option');
        opt.disabled = true;
        opt.textContent = 'Failed to load suppliers';
        suppliersList.appendChild(opt);
        if (supplierMatchCount) supplierMatchCount.textContent = '';
        return;
      }
      const { success, data } = await res.json();
      const rows = Array.isArray(data) ? data : [];

      if (supplierMatchCount) supplierMatchCount.textContent = `Matching suppliers: ${rows.length}`;
      if (!rows.length) {
        const opt = document.createElement('option');
        opt.disabled = true;
        opt.textContent = 'No prequalified suppliers match this category';
        suppliersList.appendChild(opt);
        return;
      }

      rows
        .map(r => ({
          value: r.SupplierId || r.ThirdPartyId || '',
          label: r.SupplierName || `Supplier #${r.SupplierId || r.ThirdPartyId || ''}`
        }))
        .filter(r => String(r.value).length > 0)
        .sort((a, b) => a.label.toLowerCase().localeCompare(b.label.toLowerCase()))
        .forEach(({ value, label }) => {
          const opt = document.createElement('option');
          opt.value = value;
          opt.textContent = label;
          suppliersList.appendChild(opt);
        });
    } catch (e) {
      if (DEBUG) console.warn('prequalified-suppliers fetch failed', e);
      const opt = document.createElement('option');
      opt.disabled = true;
      opt.textContent = 'Failed to load suppliers';
      suppliersList.appendChild(opt);
      if (supplierMatchCount) supplierMatchCount.textContent = '';
    }
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
  if (tenderCatSel && tenderCatSel.value) {
    refreshItemCategories();
  }
  if (restricted && restricted.checked) {
    suppliersSection.style.display = 'block';
    if (itemCatSel && itemCatSel.value) populateSuppliers(itemCatSel.value);
  }

  // ---- Initialize date validation and form validation
  setupDateValidation();
  setupFormValidation();
})();
</script>
@endsection
