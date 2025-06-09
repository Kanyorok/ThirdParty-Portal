@extends('layouts.app')
@section('title', 'Expenses by GL Category')
@section('content')
@stack('scripts')
<div class="card p-4">
  <h5>🧾 Drilldown – Expenses by GL Category</h5>
  <p class="text-muted">Analyze operating expenses broken down by GL category and filtered by cost center or department.</p>

  <!-- Filters -->
  <div class="row mb-3">
    <div class="col-md-3">
      <label class="form-label">Period</label>
      <select class="form-select" id="expensesPeriod">
        <option>Q1 2025</option>
        <option>Q2 2025</option>
        <option>YTD 2025</option>
        <option>2024</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Department</label>
      <select class="form-select" id="expensesDept">
        <option>All</option>
        <option>Finance</option>
        <option>Operations</option>
        <option>HR</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Cost Center</label>
      <select class="form-select" id="costCenter">
        <option>All</option>
        <option>Main Office</option>
        <option>Branch Ops</option>
        <option>Admin Unit</option>
      </select>
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button class="btn btn-primary w-100" onclick="applyExpensesFilters()">📊 Apply Filters</button>
    </div>
  </div>

  <!-- Chart -->
  <div class="mb-4">
    <canvas id="expensesGLChart" height="200"></canvas>
  </div>

  <!-- Table -->
  <div class="table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>GL Category</th>
          <th>Department</th>
          <th>Expense (KES)</th>
          <th>Contribution (%)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Salaries</td>
          <td>HR</td>
          <td>220,000,000</td>
          <td>42.3%</td>
        </tr>
        <tr>
          <td>Utilities</td>
          <td>Operations</td>
          <td>45,000,000</td>
          <td>8.6%</td>
        </tr>
        <tr>
          <td>IT Infrastructure</td>
          <td>Operations</td>
          <td>70,000,000</td>
          <td>13.5%</td>
        </tr>
        <tr>
          <td>Rent</td>
          <td>Admin Unit</td>
          <td>80,000,000</td>
          <td>15.4%</td>
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
  let expensesChart;

  function applyExpensesFilters() {
    const ctx = document.getElementById("expensesGLChart").getContext("2d");
    if (expensesChart) expensesChart.destroy();

    expensesChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Salaries', 'Utilities', 'IT Infrastructure', 'Rent'],
        datasets: [{
          label: 'Expense (KES)',
          data: [220000000, 45000000, 70000000, 80000000],
          backgroundColor: '#e74a3b'
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: value => value.toLocaleString() + ' KES'
            }
          }
        }
      }
    });
  }

  document.addEventListener("DOMContentLoaded", applyExpensesFilters);
</script>
@endpush