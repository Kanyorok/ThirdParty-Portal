@extends('layouts.app')
@section('title', 'CASA Account Status')
@section('content')
<div class="card p-4">
  <h5>🏦 Drilldown – CASA Account Status</h5>
  <p class="text-muted">View the distribution of CASA accounts by activity status across branches or officers.</p>

  <div class="row mb-3">
    <div class="col-md-4">
      <label class="form-label">Period</label>
      <select class="form-select" id="casaPeriod">
        <option>Q1 2025</option>
        <option>Q2 2025</option>
        <option>YTD 2025</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Branch</label>
      <select class="form-select" id="casaBranch">
        <option>All Branches</option>
        <option>Central Branch</option>
        <option>West Branch</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Product Type</label>
      <select class="form-select" id="casaProduct">
        <option>All</option>
        <option>Current Account</option>
        <option>Savings Account</option>
      </select>
    </div>
  </div>

  <!-- Chart -->
  <div class="mb-4">
    <canvas id="casaStatusChart" height="200"></canvas>
  </div>

  <!-- Status Table -->
  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Entity</th>
          <th>Active Accounts</th>
          <th>Inoperative</th>
          <th>Dormant</th>
          <th>Zero Balance</th>
          <th>Total Accounts</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>Central Branch</td>
          <td>14,200</td>
          <td>1,150</td>
          <td>650</td>
          <td>900</td>
          <td>16,900</td>
        </tr>
        <tr>
          <td>2</td>
          <td>West Branch</td>
          <td>9,500</td>
          <td>800</td>
          <td>320</td>
          <td>450</td>
          <td>11,070</td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="text-end">
    <button class="btn btn-outline-secondary">🔙 Back to KPI Dashboard</button>
  </div>
</div>


@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  let casaChart;

  function renderCasaChart(data) {
    const ctx = document.getElementById("casaStatusChart").getContext("2d");
    if (casaChart) casaChart.destroy();

    casaChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: data.labels,
        datasets: [
          {
            label: 'Active',
            backgroundColor: '#28a745',
            data: data.active
          },
          {
            label: 'Inoperative',
            backgroundColor: '#ffc107',
            data: data.inoperative
          },
          {
            label: 'Dormant',
            backgroundColor: '#fd7e14',
            data: data.dormant
          },
          {
            label: 'Zero Balance',
            backgroundColor: '#dc3545',
            data: data.zeroBalance
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          title: {
            display: false
          },
          legend: {
            position: 'top'
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  }

  function applyCasaFilters() {
    const period = document.getElementById("casaPeriod").value;
    const branch = document.getElementById("casaBranch").value;
    const product = document.getElementById("casaProduct").value;

    // Simulated data based on filters
    const data = {
      labels: ['Central Branch', 'West Branch'],
      active: [14200, 9500],
      inoperative: [1150, 800],
      dormant: [650, 320],
      zeroBalance: [900, 450]
    };

    renderCasaChart(data);
  }

  document.addEventListener("DOMContentLoaded", applyCasaFilters);
</script>
@endpush
