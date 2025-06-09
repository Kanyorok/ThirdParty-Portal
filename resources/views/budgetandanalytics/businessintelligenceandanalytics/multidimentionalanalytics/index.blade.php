@extends('layouts.app')
@section('title', 'Multi-Dimensional Analytics Dashboard')
@section('content')
@stack('scripts')
<div class="card p-4">
  <h4>📊 Multi-Dimensional Analytics Dashboard</h4>
  <p class="text-muted">Interactively explore financial and operational data across branches, officers, products, and more.</p>

  <!-- Filter Panel -->
  <div class="row mb-4">
    <div class="col-md-3">
      <label class="form-label">Period</label>
      <select class="form-select">
        <option>Q1 2025</option>
        <option>Q2 2025</option>
        <option>YTD 2025</option>
        <option>2024</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Branch</label>
      <select class="form-select">
        <option>All</option>
        <option>Central</option>
        <option>West</option>
        <option>North</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Product Type</label>
      <select class="form-select">
        <option>All</option>
        <option>Loans</option>
        <option>Deposits</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Officer</label>
      <select class="form-select">
        <option>All</option>
        <option>James M.</option>
        <option>Grace A.</option>
        <option>Peter K.</option>
      </select>
    </div>
  </div>

  <!-- Analytics Tiles -->
  <div class="row g-4">
    <div class="col-md-4">
      <div class="card shadow-sm text-center p-3">
        <h6 class="text-muted">🧮 Income by Branch</h6>
        <canvas id="incomeByBranchChart" height="100"></canvas>
        <a href="{{ route('incomebybranch.index') }}" class="btn btn-sm btn-outline-primary mt-2">🔍 Drill Down</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm text-center p-3">
        <h6 class="text-muted">💰 Expenses by GL Category</h6>
        <canvas id="expensesByGLChart" height="100"></canvas>
        <a href="{{ route('expensebygl.index') }}" class="btn btn-sm btn-outline-primary mt-2">🔍 Drill Down</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm text-center p-3">
        <h6 class="text-muted">📉 NPL Trend by Product</h6>
        <canvas id="nplByProductChart" height="100"></canvas>
        <a href="{{ route('npltrendbyproduct.index') }}" class="btn btn-sm btn-outline-primary mt-2">🔍 Drill Down</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm text-center p-3">
        <h6 class="text-muted">🏦 Deposit Growth by Officer</h6>
        <canvas id="depositGrowthByOfficerChart" height="100"></canvas>
        <a href="{{ route('depositgrowthbyofficer.index') }}" class="btn btn-sm btn-outline-primary mt-2">🔍 Drill Down</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm text-center p-3">
        <h6 class="text-muted">📊 Budget vs Actual by Cost Center</h6>
        <canvas id="budgetVsActualChart" height="100"></canvas>
        <a href="{{ route('budgetvsactualbybranch.index') }}" class="btn btn-sm btn-outline-primary mt-2">🔍 Drill Down</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm text-center p-3">
        <h6 class="text-muted">💼 Loan Yield by Product</h6>
        <canvas id="loanYieldChart" height="100"></canvas>
        <a href="{{ route('loanyieldbybranch.index') }}" class="btn btn-sm btn-outline-primary mt-2">🔍 Drill Down</a>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  // Chart 1: Income by Branch
  new Chart(document.getElementById('incomeByBranchChart'), {
    type: 'bar',
    data: {
      labels: ['Central', 'West', 'North'],
      datasets: [{
        label: 'Income (KES)',
        data: [420000000, 370000000, 310000000],
        backgroundColor: '#4e73df'
      }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
  });

  // Chart 2: Expenses by GL Category
  new Chart(document.getElementById('expensesByGLChart'), {
    type: 'bar',
    data: {
      labels: ['Salaries', 'Utilities', 'IT', 'Rent'],
      datasets: [{
        label: 'Expenses (KES)',
        data: [220000000, 45000000, 70000000, 80000000],
        backgroundColor: '#e74a3b'
      }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
  });

  // Chart 3: NPL by Product
  new Chart(document.getElementById('nplByProductChart'), {
    type: 'line',
    data: {
      labels: ['Jan', 'Feb', 'Mar'],
      datasets: [{
        label: 'NPL Ratio (%)',
        data: [5.2, 5.6, 6.1],
        backgroundColor: '#f6c23e',
        borderColor: '#f6c23e',
        fill: false
      }]
    },
    options: { responsive: true, plugins: { legend: { display: true } } }
  });

  // Chart 4: Deposit Growth by Officer
  new Chart(document.getElementById('depositGrowthByOfficerChart'), {
    type: 'bar',
    data: {
      labels: ['James M.', 'Grace A.', 'Peter K.'],
      datasets: [{
        label: 'Growth (KES)',
        data: [80000000, 95000000, 74000000],
        backgroundColor: '#36b9cc'
      }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
  });

  // Chart 5: Budget vs Actual by Cost Center
  new Chart(document.getElementById('budgetVsActualChart'), {
    type: 'bar',
    data: {
      labels: ['HR', 'Finance', 'Operations'],
      datasets: [
        {
          label: 'Budget (KES)',
          data: [100000000, 150000000, 120000000],
          backgroundColor: '#858796'
        },
        {
          label: 'Actual (KES)',
          data: [95000000, 160000000, 110000000],
          backgroundColor: '#1cc88a'
        }
      ]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'bottom' } }
    }
  });

  // Chart 6: Loan Yield by Product
  new Chart(document.getElementById('loanYieldChart'), {
    type: 'bar',
    data: {
      labels: ['Personal Loan', 'SME Loan', 'Mortgage'],
      datasets: [{
        label: 'Yield (%)',
        data: [12.5, 14.1, 10.8],
        backgroundColor: '#fd7e14'
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } }
    }
  });
});
</script>
@endpush