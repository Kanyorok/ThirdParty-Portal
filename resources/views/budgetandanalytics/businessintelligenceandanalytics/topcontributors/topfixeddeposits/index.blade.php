@extends('layouts.app')
@section('title', 'Top Fixed Depositors')
@section('content')
    @stack('scripts')
    <div class="card p-4">
        <h5>🔍 Drilldown – Top Fixed Depositors</h5>
        <p class="text-muted">Detailed view of clients with the highest fixed deposit holdings.</p>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Period</label>
                <select class="form-select" id="fixedPeriod">
                    <option>Q1 2025</option>
                    <option>Q2 2025</option>
                    <option>YTD 2025</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Branch</label>
                <select class="form-select" id="fixedBranch">
                    <option>All Branches</option>
                    <option>Central Branch</option>
                    <option>West Branch</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Top N</label>
                <select class="form-select" id="fixedTopN">
                    <option>Top 3</option>
                    <option>Top 5</option>
                    <option selected>Top 10</option>
                    <option>Top 20</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100" onclick="renderTopFixed()">📊 Apply Filters</button>
            </div>
        </div>

        <!-- Chart -->
        <div class="mb-4">
            <canvas id="topFixedDrilldownChart" height="200"></canvas>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Client</th>
                    <th>FD Account No</th>
                    <th>Deposit Amount (KES)</th>
                    <th>Officer</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td>Client P</td>
                    <td>003-300901</td>
                    <td>15,000,000</td>
                    <td>Joan K.</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Client Q</td>
                    <td>003-300777</td>
                    <td>13,200,000</td>
                    <td>Brian T.</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Client R</td>
                    <td>003-300456</td>
                    <td>12,700,000</td>
                    <td>Faith L.</td>
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
        let topFixedDrilldownChart;

        function renderTopFixed() {
            const ctx = document.getElementById("topFixedDrilldownChart").getContext("2d");
            if (topFixedDrilldownChart) topFixedDrilldownChart.destroy();

            topFixedDrilldownChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Client P', 'Client Q', 'Client R'],
                    datasets: [{
                        label: 'Fixed Deposit (KES)',
                        data: [15000000, 13200000, 12700000],
                        backgroundColor: '#f6c23e'
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

        document.addEventListener("DOMContentLoaded", renderTopFixed);
    </script>
@endpush
