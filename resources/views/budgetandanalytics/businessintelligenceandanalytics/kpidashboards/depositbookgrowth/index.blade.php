@extends('layouts.app')
@section('title', 'Deposit Book Growth')
@section('content')

    <div class="card p-4">
        <h5>🔍 Drilldown – Deposit Book Growth</h5>
        <p class="text-muted">Analyze deposit growth performance by branch or officer.</p>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Period</label>
                <select class="form-select" id="period">
                    <option>Q1 2025</option>
                    <option>Q2 2025</option>
                    <option>YTD 2025</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Group By</label>
                <select class="form-select" id="groupBy">
                    <option>Branch</option>
                    <option>Officer</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Product</label>
                <select class="form-select" id="product">
                    <option>All</option>
                    <option>Fixed Deposit</option>
                    <option>Savings Account</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100" onclick="applyChartFilters()">📊 Apply Filters</button>
            </div>
        </div>

        <!-- Chart -->
        <div class="mb-4">
            <canvas id="depositGrowthDrilldownChart" height="200"></canvas>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Entity</th>
                    <th>Target Growth %</th>
                    <th>Actual Growth %</th>
                    <th>Variance</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td>Central Branch</td>
                    <td>10%</td>
                    <td>12.3%</td>
                    <td>+2.3%</td>
                    <td><span class="badge bg-success">✅ On Track</span></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>West Branch</td>
                    <td>8%</td>
                    <td>7.5%</td>
                    <td>-0.5%</td>
                    <td><span class="badge bg-warning text-dark">⚠ Slight Deviation</span></td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="text-end">
            <a href="{{ route('kpidashboards.index') }}" class="btn btn-outline-secondary">🔙 Back to KPI Dashboard</a>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let chart;

        function renderChart(data, label) {
            const ctx = document.getElementById("depositGrowthDrilldownChart").getContext("2d");
            if (chart) chart.destroy();

            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar'],
                    datasets: [{
                        label: label,
                        data: data,
                        backgroundColor: '#4e73df'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {display: false}
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => value + ' KES'
                            }
                        }
                    }
                }
            });
        }

        function applyChartFilters() {
            const period = document.getElementById("period").value;
            const groupBy = document.getElementById("groupBy").value;
            const product = document.getElementById("product").value;

            let data = [];

            if (period === 'Q1 2025' && product === 'Fixed Deposit') {
                data = [2.5, 3.1, 3.8];
            } else if (period === 'Q2 2025' && groupBy === 'Officer') {
                data = [3.8, 4.2, 4.6];
            } else {
                data = [3.0, 3.4, 3.9];
            }

            renderChart(data, `Deposit Growth (${period})`);
        }

        document.addEventListener("DOMContentLoaded", applyChartFilters);
    </script>
@endpush
