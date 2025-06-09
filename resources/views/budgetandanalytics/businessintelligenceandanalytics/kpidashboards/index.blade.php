@extends('layouts.app')
@section('title', 'KPI Dashboard')
@section('content')
<div class="card p-4">
  <h5>📈 KPI Dashboard</h5>
  <p class="text-muted">Monitor performance and view mini summaries with quick drilldowns per metric.</p>

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
    <div class="col-md-3">
      <label class="form-label">Officer</label>
      <select class="form-select">
        <option>All Officers</option>
        <option>Moses Kariuki</option>
        <option>Janet Chebet</option>
      </select>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-md-4">
      <div class="card p-3 shadow-sm text-center h-100">
        <h6>Loan Book Growth</h6>
        <p><strong>13.2%</strong> vs Target 15% <span class="badge bg-warning text-dark">⚠</span></p>
        <canvas id="loanGrowthChart" height="110"></canvas>
        <a href="{{ route('loanbooktrends.index') }}" class="btn btn-outline-primary btn-sm mt-2 w-100">🔍 Drilldown</a>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card p-3 shadow-sm text-center h-100">
        <h6>Deposit Book Growth</h6>
        <p><strong>12.1%</strong> vs Target 10% <span class="badge bg-success">✅</span></p>
        <canvas id="depositGrowthChart" height="110"></canvas>
        <a href="{{ route('depositbooktrends.index') }}" class="btn btn-outline-primary btn-sm mt-2 w-100">🔍 Drilldown</a>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card p-3 shadow-sm text-center h-100">
        <h6>CASA Perfomance</h6>
        <p><strong>45.0%</strong> Dormancy <span class="badge bg-warning text-dark">⚠</span></p>
        <p><strong>2.0%</strong> Zero Balance <span class="badge bg-warning text-dark">⚠</span></p>
        <canvas id="nimChart" height="110"></canvas>
        <a href="{{ route('dormantcasa.index') }}" class="btn btn-outline-primary btn-sm mt-2 w-100">🔍 Drilldown</a>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card p-3 shadow-sm text-center h-100">
        <h6>Loan Book Perfomance</h6>
        <p><strong>NPL 9.1%</strong> vs Limit 5% <span class="badge bg-danger">🔺</span></p>
        <p><strong>Watch 17.1%</strong> vs Limit 8% <span class="badge bg-danger">🔺</span></p>
        <canvas id="nplChart" height="110"></canvas>
        <a href="{{ route('nplrisk.index') }}" class="btn btn-outline-primary btn-sm mt-2 w-100">🔍 Drilldown</a>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card p-3 shadow-sm text-center h-100">
        <h6>Budget Utilization</h6>
        <p><strong>82%</strong> vs Forecast 85% <span class="badge bg-success">✓</span></p>
        <canvas id="budgetUtilChart" height="110"></canvas>
        <button class="btn btn-outline-primary btn-sm mt-2 w-100">🔍 Drilldown</button>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card p-3 shadow-sm text-center h-100">
        <h6>Revenue per Officer</h6>
        <p><strong>KES 2.4M</strong> vs Target 2.5M <span class="badge bg-warning text-dark">⚠</span></p>
        <canvas id="revenueOfficerChart" height="110"></canvas>
        <button class="btn btn-outline-primary btn-sm mt-2 w-100">🔍 Drilldown</button>
      </div>
    </div>
  </div>
</div>
@endsection
