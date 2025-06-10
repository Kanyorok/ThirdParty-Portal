@extends('layouts.app')
@section('title', 'Liquidity Ratio')
@section('content')
    @stack('scripts')
    <div class="card p-4">
        <h5>💧 Drilldown – Liquidity Ratio</h5>
        <p class="text-muted">Detailed analysis of liquid assets and short-term liabilities across the institution.</p>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Period</label>
                <select class="form-select" id="liqPeriod">
                    <option>Q1 2025</option>
                    <option>Q2 2025</option>
                    <option>YTD 2025</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Branch</label>
                <select class="form-select" id="liqBranch">
                    <option>All Branches</option>
                    <option>Central Branch</option>
                    <option>West Branch</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary w-100" onclick="renderLiquidityRatioChart()">📊 Apply Filters</button>
            </div>
        </div>

        <!-- Chart -->
        <div class="mb-4">
            <canvas id="liqChart" height="200"></canvas>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>Category</th>
                    <th>Liquid Assets (KES)</th>
                    <th>Short-Term Liabilities (KES)</th>
                    <th>Liquidity Ratio (%)</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Cash & Balances</td>
                    <td>900,000,000</td>
                    <td>400,000,000</td>
                    <td>225%</td>
                </tr>
                <tr>
                    <td>Govt Securities</td>
                    <td>1,200,000,000</td>
                    <td>700,000,000</td>
                    <td>171.4%</td>
                </tr>
                <tr>
                    <td>Call Placements</td>
                    <td>500,000,000</td>
                    <td>300,000,000</td>
                    <td>166.7%</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="text-end">
            <a href="{{ route('regulatoryratios.index') }}" class="btn btn-outline-secondary">🔙 Back to Dashboard</a>
        </div>
    </div>

@endsection

<!-- Chart Script -->
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let liqChart;

        function renderLiquidityRatioChart() {
            const ctx = document.getElementById("liqChart").getContext("2d");
            if (liqChart) liqChart.destroy();

            liqChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Cash & Balances', 'Govt Securities', 'Call Placements'],
                    datasets: [
                        {
                            label: 'Liquid Assets (KES)',
                            data: [900000000, 1200000000, 500000000],
                            backgroundColor: '#36b9cc'
                        },
                        {
                            label: 'Short-Term Liabilities (KES)',
                            data: [400000000, 700000000, 300000000],
                            backgroundColor: '#f6c23e'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => 'KES ' + value.toLocaleString()
                            }
                        }
                    }
                }
            });
        }

        document.addEventListener("DOMContentLoaded", renderLiquidityRatioChart);
    </script>
@endpush
