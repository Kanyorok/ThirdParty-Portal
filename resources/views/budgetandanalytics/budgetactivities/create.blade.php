@extends('layouts.app')
@section('title', ' New Budget Activity')
@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <strong>There were some errors with your submission:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="container mt-4">
  <div class="card p-4">
    <div class="card-header bg-dark text-white">
        ➕ New Budget Activity
        <a href="{{ route('budgetactivities.index') }}" class="btn btn-secondary btn-sm float-end">← Back to Activities</a>
    </div>
  <div class="card-body">
    <p class="muted">
        Please use this form to add a new activity under your selected budget line. An activity represents a specific task or 
        project planned within the broader budget. Make sure to associate it with the appropriate budget and budget line. You can 
        also define how funds will be allocated across different months to track planned expenditures throughout the budget period.
    </p>
    {{-- <h5>➕ New Budget Activity</h5> --}}
    <form action="{{ route('budgetactivities.store') }}" method="POST">
        @csrf
        @method('POST')

        <!-- Budget Line and Activity Info -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Budget</label>
                <select name="BudgetID" class="form-select" required>
                    <option disabled selected required>-- Select Budget --</option>
                    @foreach ($budgets as $item)
                        <option value="{{ $item->Id }}">{{ $item->Name }}</option>
                    @endforeach
                </select>
                @error('BudgetLineID') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mb-3">
             <label class="form-label">Budget Line</label>
                <select name="BudgetLineID" class="form-select" id="budgetLineSelect" required>
                    <option disabled selected>-- Select Budget Line --</option>
                    @foreach ($budgetLines as $item)
                        <option value="{{ $item->Id }}">{{ $item->LineName }}</option>
                    @endforeach
                </select>
                @error('BudgetLineID') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Activity</label>
                <select name="ActivityID" id="activitySelect" class="form-select" required disabled>
                    <option selected disabled>-- Select Activity --</option>
                </select>
                <div id="activity-loading" class="form-text text-muted d-none">Loading activities...</div>
                @error('ActivityID') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Allocation Type</label>
                <select name="AllocationType" class="form-select" id="allocationType" required>
                    <option disabled selected>-- Select allocation type --</option>
                    <option value="full">Annual or Full Allocation</option>
                    <option value="monthly">Monthly Allocation</option>
                </select>
                @error('AllocationType') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- <div class="col-md-6 mb-3">
                <label class="form-label">Branch</label>
                <select name="BranchID" class="form-select" required>
                    <option disabled selected>--Select Branch --</option>
                    @foreach ($branches as $item)
                        <option value="{{ $item->Id }}">{{ $item->Name }}</option>
                    @endforeach
                </select>
                @error('BranchID') <small class="text-danger">{{ $message }}</small> @enderror
            </div> --}}

            <div class="col-md-12 mb-3">
                <label class="form-label">Description</label>
                <textarea name="Description" class="form-control" rows="3" placeholder="Brief description..." required></textarea>
                @error('Description') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
        </div>
    
        <!-- Full Allocation Fields -->
        <div id="fullAllocationSection" class="mb-3" style="display:none;">
            <h6>Full Allocation (KES)</h6>
            <input type="number" name="FullAllocation" min="0" class="form-control" placeholder="0.00">
            @error('FullAllocation') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <!-- Monthly Allocation Fields -->
        <div id="monthlyAllocationSection" style="display:none;">
            <h6>Monthly Allocation(s)</h6>
            <div class="row">
                @php
                    $months = range(1, 12); // Generates [1, 2, ..., 12]
                @endphp

                @foreach($months as $key)
                    <div class="col-md-3 mb-2">
                        <label>Month {{ $key }}</label>
                        <input type="number" value="0.00" min="0" name="monthly_allocations[{{ $key }}]" class="form-control" placeholder="0.00">
                    </div>
                @endforeach
            </div>
            @error('monthly_allocations') <small class="text-danger">{{ $message }}</small> @enderror
            <div class="mt-3">
                <strong>Total Allocation:</strong> <span id="totalAllocation" class=" text-danger">0.00</span>
            </div>
        </div>


        <!-- Submit Button (initially hidden) -->
        <div class="text-end mt-4" id="submitButtonContainer" style="display:none;">
            <button type="submit" class="btn btn-success" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit(); }">
                💾 Save Activity
            </button>
        </div>
    </form>

  </div>
  </div>
  </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const allocationType = document.getElementById('allocationType');
        const fullSection = document.getElementById('fullAllocationSection');
        const monthlySection = document.getElementById('monthlyAllocationSection');
        const submitBtnContainer = document.getElementById('submitButtonContainer');
        const totalAllocationEl = document.getElementById('totalAllocation');

        allocationType?.addEventListener('change', function () {
            const selected = this.value;
            if (selected === 'full') {
                fullSection.style.display = 'block';
                monthlySection.style.display = 'none';
                submitBtnContainer.style.display = 'block';
            } else if (selected === 'monthly') {
                fullSection.style.display = 'none';
                monthlySection.style.display = 'block';
                submitBtnContainer.style.display = 'block';
                calculateMonthlyTotal(); // Initial calculation in case fields are already filled
            } else {
                fullSection.style.display = 'none';
                monthlySection.style.display = 'none';
                submitBtnContainer.style.display = 'none';
            }
        });

        function calculateMonthlyTotal() {
            let total = 0;
            const inputs = document.querySelectorAll('input[name^="monthly_allocations"]');
            inputs.forEach(input => {
                const val = parseFloat(input.value);
                if (!isNaN(val)) {
                    total += val;
                }
            });
            totalAllocationEl.textContent = total.toFixed(2);
        }

        // Attach listener to all monthly inputs
        document.querySelectorAll('input[name^="monthly_allocations"]').forEach(input => {
            input.addEventListener('input', calculateMonthlyTotal);
        });
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const budgetLineSelect = document.getElementById('budgetLineSelect');
    const activitySelect = document.getElementById('activitySelect');
    const loadingText = document.getElementById('activity-loading');

    budgetLineSelect.addEventListener('change', function () {
        const budgetLineId = this.value;

        activitySelect.innerHTML = '<option selected disabled>-- Select Activity --</option>';
        activitySelect.disabled = true;
        loadingText.classList.remove('d-none');

        fetch(`{{ route('api.budget-activities') }}?budget_line_id=${budgetLineId}`)
            .then(response => response.json())
            .then(data => {
                activitySelect.disabled = false;
                loadingText.classList.add('d-none');

                if (data.length === 0) {
                    activitySelect.innerHTML += `<option disabled>No activities found</option>`;
                }

                data.forEach(activity => {
                    const option = document.createElement('option');
                    option.value = activity.Id;
                    option.textContent = activity.ActivityName;
                    activitySelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading activities:', error);
                loadingText.textContent = 'Failed to load activities.';
            });
    });
});
</script>

@endsection
