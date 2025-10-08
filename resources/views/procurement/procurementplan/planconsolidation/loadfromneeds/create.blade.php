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
                            <td>{{ \Carbon\Carbon::parse($need->RequestedDate)->format('d/m/Y') }}</td>
                            <td>{{ $need->Justification }}</td>
                            <td>
                                <select name="budget_line_id[{{ $need->Id }}]" class="form-select budget-select" {{ old('selected_needs') && !in_array($need->Id, old('selected_needs', [])) ? 'disabled' : '' }}>
                                    <option disabled {{ old('budget_line_id.'.$need->Id) ? '' : 'selected' }}>Select Budget Line</option>
                                    @foreach($budgetLines as $budgetLine)
                                        <option value="{{ $budgetLine->Id }}" @selected(old('budget_line_id.'.$need->Id) == $budgetLine->Id)>{{ $budgetLine->LineName }}</option>
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
                    attachCheckboxEvents();
                }
            })
            .catch(err => console.error('Error fetching filtered needs:', err));
    }

    // Enable/disable submit button depending on checkbox selection
    function attachCheckboxEvents() {
        const checkboxes = document.querySelectorAll('.need-checkbox');
        const selectAll = document.getElementById('selectAll');
        const submitBtn = document.getElementById('submitBtn');
        const getBudgetSelectFor = (cb) => cb.closest('tr')?.querySelector('.budget-select');

        if (!selectAll || checkboxes.length === 0) return;

        // Remove any existing event listeners by cloning (in case re-renders cause duplicates)
        const newSelectAll = selectAll.cloneNode(true);
        selectAll.parentNode.replaceChild(newSelectAll, selectAll);

        newSelectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => {
                cb.checked = newSelectAll.checked;
                const select = getBudgetSelectFor(cb);
                if (select) {
                    select.disabled = !cb.checked;
                    if (!cb.checked) select.selectedIndex = 0;
                }
            });
            toggleSubmitButton();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                if (!cb.checked) {
                    newSelectAll.checked = false;
                } else if (Array.from(checkboxes).every(c => c.checked)) {
                    newSelectAll.checked = true;
                }
                const select = getBudgetSelectFor(cb);
                if (select) {
                    select.disabled = !cb.checked;
                    if (!cb.checked) select.selectedIndex = 0;
                }
                toggleSubmitButton();
            });
        });

        function toggleSubmitButton() {
            const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            if (submitBtn) {
                submitBtn.disabled = !anyChecked;
            }
        }

        // Initialize selects on load
        checkboxes.forEach(cb => {
            const select = getBudgetSelectFor(cb);
            if (select) {
                select.disabled = !cb.checked;
                if (!cb.checked) select.selectedIndex = 0;
            }
        });
        toggleSubmitButton();
    }


    // Listen to filter changes
    document.querySelectorAll('.filter-input').forEach(select => {
        select.addEventListener('change', fetchFilteredNeeds);
    });

    // Initialize
    updateFiscalYear();
    attachCheckboxEvents();
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        @if(!$approvedNeeds->isEmpty())
        $('#loadfromneedsTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                emptyTable: ""
            }
        });
        @endif
        attachCheckboxEvents();
    });
</script>
@endsection
