@extends('layouts.app')
@section('title', 'Top Savings Account Depositors')
@section('content')
    <div class="card p-4">
        <h5>🔍 Drilldown – Top Savings Account Depositors</h5>
        <p class="text-muted">Detailed view of clients with the highest balances in savings accounts.</p>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Period</label>
                <select class="form-select" id="savingsPeriod">
                    <option>Q1 2025</option>
                    <option>Q2 2025</option>
                    <option>YTD 2025</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Branch</label>
                <select class="form-select" id="savingsBranch">
                    <option>All Branches</option>
                    <option>Central Branch</option>
                    <option>West Branch</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Top N</label>
                <select class="form-select" id="savingsTopN">
                    <option>Top 3</option>
                    <option>Top 5</option>
                    <option selected>Top 10</option>
                    <option>Top 20</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100" onclick="renderTopSavings()">📊 Apply Filters</button>
            </div>
        </div>

        <!-- Chart -->
        <div class="mb-4">
            <canvas id="topSavingsDrilldownChart" height="200"></canvas>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Client</th>
                    <th>Account No</th>
                    <th>Balance (KES)</th>
                    <th>Officer</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td>Client M</td>
                    <td>002-200111</td>
                    <td>6,800,000</td>
                    <td>Mary O.</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Client N</td>
                    <td>002-200207</td>
                    <td>6,100,000</td>
                    <td>Peter W.</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Client O</td>
                    <td>002-200345</td>
                    <td>5,800,000</td>
                    <td>Linda A.</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="text-end">
            <a href="{{ route('topcontributors.index') }}" class="btn btn-outline-secondary">🔙 Back to Dashboard</a>
        </div>
    </div>

@endsection

<!-- Chart Script -->
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let topSavingsDrilldownChart;

        function renderTopSavings() {
            const ctx = document.getElementById("topSavingsDrilldownChart").getContext("2d");
            if (topSavingsDrilldownChart) topSavingsDrilldownChart.destroy();

            topSavingsDrilldownChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Client M', 'Client N', 'Client O'],
                    datasets: [{
                        label: 'Savings Account Balance (KES)',
                        data: [6800000, 6100000, 5800000],
                        backgroundColor: '#1cc88a'
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
                                callback: value => 'KES ' + value.toLocaleString()
                            }
                        }
                    }
                }
            });
        }

        document.addEventListener("DOMContentLoaded", renderTopSavings);
    </script>
@endpush
