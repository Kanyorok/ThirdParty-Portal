@extends('layouts.app')
@section('title', 'Loan Book Growth')
@section('content')
@stack('scripts')
<div class="card p-4">
  <h5>📊 Drilldown – Loan Book Growth Trend</h5>
  <p class="text-muted">Explore the monthly growth pattern of the loan book by branch, product, or officer.</p>
  
  <!-- Filters -->
  <div class="row mb-3">
    <div class="col-md-4">
      <label class="form-label">Period</label>
      <select class="form-select" id="loanTrendPeriod">
        <option>Q1 2025</option>
        <option>Q2 2025</option>
        <option>YTD 2025</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Group By</label>
      <select class="form-select" id="loanTrendGroup">
        <option>Branch</option>
        <option>Product</option>
        <option>Officer</option>
      </select>
    </div>
    <div class="col-md-4 d-flex align-items-end">
      <button class="btn btn-primary w-100" onclick="renderLoanGrowthChart()">📈 Apply Filters</button>
    </div>
  </div>

  <!-- Chart Display -->
  <div class="mb-4">
    <canvas id="loanGrowthDrilldownChart" height="220"></canvas>
  </div>

  <!-- Summary Table -->
  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Month</th>
          <th>Loan Book Value (KES)</th>
          <th>Growth %</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>January</td>
          <td>98,000,000</td>
          <td>+3.2%</td>
        </tr>
        <tr>
          <td>2</td>
          <td>February</td>
          <td>102,100,000</td>
          <td>+4.1%</td>
        </tr>
        <tr>
          <td>3</td>
          <td>March</td>
          <td>108,560,000</td>
          <td>+6.3%</td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="text-end">
    <a href="{{ route('kpidashboards.index') }}" class="btn btn-outline-secondary">🔙 Back to Analytics Dashboard</a>
  </div>
</div>

@endsection

<!-- Chart Script -->
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  let loanGrowthChart;

  function renderLoanGrowthChart() {
    const ctx = document.getElementById("loanGrowthDrilldownChart").getContext("2d");
    if (loanGrowthChart) loanGrowthChart.destroy();

    loanGrowthChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar'],
        datasets: [{
          label: 'Loan Book Growth (%)',
          data: [3.2, 4.1, 6.3],
          backgroundColor: 'rgba(78, 115, 223, 0.2)',
          borderColor: '#4e73df',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'top' }
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

  document.addEventListener("DOMContentLoaded", renderLoanGrowthChart);
</script>
@endpush