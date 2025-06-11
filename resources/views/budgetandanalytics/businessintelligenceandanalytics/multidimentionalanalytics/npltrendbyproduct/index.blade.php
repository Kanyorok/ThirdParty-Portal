@extends('layouts.app')
@section('title', 'NPL Trend by Product')
@section('content')
    @stack('scripts')
    <div class="card p-4">
        <h5>📉 Drilldown – NPL Trend by Product</h5>
        <p class="text-muted">Track non-performing loan trends across different loan products and segments.</p>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Period</label>
                <select class="form-select" id="nplPeriod">
                    <option>Q1 2025</option>
                    <option>Q2 2025</option>
                    <option>YTD 2025</option>
                    <option>2024</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Loan Product</label>
                <select class="form-select" id="loanProduct">
                    <option>All</option>
                    <option>Personal Loan</option>
                    <option>SME Loan</option>
                    <option>Mortgage</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Branch</label>
                <select class="form-select" id="nplBranch">
                    <option>All</option>
                    <option>Central Branch</option>
                    <option>West Branch</option>
                    <option>North Branch</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100" onclick="applyNPLFilters()">📊 Apply Filters</button>
            </div>
        </div>

        <!-- Chart -->
        <div class="mb-4">
            <canvas id="nplTrendChart" height="200"></canvas>
        </div>

        <!-- Updated Table -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Branch</th>
                    <th>NPL Ratio (%)</th>
                    <th>Watch Growth (%)</th>
                    <th>Loan Book Growth (%)</th>
                    <th>Outstanding Amount (KES)</th>
                    <th>NPL Amount (KES)</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>SME Loan</td>
                    <td>Central Branch</td>
                    <td>6.3%</td>
                    <td>1.9%</td>
                    <td>3.8%</td>
                    <td>950,000,000</td>
                    <td>59,850,000</td>
                </tr>
                <tr>
                    <td>Personal Loan</td>
                    <td>West Branch</td>
                    <td>5.4%</td>
                    <td>1.5%</td>
                    <td>3.5%</td>
                    <td>720,000,000</td>
                    <td>38,880,000</td>
                </tr>
                <tr>
                    <td>Mortgage</td>
                    <td>North Branch</td>
                    <td>4.2%</td>
                    <td>1.2%</td>
                    <td>3.0%</td>
                    <td>810,000,000</td>
                    <td>34,020,000</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="text-end">
            <a href="{{ route('multidimensional.index') }}" class="btn btn-outline-secondary">🔙 Back to Dashboard</a>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let nplChart;

        function applyNPLFilters() {
            const ctx = document.getElementById("nplTrendChart").getContext("2d");
            if (nplChart) nplChart.destroy();

            nplChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr'],
                    datasets: [
                        {
                            label: 'NPL Growth (%)',
                            data: [5.1, 5.3, 5.7, 6.0],
                            borderColor: 'rgba(255, 99, 132, 1)',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Watch Growth (%)',
                            data: [1.2, 1.5, 1.8, 2.0],
                            borderColor: 'rgba(255, 205, 86, 1)',
                            backgroundColor: 'rgba(255, 205, 86, 0.2)',
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Loan Book Growth (%)',
                            data: [3.5, 3.7, 4.0, 4.3],
                            borderColor: 'rgba(75, 192, 192, 1)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            fill: true,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {display: true}
                    },
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

        document.addEventListener("DOMContentLoaded", applyNPLFilters);
    </script>
@endpush
