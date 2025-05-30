@extends('layouts.app')

@section('title', 'Select Approved Needs')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">📥 Select Approved Needs to Include in Draft Plan</h4>

    <!-- 🔹 FILTER FORM (GET) -->
    <form method="GET" action="{{ route('plan-from-needs.create') }}">
        <div class="row mb-4">
            <div class="col-md-6">
                <label class="form-label">Target Plan</label>
                <select class="form-select" name="plan_id" id="plan_id_selector" required onchange="updateFiscalYear()">
                    <option disabled selected>Select Draft Plan</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->PlanID }}" data-year="{{ $plan->FiscalYear }}"
                            {{ request('plan_id') == $plan->PlanID ? 'selected' : '' }}>
                            {{ $plan->Title }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Planning Period</label>
                <input type="text" name="fiscal_year" id="fiscal_year_input" class="form-control" readonly
                       value="{{ request('fiscal_year') }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Branch</label>
                <select class="form-select" name="branch_filter">
                    <option value="">All</option>
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
                <select class="form-select" name="department_filter">
                    <option value="">All</option>
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
                <select class="form-select" name="category_id">
                    <option value="">All</option>
                    @foreach($categories as $category)
                        <option
                            value="{{ $category->Id }}" {{ request('category_id') == $category->Id ? 'selected' : '' }}>
                            {{ $category->Name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-outline-primary w-100">Apply Filters</button>
            </div>
        </div>
    </form>

    <!-- 🔹 SUBMISSION FORM (POST) -->
    <form method="POST" action="{{ route('plan-from-needs.store') }}">
        @csrf

        <!-- Preserve filters -->
        <input type="hidden" name="plan_id" value="{{ request('plan_id') }}">
        <input type="hidden" name="fiscal_year" value="{{ request('fiscal_year') }}">
        <input type="hidden" name="branch_filter" value="{{ request('branch_filter') }}">
        <input type="hidden" name="department_filter" value="{{ request('department_filter') }}">
        <input type="hidden" name="category_id" value="{{ request('category_id') }}">

        <table class="table table-bordered table-hover mt-3">
            <thead class="table-light">
            <tr>
                <th><input type="checkbox" id="selectAll"></th>
                <th>Item</th>
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
            @forelse($approvedNeeds as $need)
                <tr>
                    <td><input type="checkbox" class="need-checkbox" name="selected_needs[]" value="{{ $need->Id }}">
                    </td>
                    <td>{{ $need->item->ItemName ?? 'N/A' }}</td>
                    <td>{{ $need->branch->Name ?? 'N/A' }}</td>
                    <td>{{ $need->department->Name ?? 'N/A' }}</td>
                    <td>{{ $need->RequestedQty }}</td>
                    <td>{{ number_format($need->EstimatedUnitCost * $need->RequestedQty, 2) }}</td>
                    <td>{{ \Carbon\Carbon::parse($need->RequestedDate)->toDateString() }}</td>
                    <td>{{ $need->Justification }}</td>
                    <td>
                        <select name="budget_line_id[{{ $need->Id }}]" class="form-select" required>
                            <option selected disabled>Select Budget Line</option>
                            @foreach($budgetLines as $budgetLine)
                                <option value="{{ $budgetLine->BudgetLineID }}">
                                    {{ $budgetLine->Description }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted">No approved needs match the selected filters.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-success" id="submitBtn" disabled>
                ➕ Include Selected Items in Draft Plan
            </button>
        </div>
    </form>
</div>

<!-- 🔹 Script -->
<script>
    const planSelector = document.getElementById('plan_id_selector');
    const fiscalYearInput = document.getElementById('fiscal_year_input');
    const selectAllCheckbox = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.need-checkbox');
    const submitBtn = document.getElementById('submitBtn');

    function updateFiscalYear() {
        const selectedOption = planSelector.options[planSelector.selectedIndex];
        const fiscalYear = selectedOption.getAttribute('data-year');
        fiscalYearInput.value = fiscalYear;
    }

    selectAllCheckbox?.addEventListener('click', function () {
        checkboxes.forEach(cb => cb.checked = this.checked);
        toggleSubmitButton();
    });

    function toggleSubmitButton() {
        const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
        submitBtn.disabled = !anyChecked;
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', toggleSubmitButton);
    });

    toggleSubmitButton(); // Initial call on load
</script>
@endsection
