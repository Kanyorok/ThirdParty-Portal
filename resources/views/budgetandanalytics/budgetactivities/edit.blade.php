@extends('layouts.app')
@section('title', 'Edit Activity')

@section('content')
    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>There were some errors with your submission:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container my-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 text-primary">✏️ Edit Activity – <span class="fw-normal">{{ $budgetName }}</span></h5>
            <a href="{{ route('budgetactivities.index') }}" class="btn btn-secondary btn-sm px-3 py-2">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <div class="card shadow-sm rounded-3 p-4">
            <form action="{{ route('budgetactivities.update', $activity->Id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    {{-- Budget Line --}}
                    <div class="col-md-6 mb-3">
                        <label for="budgetLineSelect" class="form-label">Budget Line</label>
                        <select name="BudgetLineID" id="budgetLineSelect" class="form-select" required>
                            <option disabled selected>-- Select Budget Line --</option>
                            @foreach ($budgetLines as $item)
                                <option
                                    value="{{ $item->Id }}" {{ $activity->BudgetLineID == $item->Id ? 'selected' : '' }}>
                                    {{ $item->LineName }}
                                </option>
                            @endforeach
                        </select>
                        @error('BudgetLineID') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- Activity --}}
                    <div class="col-md-6 mb-3">
                        <label for="activitySelect" class="form-label">Activity</label>
                        <select name="ActivityID" id="activitySelect" class="form-select" required>
                            <option disabled selected>-- Select Activity --</option>
                        </select>
                        <div id="activity-loading" class="form-text text-muted d-none">Loading activities...</div>
                        @error('ActivityID') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- Allocation Type --}}
                    <div class="col-md-12 mb-3">
                        <label for="allocationType" class="form-label">Allocation Type</label>
                        <select name="AllocationType" id="allocationType" class="form-select" required>
                            <option disabled selected>-- Select allocation type --</option>
                            <option value="full" {{ $activity->AllocationType == 'full' ? 'selected' : '' }}>
                                Annual / Full Allocation
                            </option>
                            <option value="monthly" {{ $activity->AllocationType == 'monthly' ? 'selected' : '' }}>
                                Monthly Allocation
                            </option>
                        </select>
                        @error('AllocationType') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- Description --}}
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="Description" class="form-control" rows="3"
                                  required>{{ old('Description', $activity->Description) }}</textarea>
                        @error('Description') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                {{-- Full Allocation --}}
                <div id="fullAllocationSection" class="mb-3" style="display:none;">
                    <h6 class="fw-bold">💰 Full Allocation</h6>
                    <input type="number" name="FullAllocation" min="0" class="form-control" placeholder="0.00"
                           value="{{ old('FullAllocation', $activity->FullAllocation) }}">
                    @error('FullAllocation') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                {{-- Monthly Allocations --}}
                <div id="monthlyAllocationSection" style="display:none;">
                    <h6 class="fw-bold">📆 Monthly Allocations</h6>
                    <div class="row">
                        @php $months = range(1, 12); @endphp
                        @foreach($months as $key)
                            <div class="col-md-3 mb-2">
                                <label class="form-label">Month {{ $key }}</label>
                                <input type="number" min="0" name="monthly_allocations[{{ $key }}]"
                                       class="form-control monthly-input" placeholder="0.00"
                                       value="{{ old('monthly_allocations.'.$key, isset($monthlyAllocations[$key]) ? $monthlyAllocations[$key]->Amount : 0) }}">
                            </div>
                        @endforeach
                    </div>
                    @error('monthly_allocations') <small class="text-danger">{{ $message }}</small> @enderror
                    <div class="mt-3">
                        <strong>Total Allocation:</strong>
                        <span id="totalAllocation" class="fw-bold text-danger">0.00</span>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="text-end mt-4" id="submitButtonContainer" style="display:none;">
                    <button type="submit" class="btn btn-success px-4 py-2"
                            onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit(); }">
                        💾 Update Activity
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const allocationType = document.getElementById('allocationType');
            const fullSection = document.getElementById('fullAllocationSection');
            const monthlySection = document.getElementById('monthlyAllocationSection');
            const submitBtnContainer = document.getElementById('submitButtonContainer');
            const totalAllocationEl = document.getElementById('totalAllocation');

            function showSections() {
                if (allocationType.value === 'full') {
                    fullSection.style.display = 'block';
                    monthlySection.style.display = 'none';
                    submitBtnContainer.style.display = 'block';
                } else if (allocationType.value === 'monthly') {
                    fullSection.style.display = 'none';
                    monthlySection.style.display = 'block';
                    submitBtnContainer.style.display = 'block';
                    calculateMonthlyTotal();
                } else {
                    fullSection.style.display = 'none';
                    monthlySection.style.display = 'none';
                    submitBtnContainer.style.display = 'none';
                }
            }

            function calculateMonthlyTotal() {
                let total = 0;
                document.querySelectorAll('.monthly-input').forEach(input => {
                    const val = parseFloat(input.value);
                    if (!isNaN(val)) total += val;
                });
                totalAllocationEl.textContent = total.toFixed(2);
            }

            allocationType?.addEventListener('change', showSections);
            document.querySelectorAll('.monthly-input').forEach(input => {
                input.addEventListener('input', calculateMonthlyTotal);
            });

            showSections();
            calculateMonthlyTotal();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const budgetLineSelect = document.getElementById('budgetLineSelect');
            const activitySelect = document.getElementById('activitySelect');
            const loadingText = document.getElementById('activity-loading');

            function loadActivities(selectedId = null) {
                const budgetLineId = budgetLineSelect.value;
                activitySelect.innerHTML = '<option disabled>-- Select Activity --</option>';
                activitySelect.disabled = true;
                loadingText.classList.remove('d-none');

                fetch(`{{ route('api.budget-activities') }}?budget_line_id=${budgetLineId}`)
                    .then(response => response.json())
                    .then(data => {
                        activitySelect.disabled = false;
                        loadingText.classList.add('d-none');

                        if (!data.length) {
                            activitySelect.innerHTML += `<option disabled>No activities found</option>`;
                            return;
                        }

                        data.forEach(act => {
                            const option = document.createElement('option');
                            option.value = act.Id;
                            option.textContent = act.ActivityName;
                            if (parseInt(act.Id) === parseInt({{ $activity->ActivityID }})) {
                                option.selected = true;
                            }
                            activitySelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error loading activities:', error);
                        loadingText.textContent = '⚠️ Failed to load activities.';
                    });
            }

            budgetLineSelect.addEventListener('change', () => loadActivities());
            if (budgetLineSelect.value) loadActivities({{ $activity->ActivityID }});
        });
    </script>
@endsection
