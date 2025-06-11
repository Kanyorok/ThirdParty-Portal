@extends('layouts.app')
@section('title', 'Return on Assets')
@section('content')
@stack('scripts')
<div class="card p-4">
  <h5>📈 Drilldown – Return on Assets (ROA)</h5>
  <p class="text-muted">Measure profitability in relation to total assets across branches.</p>

  <!-- Filters -->
  <div class="row mb-3">
    <div class="col-md-4">
      <label class="form-label">Period</label>
      <select class="form-select" id="roaPeriod">
        <option>Q1 2025</option>
        <option>Q2 2025</option>
        <option>YTD 2025</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Branch</label>
      <select class="form-select" id="roaBranch">
        <option>All Branches</option>
        <option>Central Branch</option>
        <option>West Branch</option>
      </select>
    </div>
    <div class="col-md-4 d-flex align-items-end">
      <button class="btn btn-primary w-100" onclick="renderRoaChart()">📊 Apply Filters</button>
    </div>
  </div>

  <!-- Chart -->
  <div class="mb-4">
    <canvas id="roaChart" height="200"></canvas>
  </div>

  <!-- Data Table -->
  <div class="table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>Branch</th>
          <th>Net Profit (KES)</th>
          <th>Total Assets (KES)</th>
          <th>ROA (%)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Central Branch</td>
          <td>500,000,000</td>
          <td>12,000,000,000</td>
          <td>4.17%</td>
        </tr>
        <tr>
          <td>West Branch</td>
          <td>320,000,000</td>
          <td>8,500,000,000</td>
          <td>3.76%</td>
        </tr>
        <tr>
          <td>North Branch</td>
          <td>290,000,000</td>
          <td>7,800,000,000</td>
          <td>3.72%</td>
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
  let roaChart;

  function renderRoaChart() {
    const ctx = document.getElementById("roaChart").getContext("2d");
    if (roaChart) roaChart.destroy();

    roaChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Central', 'West', 'North'],
        datasets: [{
          label: 'ROA (%)',
          data: [4.17, 3.76, 3.72],
          backgroundColor: '#36b9cc'
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

  document.addEventListener("DOMContentLoaded", renderRoaChart);
</script>
@endpush