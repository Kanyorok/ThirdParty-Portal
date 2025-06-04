@extends('layouts.app')
@section('title', 'Loan-to-Deposit Ratio')
@section('content')
@stack('scripts')
<div class="card p-4">
  <h5>📈 Drilldown – Loan-to-Deposit Ratio (LDR)</h5>
  <p class="text-muted">Analyze lending efficiency by comparing total loans to total deposits.</p>

  <!-- Filters -->
  <div class="row mb-3">
    <div class="col-md-4">
      <label class="form-label">Period</label>
      <select class="form-select" id="ldrPeriod">
        <option>Q1 2025</option>
        <option>Q2 2025</option>
        <option>YTD 2025</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Branch</label>
      <select class="form-select" id="ldrBranch">
        <option>All Branches</option>
        <option>Central Branch</option>
        <option>West Branch</option>
      </select>
    </div>
    <div class="col-md-4 d-flex align-items-end">
      <button class="btn btn-primary w-100" onclick="renderLdrChart()">📊 Apply Filters</button>
    </div>
  </div>

  <!-- Chart -->
  <div class="mb-4">
    <canvas id="ldrChart" height="200"></canvas>
  </div>

  <!-- Data Table -->
  <div class="table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>Branch</th>
          <th>Total Loans (KES)</th>
          <th>Total Deposits (KES)</th>
          <th>LDR (%)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Central Branch</td>
          <td>3,200,000,000</td>
          <td>4,000,000,000</td>
          <td>80%</td>
        </tr>
        <tr>
          <td>West Branch</td>
          <td>2,100,000,000</td>
          <td>2,600,000,000</td>
          <td>80.8%</td>
        </tr>
        <tr>
          <td>North Branch</td>
          <td>1,800,000,000</td>
          <td>2,100,000,000</td>
          <td>85.7%</td>
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
  let ldrChart;

  function renderLdrChart() {
    const ctx = document.getElementById("ldrChart").getContext("2d");
    if (ldrChart) ldrChart.destroy();

    ldrChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Central', 'West', 'North'],
        datasets: [
          {
            label: 'Total Loans (KES)',
            data: [3200000000, 2100000000, 1800000000],
            backgroundColor: '#4e73df'
          },
          {
            label: 'Total Deposits (KES)',
            data: [4000000000, 2600000000, 2100000000],
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

  document.addEventListener("DOMContentLoaded", renderLdrChart);
</script>
@endpush