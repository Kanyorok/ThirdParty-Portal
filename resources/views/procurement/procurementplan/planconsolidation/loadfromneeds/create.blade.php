@extends('layouts.app')

@section('title', 'Select Approved Needs')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">📥 Select Approved Needs to Include in Draft Plan</h4>

    {{-- ✅ FLASH MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>⚠️ {{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 🔹 Form for Including Needs --}}
    <form method="POST" action="{{ route('plan-from-needs.store') }}">
        @csrf

        {{-- 🔹 Filter Fields --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <label class="form-label">Target Plan</label>
                @php
                    $selectedPlanId = old('plan_id', request('plan_id'));
                    $selectedPlan = $plans->firstWhere('PlanID', $selectedPlanId);
                @endphp

                <select class="form-select" disabled>
                    <option selected>
                        {{ $selectedPlan?->Title ?? 'No Plan Selected' }}
                    </option>
                </select>
                <input type="hidden" name="plan_id" id="plan_id_selector" value="{{ $selectedPlanId }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Planning Period</label>
                <input type="text" name="fiscal_year" id="fiscal_year_input" class="form-control" readonly
                       value="{{ old('fiscal_year', request('fiscal_year', $selectedPlan?->FiscalYear)) }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Branch</label>
                <select class="form-select filter-input" name="branch_filter" id="branch_filter">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option
                            value="{{ $branch->Id }}" {{ request('branch_filter') == $branch->Id ? 'selected' : '' }}>
                            {{ $branch->Name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Department</label>
                <select class="form-select filter-input" name="department_filter" id="department_filter">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option
                            value="{{ $dept->Id }}" {{ request('department_filter') == $dept->Id ? 'selected' : '' }}>
                            {{ $dept->Name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select class="form-select filter-input" name="category_id" id="category_id">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option
                            value="{{ $category->Id }}" {{ request('category_id') == $category->Id ? 'selected' : '' }}>
                            {{ $category->Name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                {{-- No apply button since filtering is automatic --}}
            </div>
        </div>

        {{-- 🔹 Needs Table + Submit Button Container --}}
        <div id="needs-table-container">
            @if($approvedNeeds->count())
                <table id="loadfromneedsTable" class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>Item / Need ID</th>
                        <th>Branch</th>
                        <th>Dept</th>
                        <th>Qty</th>
                        <th>Est. Cost</th>
                        <th>Required By</th>
                        <th>Justification</th>
                        <th>Budget Line</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($approvedNeeds as $need)
                        <tr>
                            <td><input type="checkbox" class="need-checkbox" name="selected_needs[]"
                                       value="{{ $need->Id }}"></td>
                            <td>
                                {{ $need->item->ItemName ?? 'N/A' }}
                                <div class="text-muted small">Need ID: {{ $need->NeedID }}</div>
                            </td>
                            <td>{{ $need->branch->Name ?? 'N/A' }}</td>
                            <td>{{ $need->department->Name ?? 'N/A' }}</td>
                            <td>{{ $need->RequestedQty }}</td>
                            <td>{{ number_format($need->EstimatedUnitCost * $need->RequestedQty, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($need->RequestedDate)->format('d M Y') }}</td>                           <td>{{ $need->Justification }}</td>
                            <td>
                                <select name="budget_line_id[{{ $need->Id }}]"
                                        class="form-select budget-select" {{ old('selected_needs') && !in_array($need->Id, old('selected_needs', [])) ? 'disabled' : '' }}>
                                    <option disabled {{ old('budget_line_id.'.$need->Id) ? '' : 'selected' }}>Select
                                        Budget Line
                                    </option>
                                    @foreach($budgetLines as $budgetLine)
                                        <option
                                            value="{{ $budgetLine->Id }}" @selected(old('budget_line_id.'.$need->Id) == $budgetLine->Id)>{{ $budgetLine->LineName }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-success" id="submitBtn" name="action" value="submit">
                        ➕ Include Selected Items in Draft Plan
                    </button>
                </div>

                <!-- Hidden container for maintaining selected IDs across pages -->
                <div id="selectedNeedsContainer" class="d-none"></div>
            @else
                <div class="text-center text-muted">No approved needs match the selected filters.</div>
            @endif
        </div>
    </form>
</div>

{{-- 🔹 Script Section --}}
<script>
    // Update fiscal year based on selected plan
    const planSelector = document.getElementById('plan_id_selector');
    const fiscalYearInput = document.getElementById('fiscal_year_input');

    function updateFiscalYear() {
        const selectedOption = planSelector.options[planSelector.selectedIndex];
        const fiscalYear = selectedOption ? selectedOption.getAttribute('data-year') : '';
        fiscalYearInput.value = fiscalYear || '';
    }

    // Fetch filtered needs dynamically and update the table container
    function fetchFilteredNeeds() {
        const branch = document.getElementById('branch_filter').value || '';
        const department = document.getElementById('department_filter').value || '';
        const category = document.getElementById('category_id').value || '';

        // Add plan_id param to keep consistency (optional)
        const planId = planSelector.value || '';

        const params = new URLSearchParams({
            branch_filter: branch,
            department_filter: department,
            category_id: category,
            plan_id: planId
        });

        fetch(window.location.pathname + '?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.text())
            .then(html => {
                // Parse returned full page html to extract #needs-table-container
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newContainer = doc.querySelector('#needs-table-container');
                const currentContainer = document.getElementById('needs-table-container');

                if (newContainer && currentContainer) {
                    currentContainer.innerHTML = newContainer.innerHTML;
                    // Reinit DataTable and event handlers after content swap
                    initDataTable();
                    attachCheckboxEvents();
                    applySelectionToVisibleRows();
                }
            })
            .catch(err => console.error('Error fetching filtered needs:', err));
    }

    // Cross-page selection state using a Set
    let selectedNeeds = new Set();

    // Rebuild hidden inputs from selectedNeeds Set
    function rebuildHiddenInputs() {
        const container = document.getElementById('selectedNeedsContainer');
        if (!container) return;
        container.innerHTML = '';
        selectedNeeds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_needs[]';
            input.value = id;
            container.appendChild(input);
        });
    }

    // Update submit button enabled state based on selectedNeeds
    function updateSubmitButtonState() {
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) submitBtn.disabled = selectedNeeds.size === 0;
    }

    // Enable/disable budget select for a row
    function setBudgetSelectStateForRow(rowEl, enabled) {
        const select = rowEl?.querySelector('.budget-select');
        if (select) {
            select.disabled = !enabled;
            if (!enabled) select.selectedIndex = 0;
        }
    }

    // Apply selection state to current page rows (after DataTables draw)
    function applySelectionToVisibleRows() {
        document.querySelectorAll('#loadfromneedsTable tbody tr').forEach(tr => {
            const cb = tr.querySelector('.need-checkbox');
            if (!cb) return;
            const id = cb.value;
            cb.checked = selectedNeeds.has(id);
            setBudgetSelectStateForRow(tr, cb.checked);
        });

        // Update Select All checkbox based on current page
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            const visibleCbs = Array.from(document.querySelectorAll('#loadfromneedsTable tbody .need-checkbox'));
            selectAll.checked = visibleCbs.length > 0 && visibleCbs.every(c => c.checked);
        }

        updateSubmitButtonState();
    }

    // Attach delegated events for checkboxes and select-all, persists across pagination
    function attachCheckboxEvents() {
        const tableEl = document.getElementById('loadfromneedsTable');
        if (!tableEl) {
            updateSubmitButtonState();
            rebuildHiddenInputs();
            return;
        }

        // Delegated checkbox change
        $(document).off('change.needsCheckbox').on('change.needsCheckbox', '#loadfromneedsTable tbody .need-checkbox', function () {
            const cb = this;
            const id = cb.value;
            if (cb.checked) {
                selectedNeeds.add(id);
            } else {
                selectedNeeds.delete(id);
                // Uncheck header select-all when any unchecked
                const selectAll = document.getElementById('selectAll');
                if (selectAll) selectAll.checked = false;
            }
            setBudgetSelectStateForRow(cb.closest('tr'), cb.checked);
            rebuildHiddenInputs();
            updateSubmitButtonState();
        });

        // Header select-all (current page only)
        $(document).off('change.needsSelectAll').on('change.needsSelectAll', '#selectAll', function () {
            const checked = this.checked;
            const $rows = $('#loadfromneedsTable').DataTable ? $('#loadfromneedsTable').DataTable().rows({ page: 'current' }).nodes() : $('#loadfromneedsTable tbody tr');
            $($rows).each(function () {
                const cb = this.querySelector('.need-checkbox');
                if (!cb) return;
                cb.checked = checked;
                const id = cb.value;
                if (checked) {
                    selectedNeeds.add(id);
                } else {
                    selectedNeeds.delete(id);
                }
                setBudgetSelectStateForRow(this, checked);
            });
            rebuildHiddenInputs();
            updateSubmitButtonState();
        });

        // Initial sync for any pre-checked boxes on first render
        document.querySelectorAll('#loadfromneedsTable tbody .need-checkbox:checked').forEach(cb => selectedNeeds.add(cb.value));
        rebuildHiddenInputs();
        applySelectionToVisibleRows();
    }


    // Listen to filter changes
    document.querySelectorAll('.filter-input').forEach(select => {
        select.addEventListener('change', fetchFilteredNeeds);
    });

    // Initialize
    updateFiscalYear();
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    // Initialize DataTable with hooks to reapply selection on page changes
    function initDataTable() {
        const $table = $('#loadfromneedsTable');
        if ($table.length === 0) return;
        // If already initialized, destroy and re-init to avoid duplicates
        if ($.fn.DataTable.isDataTable($table)) {
            $table.DataTable().off('draw');
            $table.DataTable().destroy();
        }
        const dt = $table.DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: { emptyTable: "" }
        });
        dt.on('draw', function () {
            // Reapply selection and budget select states after page change
            applySelectionToVisibleRows();
        });
    }

    $(document).ready(function () {
        @if(!$approvedNeeds->isEmpty())
        initDataTable();
        @endif
        attachCheckboxEvents();
        updateSubmitButtonState();
    });
</script>
@endsection
