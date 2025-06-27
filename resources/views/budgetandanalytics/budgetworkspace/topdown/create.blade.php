@extends('layouts.app')
@section('title', 'GL Sheet Entries')
@section('content')
<div class="container mt-4">
    <form method="POST" action="{{ route('topdownallocation.store') }}">
        @csrf
        @method('POST')
        <input type="hidden" name="branchId" value="{{ $branchId }}">
        <input type="hidden" name="budgetId" value="{{ $budgetId }}">
        <!-- Budget selection -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Select Budget</label>
                <select class="form-select" name="BudgetID" required disabled>
                    <option selected disabled>-- Select Budget --</option>
                    @foreach ($budgets as $item)
                        <option value="{{ $item->Id }}" {{$budgetId==$item->Id?'selected':''}}>{{ $item->Name }} - {{ $item->From.' '.$item->To }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Select Branch</label>
                <select class="form-select" name="BranchID" required disabled>
                    <option selected disabled>-- Select Branch --</option>
                    @foreach ($branches as $item)
                        <option value="{{ $item->Id }}" {{$branchId==$item->Id?'selected':''}}>{{ $item->Name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Select Format</label>
                <select class="form-select form-select-lg1" id="format" name="format" disabled>
                    <option value="m" selected>Monthly</option>
                </select>
            </div>
        </div>

        <!-- MONTHLY FORMAT -->
        <div id="monthly_form" class="table-responsive mb-4">
            <div style="overflow-x: auto; overflow-y: auto; max-height: 600px;">
                <table class="table table-bordered table-striped table-sm" style="min-width: 1600px;">
                    <thead class="table-light text-center">
                        <tr style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;">
                            <th style="position: sticky; left: 0; background: #f8f9fa; z-index: 11;">Account ID</th>
                            <th style="position: sticky; left: 100px; background: #f8f9fa; z-index: 11;">Budget Line</th>
                            @for ($m = 1; $m <= 12; $m++)
                                <th>Month {{ $m }}</th>
                            @endfor
                            <th>Budget 2025</th>
                            <th>Actuals Dec 2024</th>
                            <th>% Change</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($glsMaster as $item)
                            <tr>
                                <td style="position: sticky; left: 0; background: #fff; z-index: 9;">{{ $item->AccountID }}</td>
                                <td style="position: sticky; left: 100px; background: #fff; z-index: 9;">{{ $item->Description }}</td>
                                <input type="hidden" name="gl_data[{{ $item->AccountID }}][Description]" value="{{ $item->Description }}">
                                <input type="hidden" name="gl_data[{{ $item->AccountID }}][GLAccountTypeID]" value="{{ $item->GLAccountTypeID ?? 'NA' }}">
                                @for ($m = 1; $m <= 12; $m++)
                                    <td>
                                        @php
                                            $value = 0.00;
                                            if(isset($isExisting) && $isExisting) {
                                                $monthKey = 'Month_' . $m;
                                                $value = $item->$monthKey ?? 0;
                                            }
                                        @endphp
                                        <input type="number" 
                                               name="monthly_allocations[{{ $item->AccountID }}][{{ $m }}]"
                                               class="form-control"
                                               value="{{ $value }}"
                                               inputmode="numeric"
                                               style="min-width: 120px;" />
                                    </td>
                                @endfor
                                <td>
                                    <input class="form-control total-input" style="min-width: 120px;" value="435000020" readonly />
                                </td>
                                <td>
                                    <input class="form-control" style="min-width: 120px;" value="4000000" readonly />
                                </td>
                                <td>
                                    <input class="form-control" style="min-width: 110px;" value="108.75%" readonly />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <button type="submit" class="btn btn-success" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit(); }">
            💾 Save Budget
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.querySelector('#monthly_form table');

    table.querySelectorAll('tbody tr').forEach(row => {
        const monthlyInputs = row.querySelectorAll('input[name^="monthly_allocations"]');
        const totalInput = row.querySelector('.total-input');

        function calculateRowTotal() {
            let total = 0;
            monthlyInputs.forEach(input => {
                const val = parseFloat(input.value) || 0;
                total += val;
            });
            totalInput.value = total.toFixed(2);
        }

        monthlyInputs.forEach(input => {
            input.addEventListener('input', calculateRowTotal);
        });

        calculateRowTotal();
    });
});
</script>

<style>
#monthly_form table {
    border-collapse: collapse;
}
#monthly_form th, #monthly_form td {
    border: 1px solid #dee2e6;
}
</style>

@endsection