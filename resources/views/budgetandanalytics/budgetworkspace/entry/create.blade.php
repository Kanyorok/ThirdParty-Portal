@extends('layouts.app')
@section('title', 'Budget Entry')

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
    <h5>📦 Budget Entry by Product</h5>
    <p class="text-muted">Branches enter volume and value projections for each product. These will generate budget
        lines based on linked drivers and formulas.</p>

    <form action="{{ route('entrybyproduct.store') }}" method="POST">
        @csrf

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="scenario" class="form-label">Scenario</label>
                <select class="form-select" name="ScenarioID" required>
                    <option disabled selected>-- Select Scenario --</option>
                    @foreach($scenarios as $scenario)
                        <option value="{{ $scenario->Id }}">{{ $scenario->scenarioName }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="currency" class="form-label">Currency</label>
                <select class="form-select" name="CurrencyID" required>
                    <option disabled selected>-- Select Currency --</option>
                    @foreach($currencies as $currency)
                        <option value="{{ $currency->Id }}">{{ $currency->Name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

         <div class="mt-3">
                <label for="period" class="form-label">Period</label>
                <select class="form-select" name="PeriodID" required>
                    <option disabled selected>-- Select Period --</option>
                        @foreach($periods as $period)
                             <option value="{{ $period->Id }}">{{ $period->fiscalYear }}</option>
                        @endforeach
                </select>
            </div>        

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>Volume</th>
                        <th>Projected Value</th>
                    </tr>
                </thead>
                <tbody id="BudgetProducts">
                    <tr>
                        <td>
                            <select class="form-select" name="Products[0][ProductID]" required>
                                @foreach($products as $product)
                                    <option value="{{ $product->Id }}">{{ $product->Name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" class="form-control" name="Products[0][Volume]" placeholder="e.g., 120" required />
                        </td>
                        <td>
                            <input type="number" step="0.01" class="form-control" name="Products[0][Value]" placeholder="e.g., 12000000" required />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="d-flex gap-2">
            <button type="button" id="addRow" class="btn btn-secondary">➕ Add Row</button>
            <button type="submit" class="btn btn-primary">💾 Save Entry</button>
        </div>
    </form>
</div>

<script>
    const products = @json($products);

    document.addEventListener('DOMContentLoaded', function () {
        let rowCount = 1;

        function createOptions(list, valueKey, labelKey) {
            return list.map(item => `<option value="${item[valueKey]}">${item[labelKey]}</option>`).join('');
        }

        const productOptions = createOptions(products, 'Id', 'Name');

        document.getElementById('addRow').addEventListener('click', function () {
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>
                    <select class="form-select" name="Products[${rowCount}][ProductID]" required>
                        ${productOptions}
                    </select>
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
        });
    });
</script>
@endsection
