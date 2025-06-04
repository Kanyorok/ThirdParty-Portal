@extends('layouts.app')
@section('title', 'Top Current Account Depositors')
@section('content')
@stack('scripts')
<div class="card p-4">
  <h5>🔍 Drilldown – Top Current Account Depositors</h5>
  <p class="text-muted">Detailed view of clients with the highest balances in current accounts.</p>

  <!-- Filters -->
  <div class="row mb-3">
    <div class="col-md-3">
      <label class="form-label">Period</label>
      <select class="form-select" id="currentPeriod">
        <option>Q1 2025</option>
        <option>Q2 2025</option>
        <option>YTD 2025</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Branch</label>
      <select class="form-select" id="currentBranch">
        <option>All Branches</option>
        <option>Central Branch</option>
        <option>West Branch</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Top N</label>
      <select class="form-select" id="currentTopN">
        <option>Top 3</option>
        <option>Top 5</option>
        <option selected>Top 10</option>
        <option>Top 20</option>
      </select>
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button class="btn btn-primary w-100" onclick="renderTopCurrent()">📊 Apply Filters</button>
    </div>
  </div>

  <!-- Chart -->
  <div class="mb-4">
    <canvas id="topCurrentDrilldownChart" height="200"></canvas>
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
          <td>Client X</td>
          <td>001-120034</td>
          <td>9,200,000</td>
          <td>Jane K.</td>
        </tr>
        <tr>
          <td>2</td>
          <td>Client Y</td>
          <td>001-120102</td>
          <td>7,500,000</td>
          <td>Martin L.</td>
        </tr>
        <tr>
          <td>3</td>
          <td>Client Z</td>
          <td>001-120289</td>
          <td>6,700,000</td>
          <td>Esther N.</td>
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
  let topCurrentDrilldownChart;

  function renderTopCurrent() {
    const ctx = document.getElementById("topCurrentDrilldownChart").getContext("2d");
    if (topCurrentDrilldownChart) topCurrentDrilldownChart.destroy();

    topCurrentDrilldownChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Client X', 'Client Y', 'Client Z'],
        datasets: [{
          label: 'Current Account Balance (KES)',
          data: [9200000, 7500000, 6700000],
          backgroundColor: '#36b9cc'
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false }
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

  document.addEventListener("DOMContentLoaded", renderTopCurrent);
</script>
@endpush