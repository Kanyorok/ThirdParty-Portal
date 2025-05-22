@extends('layouts.app')

@section('title', 'Select Approved Needs')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">📥 Select Approved Needs to Include in Draft Plan</h4>

    <!-- Plan Selection -->
    <div class="row mb-4">
        <div class="col-md-6">
            <label class="form-label">Target Plan</label>
            <select class="form-select" id="plan_id_selector" required>
              <option disabled selected>Select Draft Plan</option>
              @foreach($plans as $plan)
                  <option value="{{ $plan->PlanID }}" data-year="{{ $plan->FiscalYear }}">
                      {{ $plan->Title }}
                  </option>
              @endforeach
          </select>

        </div>
        <div class="col-md-6">
    <label class="form-label">Planning Period</label>
    <input type="text" id="fiscal_year_input" class="form-control" readonly>
</div>
    </div>

    <!-- Filter Options (Optional) -->
    <div class="row mb-3">
        <div class="col-md-3">
            <label class="form-label">Branch</label>
            <select class="form-select" name="branch_filter">
                <option value="">All</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
                @endforeach
            </select>

        </div>
        <div class="col-md-3">
            <label class="form-label">Department</label>
            <select class="form-select" name="department_filter">
                  <option value="">All</option>
                  @foreach($departments as $dept)
                      <option value="{{ $dept->Id }}">{{ $dept->Name }}</option>
                  @endforeach
            </select>

        </div>
        <div class="col-md-3">
            <label class="form-label">Category</label>
            <select class="form-select">
                <option>All</option>
                <option>IT Equipment</option>
                <option>Stationery</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-outline-primary w-100">Apply Filters</button>
        </div>
    </div>

    <!-- Approved Needs Table -->
    <form method="POST" action="{{ route('plan-from-needs.store') }}">
        @csrf
        <input type="hidden" name="plan_id" id="plan_id_input">

        <table class="table table-bordered table-hover">
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
                @foreach($approvedNeeds as $need)
                <tr>
                    <td><input type="checkbox" class="need-checkbox" name="selected_needs[]" value="{{ $need->Id }}"></td>
                    <td>{{ $need->item->Name ?? 'N/A' }}</td>
                    <td>{{ $need->branch->Name ?? 'N/A' }}</td>
                    <td>{{ $need->department->Name ?? 'N/A' }}</td>
                    <td>{{ $need->RequestedQty }}</td>
                    <td>{{ number_format($need->EstimatedUnitCost * $need->RequestedQty, 2) }}</td>
                    <td>{{ \Carbon\Carbon::parse($need->RequestedDate)->toDateString() }}</td>
                    <td>{{ $need->Justification }}</td>
                    <td>
                        <select class="form-select" name="budgetLine_{{ $need->Id }}">
                            <option selected disabled>Select</option>
                            <option value="201">Nairobi - ICT Equipment (KES 1,000,000)</option>
                            <option value="202">Nairobi - General Supplies (KES 300,000)</option>
                        </select>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Submission -->
        <div class="d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-success" id="submitBtn" disabled>
                ➕ Include Selected Items in Draft Plan
            </button>
        </div>
    </form>
</div>

<!-- Script to sync selected plan ID -->
<script>
    const planSelector = document.getElementById('plan_id_selector');
    const fiscalYearInput = document.getElementById('fiscal_year_input');
    const planIdInput = document.getElementById('plan_id_input');
    const selectAllCheckbox = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.need-checkbox');
    const submitBtn = document.getElementById('submitBtn');

    planSelector.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const fiscalYear = selectedOption.getAttribute('data-year');

        planIdInput.value = this.value;
        fiscalYearInput.value = fiscalYear;
    });

    selectAllCheckbox.addEventListener('click', function () {
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

    toggleSubmitButton();
</script>
@endsection
