@extends('layouts.app')
@section('title', '✏️ Edit Budget Projection')

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
        <form action="{{ route('budgetprojections.update', $projection->Id) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="budget" class="form-label">Budget</label>
                    <select id="budget" class="form-select" name="BudgetID" required>
                        <option disabled>-- Select Budget --</option>
                        @foreach($budgets as $budget)
                            <option
                                value="{{ $budget->Id }}" {{ $projection->BudgetID == $budget->Id ? 'selected' : '' }}>{{ $budget->Name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="budgetLine" class="form-label">Budget Line</label>
                    <select id="budgetLine" class="form-select" name="BudgetLineID" required>
                        <option disabled>-- Select Budget Line --</option>
                        @foreach($budgetLines as $budgetline)
                            <option
                                value="{{ $budgetline->Id }}" {{ $projection->BudgetLineID == $budgetline->Id ? 'selected' : '' }}>{{ $budgetline->LineName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="productType" class="form-label">Product</label>
                    <select id="productType" class="form-select" name="ProductTypeId" required>
                        <option disabled>-- Select Product --</option>
                        @foreach($products as $product)
                            <option
                                value="{{ $product->Id }}" {{ $product->Id == $productTypeId ? 'selected' : '' }}>{{ $product->Name ?? $product->Description }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="NoOfAccounts" class="form-label">No of Accounts</label>
                    <input type="number" class="form-control" id="NoOfAccounts" name="NoOfAccounts" min="1"
                           value="{{ $projection->NumberOfAccounts }}" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Allocation Type</label>
                    <select name="AllocationType" class="form-select" id="allocationType" required>
                        <option disabled>-- Select allocation type --</option>
                        <option value="full" {{ $projection->AllocationType === 'full' ? 'selected' : '' }}>Annual or
                            Full Allocation
                        </option>
                        <option value="monthly" {{ $projection->AllocationType === 'monthly' ? 'selected' : '' }}>
                            Monthly Allocation
                        </option>
                    </select>
                </div>
                <div id="fullAllocationSection" class="mb-3"
                     style="display: {{ $projection->AllocationType === 'full' ? 'block' : 'none' }};">
                    <h6>Full Allocation (KES)</h6>
                    <input type="number" name="FullAllocation" min="0" class="form-control" placeholder="0.00"
                           value="{{ $projection->FullAllocation }}">
                </div>
            </div>

            <!-- Monthly Allocation Fields -->
            <div id="monthlyAllocationSection" class="mt-3 mb-2"
                 style="display: {{ $projection->AllocationType === 'monthly' ? 'block' : 'none' }};">
                <h6>Monthly Allocation(s)</h6>
                <div class="row">
                    @foreach(range(1, 12) as $key)
                        @php
                            $amount = optional($monthlyAllocations->firstWhere('Month', $key))->Amount ?? 0.00;
                        @endphp
                        <div class="col-md-3 mb-2">
                            <label>Month {{ $key }}</label>
                            <input type="number" min="0" name="monthly_allocations[{{ $key }}]"
                                   class="form-control" placeholder="0.00" value="{{ $amount }}" step="0.01">
                        </div>
                    @endforeach
                </div>
                <div class="mt-3">
                    <strong>Total Allocation:</strong> <span id="totalAllocation" class=" text-danger">0.00</span>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4" id="submitButtonContainer">
                <button type="submit" class="btn btn-success"
                        onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Updating...'; this.form.submit(); }">
                    Update Projection
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
            const totalAllocationEl = document.getElementById('totalAllocation');

            allocationType.addEventListener('change', function () {
                const selected = this.value;
                fullSection.style.display = selected === 'full' ? 'block' : 'none';
                monthlySection.style.display = selected === 'monthly' ? 'block' : 'none';
            });

            function calculateMonthlyTotal() {
                let total = 0;
                document.querySelectorAll('input[name^="monthly_allocations"]').forEach(input => {
                    const val = parseFloat(input.value);
                    if (!isNaN(val)) total += val;
                });
                totalAllocationEl.textContent = total.toFixed(2);
            }

            document.querySelectorAll('input[name^="monthly_allocations"]').forEach(input => {
                input.addEventListener('input', calculateMonthlyTotal);
            });

            calculateMonthlyTotal();
        });
    </script>
@endsection
