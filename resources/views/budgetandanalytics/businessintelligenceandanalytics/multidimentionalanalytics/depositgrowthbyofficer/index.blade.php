@extends('layouts.app')
@section('title', 'Deposit Growth by Officer')
@section('content')
    @stack('scripts')
    <div class="card p-4">
        <h5>💼 Drilldown – Deposit Growth by Officer</h5>
        <p class="text-muted">Track deposit mobilization performance by account officers across branches and
            products.</p>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Period</label>
                <select class="form-select" id="officerPeriod">
                    <option>Q1 2025</option>
                    <option>Q2 2025</option>
                    <option>YTD 2025</option>
                    <option>2024</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Branch</label>
                <select class="form-select" id="officerBranch">
                    <option>All</option>
                    <option>Central Branch</option>
                    <option>West Branch</option>
                    <option>North Branch</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Product</label>
                <select class="form-select" id="officerProduct">
                    <option>All</option>
                    <option>Savings Account</option>
                    <option>Current Account</option>
                    <option>Fixed Deposit</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100" onclick="applyOfficerDepositFilters()">📊 Apply Filters</button>
            </div>
        </div>

        <!-- Chart -->
        <div class="mb-4">
            <canvas id="depositOfficerChart" height="200"></canvas>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>Officer</th>
                    <th>Branch</th>
                    <th>Product</th>
                    <th>Target Growth (%)</th>
                    <th>Actual Growth (%)</th>
                    <th>Variance</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Jane Mwangi</td>
                    <td>Central Branch</td>
                    <td>Savings Account</td>
                    <td>12%</td>
                    <td>14.5%</td>
                    <td>+2.5%</td>
                </tr>
                <tr>
                    <td>Peter Otieno</td>
                    <td>West Branch</td>
                    <td>Fixed Deposit</td>
                    <td>10%</td>
                    <td>9.1%</td>
                    <td>-0.9%</td>
                </tr>
                <tr>
                    <td>Lucy Kamau</td>
                    <td>North Branch</td>
                    <td>Current Account</td>
                    <td>8%</td>
                    <td>10.2%</td>
                    <td>+2.2%</td>
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
        let officerChart;

        function applyOfficerDepositFilters() {
            const ctx = document.getElementById("depositOfficerChart").getContext("2d");
            if (officerChart) officerChart.destroy();

            officerChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jane Mwangi', 'Peter Otieno', 'Lucy Kamau'],
                    datasets: [{
                        label: 'Actual Growth (%)',
                        data: [14.5, 9.1, 10.2],
                        backgroundColor: '#1cc88a'
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

        document.addEventListener("DOMContentLoaded", applyOfficerDepositFilters);
    </script>
@endpush
