@extends('layouts.app')
@section('title', '📦 Budget Projections Entry')

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif


    <div class="card p-4">
        {{-- <h5>📦 Budget Projections Entry</h5> --}}
        <p class="text-muted">
            For each product, specify the expected volume and its corresponding projected value. These entries help
            estimate financial forecasts and contribute to the overall budgeting framework.
        </p>

        <form action="{{ route('budgetprojections.storeProjections') }}" method="POST">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="budget" class="form-label">Budget</label>
                    <select id="budget" class="form-select" name="BudgetID" required>
                        <option disabled selected>-- Select Budget --</option>
                        @foreach($budgets as $budget)
                            <option value="{{ $budget->Id }}">{{ $budget->Name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="budgetLine" class="form-label">Budget Line</label>
                    <select id="budgetLine" class="form-select" name="BudgetLineID" required>
                        <option disabled selected>-- Select Budget Line --</option>
                        @foreach($budgetLines as $budgetline)
                            <option value="{{ $budgetline->Id }}">{{ $budgetline->LineName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="productType" class="form-label">Product</label>
                    <select id="productType" class="form-select" name="ProductTypeId" required>
                        <option disabled selected>-- Select Product --</option>
                        {{-- Dynamically loaded --}}
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="NoOfAccounts" class="form-label">No of Accounts</label>
                    <input type="number" class="form-control" id="NoOfAccounts" name="NoOfAccounts" min="1" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Allocation Type</label>
                    <select name="AllocationType" class="form-select" id="allocationType" required>
                        <option disabled selected>-- Select allocation type --</option>
                        <option value="full">Annual or Full Allocation</option>
                        <option value="monthly">Monthly Allocation</option>
                    </select>
                    @error('AllocationType') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div id="fullAllocationSection" class="mb-3" style="display:none;">
                    <h6>Full Allocation (KES)</h6>
                    <input type="number" name="FullAllocation" min="0" class="form-control" placeholder="0.00">
                    @error('FullAllocation') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>

            <!-- Monthly Allocation Fields -->
            <div id="monthlyAllocationSection" class="mt-3 mb-2" style="display:none;">
                <h6>Monthly Allocation(s)</h6>
                <div class="row">
                    @php
                        $months = range(1, 12); // Generates [1, 2, ..., 12]
                    @endphp

                    @foreach($months as $key)
                        <div class="col-md-3 mb-2">
                            <label>Month {{ $key }}</label>
                            <input type="number" min="0" name="monthly_allocations[{{ $key }}]"
                                   class="form-control" placeholder="0.00">
                        </div>
                    @endforeach
                </div>
                @error('monthly_allocations') <small class="text-danger">{{ $message }}</small> @enderror
                <div class="mt-3">
                    <strong>Total Allocation:</strong> <span id="totalAllocation" class=" text-danger">0.00</span>
                </div>
            </div>


            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-success"
                        onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit(); }">
                    💾 Save Activity
                </button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('budgetLine').addEventListener('change', function () {
            const budgetLineId = this.value;
            const productSelect = document.getElementById('productType');

            productSelect.innerHTML = `<option disabled selected>Loading...</option>`;

            fetch(`{{ route('budget-lines.product-types', ':id') }}`.replace(':id', budgetLineId))
                .then(response => response.json())
                .then(data => {
                    productSelect.innerHTML = '<option disabled selected>-- Select Product --</option>';

                    data.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.Id;
                        option.text = product.Name ?? product.Description ?? 'Unnamed Product';
                        productSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    productSelect.innerHTML = '<option disabled selected>Failed to load products</option>';
                    console.error(error);
                });
        });
    </script>


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
@endsection
