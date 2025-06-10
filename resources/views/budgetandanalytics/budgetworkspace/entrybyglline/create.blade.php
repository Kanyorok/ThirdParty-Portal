@extends('layouts.app')
@section('title', 'Add Budget Entry')
@section('content')
    <div class="card mt-4">
        <div class="card-header bg-info text-white">➕ Add Budget Line Entry</div>
        <div class="card-body">
            <form>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Budget Period</label>
                        <select class="form-select">
                            <option selected>FY2025-Q1</option>
                            <option>FY2025-Q2</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Branch</label>
                        <select class="form-select">
                            <option selected>Main Branch</option>
                            <option>Westlands Branch</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Budget Line</label>
                    <select class="form-select">

                        <option>Salaries – Staff Costs</option>
                        <option>Marketing Expense</option>
                        <option>Loan Interest Income</option>
                        <option>Non Funded Income</option>
                        <option>Fixed Deposit Interest Expense</option>
                        <option>Savings Deposit Interest Expense</option>

                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Entry Method</label>
                    <select class="form-select" id="entryMethodSelect">
                        <option selected>Manual</option>
                        <option>Driver-Based</option>
                    </select>
                </div>

                <!-- Manual Entry -->
                <div class="manual-entry">
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" class="form-control" placeholder="e.g. 500000">
                    </div>
                </div>

                <!-- Driver-Based Entry -->
                <div class="driver-entry" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Product</label>
                            <select class="form-select">
                                <option selected>Consumer Loan</option>
                                <option>Agri Loan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Driver KPI Type</label>
                            <select class="form-select">
                                <option selected>Loan</option>
                                <option>Deposit</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Projected Amount (KPI)</label>
                            <input type="text" class="form-control" readonly value="10,000,000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Applied Rate (%)</label>
                            <input type="text" class="form-control" readonly value="11.00%">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Computed Budget Value</label>
                        <input type="text" class="form-control" readonly value="1,100,000">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Remarks (optional)</label>
                    <textarea class="form-control" rows="2"></textarea>
                </div>

                <button type="submit" class="btn btn-success">Save Budget Line</button>
            </form>
        </div>
    </div>

    <script>
        const methodSelect = document.getElementById('entryMethodSelect');
        const manualEntry = document.querySelector('.manual-entry');
        const driverEntry = document.querySelector('.driver-entry');

        methodSelect.addEventListener('change', function () {
            if (this.value === 'Driver-Based') {
                manualEntry.style.display = 'none';
                driverEntry.style.display = 'block';
            } else {
                manualEntry.style.display = 'block';
                driverEntry.style.display = 'none';
            }
        });
    </script>
@endsection
