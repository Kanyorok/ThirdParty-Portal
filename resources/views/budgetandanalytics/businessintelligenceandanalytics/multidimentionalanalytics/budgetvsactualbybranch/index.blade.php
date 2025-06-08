@extends('layouts.app')
@section('title', 'Budget vs Actual')
@section('content')
@stack('scripts')
<div class="card p-4">
  <h5>📊 Drilldown – Budget vs Actual</h5>
  <p class="text-muted">Compare budgeted values versus actuals by department, GL category, or branch over time.</p>

  <!-- Filters -->
  <div class="row mb-3">
    <div class="col-md-3">
      <label class="form-label">Period</label>
      <select class="form-select" id="budgetPeriod">
        <option>Q1 2025</option>
        <option>Q2 2025</option>
        <option>YTD 2025</option>
        <option>2024</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Category</label>
      <select class="form-select" id="budgetCategory">
        <option>All</option>
        <option>Revenue</option>
        <option>Operating Expense</option>
        <option>Interest Income</option>
        <option>Interest Expense</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Branch</label>
      <select class="form-select" id="budgetBranch">
        <option>All</option>
        <option>Central</option>
        <option>East</option>
        <option>North</option>
      </select>
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button class="btn btn-primary w-100" onclick="applyBudgetFilters()">📈 Apply Filters</button>
    </div>
  </div>

  <!-- Chart -->
  <div class="mb-4">
    <canvas id="budgetVsActualChart" height="200"></canvas>
  </div>

  <!-- Table -->
  <div class="table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>GL Category</th>
          <th>Branch</th>
          <th>Budgeted (KES)</th>
          <th>Actual (KES)</th>
          <th>Variance (KES)</th>
          <th>Performance (%)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Interest Income</td>
          <td>Central</td>
          <td>500,000,000</td>
          <td>520,000,000</td>
          <td>+20,000,000</td>
          <td>104%</td>
        </tr>
        <tr>
          <td>Operating Expense</td>
          <td>East</td>
          <td>150,000,000</td>
          <td>165,000,000</td>
          <td>-15,000,000</td>
          <td>110%</td>
        </tr>
        <tr>
          <td>Revenue</td>
          <td>North</td>
          <td>300,000,000</td>
          <td>310,000,000</td>
          <td>+10,000,000</td>
          <td>103%</td>
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
  let budgetChart;

  function applyBudgetFilters() {
    const ctx = document.getElementById("budgetVsActualChart").getContext("2d");
    if (budgetChart) budgetChart.destroy();

    budgetChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Interest Income', 'Operating Expense', 'Revenue'],
        datasets: [
          {
            label: 'Budgeted',
            data: [500, 150, 300],
            backgroundColor: 'rgba(54, 162, 235, 0.7)'
          },
          {
            label: 'Actual',
            data: [520, 165, 310],
            backgroundColor: 'rgba(255, 99, 132, 0.7)'
          }
        ]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: true } },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: value => value + 'M'
            }
          }
        }
      }
    });
  }

  document.addEventListener("DOMContentLoaded", applyBudgetFilters);
</script>
@endpush
