@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container-fluid mt-4">
  <div class="mb-4">
    <h3 class="fw-bold">📦 Stock Movement Dashboard</h3>
    <p class="text-muted">Track quantities and values by filters & date range</p>
  </div>

  <!-- Filters -->
  <div class="row g-3 mb-3">
    <div class="col-md-2">
      <select class="form-select" id="branchSelect">
        <option value="">All Branches</option>
        <option value="branchA">Branch A</option>
        <option value="branchB">Branch B</option>
      </select>
    </div>
    <div class="col-md-2">
      <select class="form-select" id="storeSelect">
        <option value="">All Stores</option>
        <option value="main">Main Store</option>
        <option value="back">Back Store</option>
      </select>
    </div>
    <div class="col-md-2">
      <select class="form-select" id="categorySelect">
        <option value="">All Categories</option>
        <option value="office">Office Supplies</option>
        <option value="electronics">Electronics</option>
      </select>
    </div>
    <div class="col-md-2">
      <select class="form-select" id="itemSelect">
        <option value="">All Items</option>
        <option value="itm001">A4 Paper</option>
        <option value="itm002">Printer</option>
      </select>
    </div>
    <div class="col-md-2">
      <input type="date" class="form-control" id="fromDate" value="2025-05-01">
    </div>
    <div class="col-md-2">
      <input type="date" class="form-control" id="toDate" value="2025-05-02">
    </div>
  </div>

  <div class="text-end mb-3">
    <button class="btn btn-primary" id="refreshBtn">🔄 Refresh</button>
  </div>

  <div class="row">
    <!-- Stock Movement Grid -->
    <div class="col-md-2">
      <div class="card shadow rounded-4">
        <div class="card-header bg-light fw-bold">📋 Stock Movement Grid</div>
        <div class="card-body p-2" id="movementContainer"></div>
      </div>
    </div>

    <!-- Stock Movement Charts -->
    <div class="col-md-10">
      <div class="card shadow rounded-4 mb-4">
        <div class="card-header bg-light fw-bold">📊 Quantity Movement Chart</div>
        <div class="card-body">
          <canvas id="quantityChart" height="180"></canvas>
        </div>
      </div>
      <div class="card shadow rounded-4">
        <div class="card-header bg-light fw-bold">💰 Value Movement Chart</div>
        <div class="card-body">
          <canvas id="valueChart" height="180"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // Mock data
  const movementMockData = {
    itm001: {
      label: "A4 Paper",
      quantity: [500, 120, 100, 520],
      value: [5000, 2400, 2000, 5400]
    },
    itm002: {
      label: "Printer",
      quantity: [10, 3, 2, 11],
      value: [100000, 30000, 20000, 110000]
    }
  };

  let quantityChartInstance, valueChartInstance;

  function createMovementSection(title, opening, valueIn, valueOut, closing) {
    return `
      <div class="section-title fw-bold mb-2">${title}</div>
      <div class="row">
        <div class="col-12 bg-success text-white p-1">Opening: ${opening.toLocaleString()}</div>
        <div class="col-12 bg-info text-white p-1">In: ${valueIn.toLocaleString()}</div>
        <div class="col-12 bg-primary text-white p-1">Out: ${valueOut.toLocaleString()}</div>
        <div class="col-12 bg-warning text-dark p-1">Closing: ${closing.toLocaleString()}</div>
      </div>
    `;
  }

  function updateCharts(label, quantityData, valueData) {
    const qtyCtx = document.getElementById('quantityChart').getContext('2d');
    const valCtx = document.getElementById('valueChart').getContext('2d');

    // Destroy old charts
    if (quantityChartInstance) quantityChartInstance.destroy();
    if (valueChartInstance) valueChartInstance.destroy();

    // Create quantity chart
    quantityChartInstance = new Chart(qtyCtx, {
      type: 'bar',
      data: {
        labels: ['Opening', 'In', 'Out', 'Closing'],
        datasets: [{
          label: label,
          data: quantityData,
          backgroundColor: ['#0d6efd', '#198754', '#dc3545', '#6f42c1']
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: true } },
        scales: {
          y: {
            beginAtZero: true,
            title: { display: true, text: 'Quantity' }
          }
        }
      }
    });

    // Create value chart
    valueChartInstance = new Chart(valCtx, {
      type: 'bar',
      data: {
        labels: ['Opening', 'In', 'Out', 'Closing'],
        datasets: [{
          label: label,
          data: valueData,
          backgroundColor: ['#0d6efd', '#198754', '#dc3545', '#6f42c1']
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: true } },
        scales: {
          y: {
            beginAtZero: true,
            title: { display: true, text: 'Value (KES)' }
          }
        }
      }
    });
  }

  function updateUI() {
    const selectedItem = document.getElementById('itemSelect').value || 'itm001';
    const data = movementMockData[selectedItem];

    // Update grid
    const movementContainer = document.getElementById('movementContainer');
    movementContainer.innerHTML =
      createMovementSection("Quantity Movement", ...data.quantity) +
      `<hr/>` +
      createMovementSection("Value Movement", ...data.value);

    // Update charts
    updateCharts(data.label, data.quantity, data.value);
  }

  // Initial load
  window.addEventListener('DOMContentLoaded', updateUI);
  document.getElementById('refreshBtn').addEventListener('click', updateUI);
</script>
@endsection