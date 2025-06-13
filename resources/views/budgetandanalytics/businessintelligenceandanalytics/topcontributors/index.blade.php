@extends('layouts.app')
@section('title', 'Top Contributors')
@section('content')
    @stack('scripts')
    <div class="card p-4">
        <h5>💰 Top Contributors</h5>
        <p class="text-muted">View top contributors to loans and deposits, segmented by account type and contributor
            size.</p>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-3">
                <label class="form-label">Period</label>
                <select class="form-select">
                    <option>Q1 2025</option>
                    <option>Q2 2025</option>
                    <option>YTD 2025</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Branch</label>
                <select class="form-select">
                    <option>All Branches</option>
                    <option>Central Branch</option>
                    <option>West Branch</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Group By</label>
                <select class="form-select">
                    <option>Client</option>
                    <option>Officer</option>
                    <option>Segment</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Top N</label>
                <select class="form-select">
                    <option>Top 3</option>
                    <option>Top 5</option>
                    <option selected>Top 10</option>
                    <option>Top 20</option>
                </select>
            </div>
        </div>

        <!-- Top Contributors Tiles -->
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card shadow-sm p-3 text-center h-100">
                    <h6>🏦 Top Borrowers</h6>
                    <p class="text-muted small">Highest outstanding loan balances.</p>
                    <canvas id="topBorrowersChart" height="100"></canvas>
                    <a href="{{ route('toploans.index') }}" class="btn btn-outline-primary btn-sm mt-2 w-100">🔍 View
                        Drilldown</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm p-3 text-center h-100">
                    <h6>🏛️ Top Current Account Depositors</h6>
                    <p class="text-muted small">Top contributors to current (checking) accounts.</p>
                    <canvas id="topCurrentChart" height="100"></canvas>
                    <a href="{{ route('topcurrentaccounts.index') }}" class="btn btn-outline-primary btn-sm mt-2 w-100">🔍
                        View Drilldown</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm p-3 text-center h-100">
                    <h6>💳 Top Savings Account Depositors</h6>
                    <p class="text-muted small">Top contributors to savings accounts.</p>
                    <canvas id="topSavingsChart" height="100"></canvas>
                    <a href="{{ route('topsavingaccounts.index') }}" class="btn btn-outline-primary btn-sm mt-2 w-100">🔍
                        View Drilldown</a>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm p-3 text-center h-100">
                    <h6>📊 Top Fixed Depositors</h6>
                    <p class="text-muted small">Clients with highest fixed deposit holdings.</p>
                    <canvas id="topFixedChart" height="100"></canvas>
                    <a href="{{ route('topdepositors.index') }}" class="btn btn-outline-primary btn-sm mt-2 w-100">🔍
                        View Drilldown</a>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let topBorrowersChart, topCurrentChart, topSavingsChart, topFixedChart;

        function renderTopContributorsCharts() {
            // Top Borrowers
            const ctxBorrowers = document.getElementById("topBorrowersChart").getContext("2d");
            if (topBorrowersChart) topBorrowersChart.destroy();
            topBorrowersChart = new Chart(ctxBorrowers, {
                type: 'bar',
                data: {
                    labels: ['Client A', 'Client B', 'Client C', 'Client D'],
                    datasets: [{
                        label: 'Loan Amount (KES)',
                        data: [12000000, 9800000, 8600000, 7400000],
                        backgroundColor: '#4e73df'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {legend: {display: false}},
                    scales: {y: {beginAtZero: true}}
                }
            });

            // Top Current Account Depositors
            const ctxCurrent = document.getElementById("topCurrentChart").getContext("2d");
            if (topCurrentChart) topCurrentChart.destroy();
            topCurrentChart = new Chart(ctxCurrent, {
                type: 'bar',
                data: {
                    labels: ['Client X', 'Client Y', 'Client Z'],
                    datasets: [{
                        label: 'Current Deposits (KES)',
                        data: [9200000, 7500000, 6700000],
                        backgroundColor: '#36b9cc'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {legend: {display: false}},
                    scales: {y: {beginAtZero: true}}
                }
            });

            // Top Savings Account Depositors
            const ctxSavings = document.getElementById("topSavingsChart").getContext("2d");
            if (topSavingsChart) topSavingsChart.destroy();
            topSavingsChart = new Chart(ctxSavings, {
                type: 'bar',
                data: {
                    labels: ['Client M', 'Client N', 'Client O'],
                    datasets: [{
                        label: 'Savings Deposits (KES)',
                        data: [6800000, 6100000, 5800000],
                        backgroundColor: '#1cc88a'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {legend: {display: false}},
                    scales: {y: {beginAtZero: true}}
                }
            });

            // Top Fixed Depositors
            const ctxFixed = document.getElementById("topFixedChart").getContext("2d");
            if (topFixedChart) topFixedChart.destroy();
            topFixedChart = new Chart(ctxFixed, {
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
                    plugins: {legend: {display: false}},
                    scales: {y: {beginAtZero: true}}
                }
            });
        }

        document.addEventListener("DOMContentLoaded", renderTopContributorsCharts);
    </script>
@endpush
