@extends('layouts.app')
@section('title', 'Top Borrowers')
@section('content')
@stack('scripts')
<div class="card p-4">
  <h5>🔍 Drilldown – Top Borrowers</h5>
  <p class="text-muted">View detailed analysis of clients with the highest outstanding loan balances.</p>

  <!-- Filters -->
  <div class="row mb-3">
    <div class="col-md-3">
      <label class="form-label">Period</label>
      <select class="form-select" id="borrowerPeriod">
        <option>Q1 2025</option>
        <option>Q2 2025</option>
        <option>YTD 2025</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Branch</label>
      <select class="form-select" id="borrowerBranch">
        <option>All Branches</option>
        <option>Central Branch</option>
        <option>West Branch</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Top N</label>
      <select class="form-select" id="borrowerTopN">
        <option>Top 3</option>
        <option>Top 5</option>
        <option selected>Top 10</option>
        <option>Top 20</option>
      </select>
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button class="btn btn-primary w-100" onclick="renderTopBorrowers()">📊 Apply Filters</button>
    </div>
  </div>

  <!-- Chart -->
  <div class="mb-4">
    <canvas id="topBorrowersDrilldownChart" height="200"></canvas>
  </div>

  <!-- Data Table -->
  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Client</th>
          <th>Loan Product</th>
          <th>Outstanding Loan (KES)</th>
          <th>Officer</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>Client A</td>
          <td>SME Loan</td>
          <td>12,000,000</td>
          <td>John M.</td>
        </tr>
        <tr>
          <td>2</td>
          <td>Client B</td>
          <td>Business Loan</td>
          <td>9,800,000</td>
          <td>Sarah K.</td>
        </tr>
        <tr>
          <td>3</td>
          <td>Client C</td>
          <td>Personal Loan</td>
          <td>8,600,000</td>
          <td>David O.</td>
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
  let topBorrowersDrilldownChart;

  function renderTopBorrowers() {
    const ctx = document.getElementById("topBorrowersDrilldownChart").getContext("2d");
    if (topBorrowersDrilldownChart) topBorrowersDrilldownChart.destroy();

    topBorrowersDrilldownChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Client A', 'Client B', 'Client C'],
        datasets: [{
          label: 'Outstanding Loan (KES)',
          data: [12000000, 9800000, 8600000],
          backgroundColor: '#4e73df'
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
  </script>
@endpush