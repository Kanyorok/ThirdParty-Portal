@extends('layouts.app')
@section('title', 'Trend & Growth Analysis')
@section('content')
@stack('scripts')
<div class="card p-4">
  <h5>📈 Trend & Growth Analysis</h5>
  <p class="text-muted">View trends in key growth metrics with the ability to drill down for more details.</p>

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
      <label class="form-label">Product</label>
      <select class="form-select">
        <option>All Products</option>
        <option>Loans</option>
        <option>Deposits</option>
      </select>
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button class="btn btn-primary w-100">📊 Apply Filters</button>
    </div>
  </div>

  <!-- Metric Tiles with Charts -->
  <div class="row g-4">
    <div class="col-md-6">
      <div class="card p-3 shadow-sm h-100">
        <h6>📈 Loan Book Growth</h6>
        <p><strong>13.2%</strong> (vs 15%) <span class="badge bg-warning text-dark">⚠</span></p>
        <canvas id="loanTrendChart" height="100"></canvas>
        <button class="btn btn-outline-primary btn-sm mt-2 w-100">🔍 Drilldown</button>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card p-3 shadow-sm h-100">
        <h6>💰 Deposit Book Growth</h6>
        <p><strong>12.1%</strong> (vs 10%) <span class="badge bg-success">✅</span></p>
        <canvas id="depositTrendChart" height="100"></canvas>
        <button class="btn btn-outline-primary btn-sm mt-2 w-100">🔍 Drilldown</button>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card p-3 shadow-sm h-100">
        <h6>📊 Income vs Expense Trend</h6>
        <p><strong>Net: KES 2.6M</strong> <span class="badge bg-success">✓ Improving</span></p>
        <canvas id="incomeExpenseChart" height="100"></canvas>
        <button class="btn btn-outline-primary btn-sm mt-2 w-100">🔍 Drilldown</button>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card p-3 shadow-sm h-100">
        <h6>🧾 Budget Utilization Trend</h6>
        <p><strong>82%</strong> used <span class="badge bg-info">📈 Consistent</span></p>
        <canvas id="budgetTrendChart" height="100"></canvas>
        <button class="btn btn-outline-primary btn-sm mt-2 w-100">🔍 Drilldown</button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  let loanChart, depositChart, incomeChart, budgetChart;

  function renderTrendCharts() {
    // LOAN
    const loanCtx = document.getElementById("loanTrendChart").getContext("2d");
    if (loanChart) loanChart.destroy();
    loanChart = new Chart(loanCtx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar'],
        datasets: [{
          label: 'Loan Growth (%)',
          data: [3.2, 4.1, 6.3],
          backgroundColor: 'rgba(78, 115, 223, 0.2)',
          borderColor: '#4e73df',
          tension: 0.4,
          fill: true
        }]
      },
      options: { responsive: true, plugins: { legend: { display: false } } }
    });

    // DEPOSIT
    const depositCtx = document.getElementById("depositTrendChart").getContext("2d");
    if (depositChart) depositChart.destroy();
    depositChart = new Chart(depositCtx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar'],
        datasets: [{
          label: 'Deposit Growth (%)',
          data: [2.8, 3.4, 5.9],
          backgroundColor: 'rgba(28, 200, 138, 0.2)',
          borderColor: '#1cc88a',
          tension: 0.4,
          fill: true
        }]
      },
      options: { responsive: true, plugins: { legend: { display: false } } }
    });

    // INCOME VS EXPENSE
    const incomeCtx = document.getElementById("incomeExpenseChart").getContext("2d");
    if (incomeChart) incomeChart.destroy();
    incomeChart = new Chart(incomeCtx, {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar'],
        datasets: [
          {
            label: 'Income',
            data: [6.5, 7.2, 7.8],
            backgroundColor: '#36b9cc'
          },
          {
            label: 'Expense',
            data: [4.0, 4.3, 5.2],
            backgroundColor: '#f6c23e'
          }
        ]
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true } }
      }
    });

    // BUDGET UTILIZATION
    const budgetCtx = document.getElementById("budgetTrendChart").getContext("2d");
    if (budgetChart) budgetChart.destroy();
    budgetChart = new Chart(budgetCtx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar'],
        datasets: [{
          label: 'Budget Utilization (%)',
          data: [70, 78, 82],
          backgroundColor: 'rgba(54, 185, 204, 0.2)',
          borderColor: '#36b9cc',
          tension: 0.4,
          fill: true
        }]
      },
      options: { responsive: true, plugins: { legend: { display: false } } }
    });
  }

  document.addEventListener("DOMContentLoaded", renderTrendCharts);
</script>
@endpush