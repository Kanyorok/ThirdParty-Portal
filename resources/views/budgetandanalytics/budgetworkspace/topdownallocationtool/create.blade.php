@extends('layouts.app')
@section('title', 'Top-Down Budget Allocation')
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
    <h5>🎯 Top-Down Budget Allocation</h5>
    <p class="text-muted">Define central targets per budget line and allocate to branches.</p>

    <form action="{{ route('topdownallocation.store') }}" method="POST">
        @csrf

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Budget Scenario</label>
                <select class="form-select" name="ScenarioID" required>
                    <option value="" disabled selected>Select Scenario</option>
                    @foreach ($scenarios as $scenario)
                        <option value="{{ $scenario->Id }}">{{ $scenario->scenarioName }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Budget Period</label>
                <select class="form-select" name="PeriodID" required>
                    <option value="" disabled selected>Select Period</option>
                    @foreach ($periods as $period)
                        <option value="{{ $period->Id }}">{{ $period->fiscalYear }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Budget Line</label>
                <select class="form-select" name="BudgetLineID" required>
                    <option value="" disabled selected>Select Line</option>
                    @foreach ($lines as $line)
                        <option value="{{ $line->Id }}">{{ $line->LineName }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Total Target</label>
                <input type="number" name="TotalTarget" id="TotalTarget" class="form-control" required placeholder="e.g. 1000000">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="allocationTable">
                <thead class="table-light">
                    <tr>
                        <th>Branch</th>
                        <th>Allocation (%)</th>
                        <th>Allocated Amount (KES)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="allocationBody">
                    <tr>
                        <td>
                            <select class="form-select" name="Allocations[0][BranchID]" required>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" class="form-control percent-input" name="Allocations[0][AllocationPercentage]" value="0" required>
                        </td>
                        <td>
                            <input type="number" class="form-control allocated-input" value="0" readonly>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger remove-row">🗑️</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mb-3 d-flex justify-content-between">
            <button type="button" id="addRowBtn" class="btn btn-secondary">➕ Add Branch</button>
            <button type="submit" class="btn btn-primary">💾 Save Allocation</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let rowCount = 1;

    function recalculateAllocations() {
        const totalTarget = parseFloat(document.getElementById('TotalTarget').value) || 0;
        document.querySelectorAll('#allocationBody tr').forEach(row => {
            const percentInput = row.querySelector('.percent-input');
            const amountInput = row.querySelector('.allocated-input');
            const percent = parseFloat(percentInput.value) || 0;
            amountInput.value = ((percent / 100) * totalTarget).toFixed(2);
        });
    }

    document.getElementById('TotalTarget').addEventListener('input', recalculateAllocations);

    document.getElementById('allocationBody').addEventListener('input', function (e) {
        if (e.target.classList.contains('percent-input')) {
            recalculateAllocations();
        }
    });

    document.getElementById('addRowBtn').addEventListener('click', function () {
        const tbody = document.getElementById('allocationBody');
        const newRow = document.createElement('tr');

        newRow.innerHTML = `
            <td>
                <select class="form-select" name="Allocations[${rowCount}][BranchID]" required>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" class="form-control percent-input" name="Allocations[${rowCount}][AllocationPercentage]" value="0" required>
            </td>
            <td>
                <input type="number" class="form-control allocated-input" value="0" readonly>
            </td>
            <td>
                <button type="button" class="btn btn-danger remove-row">🗑️</button>
            </td>
        `;
        tbody.appendChild(newRow);
        rowCount++;
        recalculateAllocations();
    });

    document.getElementById('allocationBody').addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('tr').remove();
            recalculateAllocations();
        }
    });
});
</script>
@endsection
