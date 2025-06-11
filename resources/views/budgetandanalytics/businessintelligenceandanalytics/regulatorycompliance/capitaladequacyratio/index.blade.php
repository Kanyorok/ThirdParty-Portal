@extends('layouts.app')
@section('title', 'Capital Adequacy Ratio')
@section('content')
@stack('scripts')
<div class="card p-4">
  <h5>🛡️ Drilldown – Capital Adequacy Ratio</h5>
  <p class="text-muted">Detailed analysis of capital and risk-weighted assets across categories.</p>

  <!-- Filters -->
  <div class="row mb-3">
    <div class="col-md-3">
      <label class="form-label">Period</label>
      <select class="form-select" id="carPeriod">
        <option>Q1 2025</option>
        <option>Q2 2025</option>
        <option>YTD 2025</option>
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label">Branch</label>
      <select class="form-select" id="carBranch">
        <option>All Branches</option>
        <option>Central</option>
        <option>West</option>
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label">Risk Weight Category</label>
      <select class="form-select" id="carCategory">
        <option>All</option>
        <option>Loans</option>
        <option>Investments</option>
        <option>Off-Balance Sheet</option>
      </select>
    </div>

    <div class="col-md-3 d-flex align-items-end">
      <button class="btn btn-primary w-100" onclick="renderCapitalAdequacyChart()">📊 Apply Filters</button>
    </div>
  </div>

  <!-- Chart -->
  <div class="mb-4">
    <canvas id="carChart" height="200"></canvas>
  </div>

  <!-- Data Table -->
  <div class="table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>Category</th>
          <th>Risk-Weighted Assets (KES)</th>
          <th>Core Capital (KES)</th>
          <th>Total Capital (KES)</th>
          <th>CAR %</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Loans</td>
          <td>1,200,000,000</td>
          <td>220,000,000</td>
          <td>270,000,000</td>
          <td>18.0%</td>
        </tr>
        <tr>
          <td>Investments</td>
          <td>400,000,000</td>
          <td>80,000,000</td>
          <td>90,000,000</td>
          <td>22.5%</td>
        </tr>
        <tr>
          <td>Off-Balance Sheet</td>
          <td>300,000,000</td>
          <td>70,000,000</td>
          <td>85,000,000</td>
          <td>28.3%</td>
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
  let carChart;

  function renderCapitalAdequacyChart() {
    const ctx = document.getElementById("carChart").getContext("2d");
    if (carChart) carChart.destroy();

    carChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Loans', 'Investments', 'Off-Balance Sheet'],
        datasets: [
          {
            label: 'Risk-Weighted Assets (KES)',
            data: [1200000000, 400000000, 300000000],
            backgroundColor: '#36b9cc'
          },
          {
            label: 'Core Capital (KES)',
            data: [220000000, 80000000, 70000000],
            backgroundColor: '#1cc88a'
          },
          {
            label: 'Total Capital (KES)',
            data: [270000000, 90000000, 85000000],
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

  document.addEventListener("DOMContentLoaded", renderCapitalAdequacyChart);
</script>
@endpush