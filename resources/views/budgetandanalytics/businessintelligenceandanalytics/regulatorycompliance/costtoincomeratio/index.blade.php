@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
    @stack('scripts')
    <div class="card p-4">
        <h5>💸 Drilldown – Cost to Income Ratio</h5>
        <p class="text-muted">Evaluate operational efficiency by comparing operating costs to income.</p>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Period</label>
                <select class="form-select" id="ctiPeriod">
                    <option>Q1 2025</option>
                    <option>Q2 2025</option>
                    <option>YTD 2025</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Branch</label>
                <select class="form-select" id="ctiBranch">
                    <option>All Branches</option>
                    <option>Central Branch</option>
                    <option>West Branch</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary w-100" onclick="renderCtiChart()">📊 Apply Filters</button>
            </div>
        </div>

        <!-- Chart -->
        <div class="mb-4">
            <canvas id="ctiChart" height="200"></canvas>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>Branch</th>
                    <th>Operating Expenses (KES)</th>
                    <th>Operating Income (KES)</th>
                    <th>Cost to Income Ratio (%)</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Central Branch</td>
                    <td>1,200,000,000</td>
                    <td>3,000,000,000</td>
                    <td>40%</td>
                </tr>
                <tr>
                    <td>West Branch</td>
                    <td>950,000,000</td>
                    <td>2,500,000,000</td>
                    <td>38%</td>
                </tr>
                <tr>
                    <td>North Branch</td>
                    <td>700,000,000</td>
                    <td>1,800,000,000</td>
                    <td>38.9%</td>
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
        let ctiChart;

        function renderCtiChart() {
            const ctx = document.getElementById("ctiChart").getContext("2d");
            if (ctiChart) ctiChart.destroy();

            ctiChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Central', 'West', 'North'],
                    datasets: [
                        {
                            label: 'Operating Expenses (KES)',
                            data: [1200000000, 950000000, 700000000],
                            backgroundColor: '#e74a3b'
                        },
                        {
                            label: 'Operating Income (KES)',
                            data: [3000000000, 2500000000, 1800000000],
                            backgroundColor: '#1cc88a'
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

        document.addEventListener("DOMContentLoaded", renderCtiChart);
    </script>
@endpush
