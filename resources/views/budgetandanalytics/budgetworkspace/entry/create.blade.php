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

        <form action="{{ route('budgetprojections.store') }}" method="POST">
            @csrf

            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="scenario" class="form-label">Budget</label>
                    <select class="form-select" name="BudgetID" required>
                        <option disabled selected>-- Select Budget --</option>
                        @foreach($budgets as $budget)
                            <option value="{{ $budget->Id }}">{{ $budget->Name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- <div class="col-md-6">
                    <label for="currency" class="form-label">Currency</label>
                    <select class="form-select" name="CurrencyID" required>
                        <option disabled selected>-- Select Currency --</option>
                        @foreach($currencies as $currency)
                            <option value="{{ $currency->Id }}">{{ $currency->Name }}</option>
                        @endforeach
                    </select>
                </div> --}}
            </div>

            {{-- <div class="mt-3">
                   <label for="period" class="form-label">Period</label>
                   <select class="form-select" name="PeriodID" required>
                       <option disabled selected>-- Select Period --</option>
                           @foreach($periods as $period)
                                <option value="{{ $period->Id }}">{{ $period->fiscalYear }}</option>
                           @endforeach
                   </select>
               </div>         --}}

            <div class="table-responsive mt-3">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>No of Accounts</th>
                        {{-- <th>Projected Value</th> --}}
                    </tr>
                    </thead>
                    <tbody id="BudgetProducts">
                    <tr>
                        <td>
                            <select class="form-select" name="Products[0][ProductID]" required></select>
                        </td>
                        <td>
                            <input type="number" class="form-control" name="Products[0][Volume]" placeholder="e.g., 120"
                                   required/>
                        </td>
                        {{-- <td>
                             <input type="number" step="0.01" class="form-control" name="Products[0][Value]" placeholder="e.g., 12000000" required />
                        </td> --}}
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex gap-2">
                <button type="button" id="addRow" class="btn btn-secondary">➕ Add Row</button>
                <button type="submit" class="btn btn-primary"
                        onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">💾 Save Entry
                </button>
            </div>
        </form>
    </div>

    <script>
        const products = @json($products);

        document.addEventListener('DOMContentLoaded', function () {
            let rowCount = 1;

            function createOptions(list, valueKey, labelKey, selectedValue, selectedInOtherRows = []) {
                return (
                    '<option disabled value="">-- Select Product --</option>' +
                    list.map(item => {
                        const value = item[valueKey];
                        const label = item[labelKey];
                        // Disable if selected in another row and not the current value
                        const disabled = selectedInOtherRows.includes(String(value)) && String(value) !== String(selectedValue) ? 'disabled' : '';
                        const selected = String(value) === String(selectedValue) ? 'selected' : '';
                        return `<option value="${value}" ${disabled} ${selected}>${label}</option>`;
                    }).join('')
                );
            }

            function getSelectedProductIds() {
                return Array.from(document.querySelectorAll('select[name^="Products"][name$="[ProductID]"]'))
                    .map(sel => sel.value)
                    .filter(val => val !== '');
            }

            function updateAllProductDropdowns() {
                const allDropdowns = document.querySelectorAll('select[name^="Products"][name$="[ProductID]"]');
                const selected = getSelectedProductIds();
                allDropdowns.forEach(sel => {
                    const currentValue = sel.value;
                    sel.innerHTML = createOptions(products, 'Id', 'Description', currentValue, selected);
                    sel.value = currentValue; // Restore selection
                });
            }

            document.getElementById('addRow').addEventListener('click', function () {
                const newRow = document.createElement('tr');
                newRow.innerHTML = `
                <td>
                    <select class="form-select" name="Products[${rowCount}][ProductID]" required></select>
                </td>
                <td>
                    <input type="number" class="form-control" name="Products[${rowCount}][Volume]" placeholder="e.g., 120" required />
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control" name="Products[${rowCount}][Value]" placeholder="e.g., 12000000" required />
                </td>
            `;
                document.getElementById('BudgetProducts').appendChild(newRow);
                rowCount++;
                updateAllProductDropdowns();
            });

            document.getElementById('BudgetProducts').addEventListener('change', function (e) {
                if (e.target.matches('select[name^="Products"][name$="[ProductID]"]')) {
                    updateAllProductDropdowns();
                }
            });

            // On page load, update all dropdowns (including the first)
            updateAllProductDropdowns();
        });
    </script>
@endsection
