<!-- Blade-compatible HTML with JavaScript enhancements -->
@extends('layouts.app')
@section('content')
    <div class="container-fluid mt-4">
        <h4 class="mb-4">📋 Financial Dashboard – Statement of Financial Position & Income Statement</h4>

        <!-- Year/Scenario/Branch -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Budget Year</label>
                <input type="text" class="form-control form-control-lg" value="2025" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Scenario</label>
                <input type="text" class="form-control form-control-lg" value="Base Scenario" readonly>
            </div>
            <div class="col-md-5">
                <label class="form-label">Branch</label>
                <input type="text" class="form-control form-control-lg" value="Main Branch" readonly>
            </div>
        </div>

        @php
            $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            $sections = [
                'ASSETS - Cash (Budget Line Items)' => ['Cash on Hand', 'BOT Clearing Account/SMR'],
                'ASSETS - Investments (Projections)' => ['Placements with Other Banks', 'Treasury Bills', 'Treasury Bond'],
                'ASSETS - Term Loans (Projections)' => ['Personal Loans', 'Staff Loan', 'Cooperative Loan', 'BAJAJI LOANS', 'SME Loans'],
                'ASSETS - Overdrafts (Budget Line Items)' => ['Overdraft Facility'],
                'ASSETS - Other Assets (Budget Line Items)' => ['Prepaid Expenses', 'Receivables'],
                'LIABILITIES - Savings Deposits (Projections)' => ['Individual', 'Mtoto', 'Vicoba', 'Wekeza', 'Dormant', 'Cooperatives', 'Staff'],
                'LIABILITIES - Current Accounts (Projections)' => ['Cooperatives', 'Individuals'],
                'LIABILITIES - Time Deposits (Projections)' => ['Individuals', 'Cooperatives', 'Other Organisation'],
                'LIABILITIES - Others (Budget Line Items)' => ['Accrued Expenses', 'Deferred Income'],
                'OWNERS EQUITY (Budget Line Items)' => ['Retained Earnings', 'Capital Reserves'],
                'INCOME - Financial Income (Budget Line Items)' => ['Interest Income', 'Interest Expenses', 'Net Interest Income', 'Fees and Commission', 'Other Incomes'],
                'INCOME - Operating Expenses (Budget Line Items)' => ['Staff Expenses', 'Admin Expenses', 'Commissions'],
                'INCOME - Summary Results (Budget Line Items)' => ['Result Before Allowances', 'Allowances for Loan Losses', 'Provision Other Assets', 'Operating Result', 'Recoveries for NPA', 'Profit Before Tax', 'Taxation', 'Net Profit']
            ];
        @endphp

        @foreach($sections as $section => $items)
            <div class="card mb-5 shadow-sm">
                <div class="card-header bg-dark text-white fw-semibold">{{ strtoupper($section) }}</div>
                <div class="card-body p-0 overflow-auto" style="white-space: nowrap;">
                    <table class="table table-bordered table-sm text-center align-middle mb-0"
                           style="min-width: 2000px;">
                <thead class="table-light">
                <tr>
                    <th class="text-start ps-3"
                        style="min-width: 280px;">{{ str_contains($section, 'Projections') ? 'Product/Projection Item' : 'Budget Line Item' }}</th>
                    <th style="min-width: 180px;">Prev. Year Closing</th>
                    @foreach($months as $m)
                        <th style="min-width: 130px;">{{ $m }}</th>
                    @endforeach
                    <th style="min-width: 150px;">Annual Total</th>
                    <th style="min-width: 150px;">% Change</th>
                </tr>
                </thead>
                <tbody>
                @foreach($items as $item)
                    <tr>
                        <td class="text-start ps-3">{{ $item }}</td>
                        <td>
                            <input type="number" class="form-control form-control-lg prev-year text-end" value="1000000"
                                   readonly>
                        </td>
                        @foreach($months as $m)
                            <td>
                                <input type="number" class="form-control form-control-lg month-value text-end" value="0"
                                       readonly>
                            </td>
                        @endforeach
                        <td>
                            <input type="text" class="form-control form-control-lg bg-light fw-bold text-end total"
                                   value="0" readonly>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-lg bg-light text-end percent" value="0%"
                                   readonly>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
        @endforeach

        <div class="text-end mb-5">
            <button type="button" class="btn btn-outline-secondary btn-lg" disabled>🔒 View Only</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const rows = document.querySelectorAll('table tbody tr');

            rows.forEach(row => {
                const monthInputs = row.querySelectorAll('.month-value');
                const totalField = row.querySelector('.total');
                const percentField = row.querySelector('.percent');
                const prevField = row.querySelector('.prev-year');

                let total = 0;
                monthInputs.forEach(input => {
                    total += parseFloat(input.value) || 0;
                });
                totalField.value = total.toLocaleString();

                const prev = parseFloat(prevField.value) || 0;
                const change = prev > 0 ? ((total - prev) / prev * 100).toFixed(2) + '%' : '0%';
                percentField.value = change;
            });
        });
    </script>
@endsection
