@extends('layouts.app')
@section('title', 'Loan Yield by Product')
@section('content')
    @stack('scripts')
    <div class="card p-4">
        <h5>📈 Drilldown – Loan Yield by Product</h5>
        <p class="text-muted">Analyze the average interest income generated per loan product relative to outstanding
            balances.</p>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Period</label>
                <select class="form-select" id="yieldPeriod">
                    <option>Q1 2025</option>
                    <option>Q2 2025</option>
                    <option>YTD 2025</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Branch</label>
                <select class="form-select" id="yieldBranch">
                    <option>All</option>
                    <option>Central</option>
                    <option>West</option>
                    <option>North</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary w-100" onclick="applyYieldFilters()">📊 Apply Filters</button>
            </div>
        </div>

        <!-- Chart -->
        <div class="mb-4">
            <canvas id="loanYieldChart" height="200"></canvas>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Branch</th>
                    <th>Interest Income (KES)</th>
                    <th>Outstanding Loans (KES)</th>
                    <th>Loan Yield (%)</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>SME Loan</td>
                    <td>Central</td>
                    <td>60,000,000</td>
                    <td>950,000,000</td>
                    <td>6.32%</td>
                </tr>
                <tr>
                    <td>Personal Loan</td>
                    <td>West</td>
                    <td>45,000,000</td>
                    <td>810,000,000</td>
                    <td>5.56%</td>
                </tr>
                <tr>
                    <td>Mortgage</td>
                    <td>North</td>
                    <td>38,000,000</td>
                    <td>700,000,000</td>
                    <td>5.43%</td>
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
        let yieldChart;

        function applyYieldFilters() {
            const ctx = document.getElementById("loanYieldChart").getContext("2d");
            if (yieldChart) yieldChart.destroy();

            yieldChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['SME Loan', 'Personal Loan', 'Mortgage'],
                    datasets: [{
                        label: 'Loan Yield (%)',
                        data: [6.32, 5.56, 5.43],
                        backgroundColor: '#36b9cc'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {legend: {display: false}},
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => value + '%'
                            }
                        }
                    }
                }
            });
        }

        document.addEventListener("DOMContentLoaded", applyYieldFilters);
    </script>
@endpush
