@extends('layouts.app')
@section('title', 'Income by Branch')
@section('content')
    @stack('scripts')
    <div class="card p-4">
        <h5>🏦 Drilldown – Income by Branch</h5>
        <p class="text-muted">Review detailed income streams across branches for selected periods and dimensions.</p>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Period</label>
                <select class="form-select" id="incomePeriod">
                    <option>Q1 2025</option>
                    <option>Q2 2025</option>
                    <option>YTD 2025</option>
                    <option>2024</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Product Type</label>
                <select class="form-select" id="incomeProduct">
                    <option>All</option>
                    <option>Loans</option>
                    <option>Deposits</option>
                    <option>Fees & Commissions</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Branch</label>
                <select class="form-select" id="incomeBranch">
                    <option>All Branches</option>
                    <option>Central Branch</option>
                    <option>West Branch</option>
                    <option>North Branch</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100" onclick="applyIncomeFilters()">📊 Apply Filters</button>
            </div>
        </div>

        <!-- Chart -->
        <div class="mb-4">
            <canvas id="incomeBranchChart" height="200"></canvas>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>Branch</th>
                    <th>Product Type</th>
                    <th>Income (KES)</th>
                    <th>Contribution (%)</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Central Branch</td>
                    <td>Loans</td>
                    <td>420,000,000</td>
                    <td>35.6%</td>
                </tr>
                <tr>
                    <td>Central Branch</td>
                    <td>Deposits</td>
                    <td>180,000,000</td>
                    <td>15.2%</td>
                </tr>
                <tr>
                    <td>Central Branch</td>
                    <td>Fees & Commissions</td>
                    <td>130,000,000</td>
                    <td>11.0%</td>
                </tr>
                <tr>
                    <td>West Branch</td>
                    <td>Loans</td>
                    <td>370,000,000</td>
                    <td>31.4%</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="text-end">
            <a href="{{ route('multidimensional.index') }}" class="btn btn-outline-secondary">🔙 Back to Dashboard</a>
        </div>
    </div>

@endsection

<!-- Chart Script -->
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let incomeChart;

        function applyIncomeFilters() {
            const ctx = document.getElementById("incomeBranchChart").getContext("2d");
            if (incomeChart) incomeChart.destroy();

            incomeChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Central', 'West', 'North'],
                    datasets: [{
                        label: 'Total Income (KES)',
                        data: [730000000, 370000000, 280000000],
                        backgroundColor: '#4e73df'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {legend: {display: false}},
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => value.toLocaleString() + ' KES'
                            }
                        }
                    }
                }
            });
        }

        document.addEventListener("DOMContentLoaded", applyIncomeFilters);
    </script>
@endpush
