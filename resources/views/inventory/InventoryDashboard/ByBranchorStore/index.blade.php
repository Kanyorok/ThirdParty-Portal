@extends('layouts.app')
@section('title', 'Inventory Dashboard')
@section('content')
<div class="container mt-5">
  <div class="mb-4">
    <h3 class="fw-bold">📊 Inventory Dashboard</h3>
    <p class="text-muted">Filter stock by branch and category</p>
  </div>

  <!-- Filter Controls -->
  <div class="row mb-4 g-3">
    <div class="col-md-4">
      <label for="branchSelect" class="form-label fw-bold">Select Branch</label>
      <select class="form-select" id="branchSelect">
        <option value="all">All Branches</option>
        <option value="central">Central Warehouse</option>
        <option value="branchA">Branch A</option>
        <option value="branchB">Branch B</option>
      </select>
    </div>
    <div class="col-md-4">
      <label for="stockTypeSelect" class="form-label fw-bold">Select Stock Type</label>
      <select class="form-select" id="stockTypeSelect">
        <option value="total">Total Stock</option>
        <option value="damaged">Damaged</option>
        <option value="expired">Expired</option>
        <option value="inTransit">In Transit</option>
      </select>
    </div>
  </div>

  <!-- Chart Section -->
  <div class="card shadow rounded-4 mb-5">
    <div class="card-header bg-light fw-bold">Stock Chart</div>
    <div class="card-body">
      <canvas id="dynamicChart" height="100"></canvas>
    </div>
  </div>

  <!-- Table Section -->
  <div class="card shadow rounded-4">
    <div class="card-header bg-dark text-white rounded-top-4">
      <h5 class="mb-0">📍 Store/Branch Stock Summary</h5>
    </div>
    <div class="card-body">
      <table class="table table-bordered text-center align-middle" id="stockTable">
        <thead class="table-light">
          <tr>
            <th>Store / Branch</th>
            <th>Total</th>
            <th>Damaged</th>
            <th>Expired</th>
            <th>In Transit</th>
          </tr>
        </thead>
        <tbody>
          <!-- Table rows injected by JS -->
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- JavaScript for Dynamic Filtering -->
<script>
  const stockData = {
    central: { total: 5200, damaged: 120, expired: 80, inTransit: 300 },
    branchA: { total: 3100, damaged: 90, expired: 70, inTransit: 200 },
    branchB: { total: 4045, damaged: 240, expired: 170, inTransit: 370 }
  };

  const chartCtx = document.getElementById('dynamicChart').getContext('2d');
  let dynamicChart;

  function updateChartAndTable() {
    const branch = document.getElementById('branchSelect').value;
    const type = document.getElementById('stockTypeSelect').value;

    let labels = [];
    let values = [];

    const tableBody = document.querySelector('#stockTable tbody');
    tableBody.innerHTML = '';

    const branches = Object.keys(stockData);

    branches.forEach(br => {
      const row = stockData[br];
      const branchName = br === 'central' ? 'Central Warehouse' : (br === 'branchA' ? 'Branch A' : 'Branch B');

      // Show relevant branches
      if (branch === 'all' || branch === br) {
        labels.push(branchName);
        values.push(row[type]);

        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${branchName}</td>
          <td>${row.total}</td>
          <td>${row.damaged}</td>
          <td>${row.expired}</td>
          <td>${row.inTransit}</td>
        `;
        tableBody.appendChild(tr);
      }
    });

    if (dynamicChart) dynamicChart.destroy();

    dynamicChart = new Chart(chartCtx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: type.charAt(0).toUpperCase() + type.slice(1),
          data: values,
          backgroundColor: type === 'total' ? '#0d6efd' :
                           type === 'damaged' ? '#ffc107' :
                           type === 'expired' ? '#dc3545' : '#17a2b8'
        }]
      },
      options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
      }
    });
  }

  document.getElementById('branchSelect').addEventListener('change', updateChartAndTable);
  document.getElementById('stockTypeSelect').addEventListener('change', updateChartAndTable);

  // Initialize
  updateChartAndTable();
</script>
@endsection