@extends('layouts.app')
@section('title', 'Return on Equity')
@section('content')
@stack('scripts')
<div class="card p-4">
  <h5>💹 Drilldown – Return on Equity (ROE)</h5>
  <p class="text-muted">Analyze how effectively equity is generating profits for each branch.</p>

  <!-- Filters -->
  <div class="row mb-3">
    <div class="col-md-4">
      <label class="form-label">Period</label>
      <select class="form-select" id="roePeriod">
        <option>Q1 2025</option>
        <option>Q2 2025</option>
        <option>YTD 2025</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Branch</label>
      <select class="form-select" id="roeBranch">
        <option>All Branches</option>
        <option>Central Branch</option>
        <option>West Branch</option>
      </select>
    </div>
    <div class="col-md-4 d-flex align-items-end">
      <button class="btn btn-primary w-100" onclick="renderRoeChart()">📊 Apply Filters</button>
    </div>
  </div>

  <!-- Chart -->
  <div class="mb-4">
    <canvas id="roeChart" height="200"></canvas>
  </div>

  <!-- Data Table -->
  <div class="table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>Branch</th>
          <th>Net Profit (KES)</th>
          <th>Equity (KES)</th>
          <th>ROE (%)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Central Branch</td>
          <td>500,000,000</td>
          <td>4,200,000,000</td>
          <td>11.9%</td>
        </tr>
        <tr>
          <td>West Branch</td>
          <td>320,000,000</td>
          <td>3,500,000,000</td>
          <td>9.14%</td>
        </tr>
        <tr>
          <td>North Branch</td>
          <td>290,000,000</td>
          <td>3,200,000,000</td>
          <td>9.06%</td>
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
  let roeChart;

  function renderRoeChart() {
    const ctx = document.getElementById("roeChart").getContext("2d");
    if (roeChart) roeChart.destroy();

    roeChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Central', 'West', 'North'],
        datasets: [{
          label: 'ROE (%)',
          data: [11.9, 9.14, 9.06],
          backgroundColor: '#f6c23e'
        }]
      },
      options: {
        responsive: true,
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

  document.addEventListener("DOMContentLoaded", renderRoeChart);
</script>
@endpush