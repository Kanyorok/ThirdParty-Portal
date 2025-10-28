@extends('layouts.app')
@section('title', 'Stock Movement Dashboard')
@section('content')
<div class="container-fluid mt-4">
  <div class="mb-4">
    <h3 class="fw-bold">📦 Stock Movement Dashboard</h3>
    <p class="text-muted">Track quantities and values by filters & date range</p>
  </div>

  <!-- Filters -->
    <form method="GET" id="filterForm" class="row g-3 mb-3">
    <div class="col-md-2">
        <select name="branch" class="form-select" id="branchSelect">
        <option value="">All Branches</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->Id }}" {{ request('branch') == $branch->Id ? 'selected' : '' }}>
                    {{ $branch->Description }}
                </option>
            @endforeach
      </select>
    </div>
    <div class="col-md-2">
        <select name="store" class="form-select" id="storeSelect">
        <option value="">All Stores</option>
            @foreach($stores as $store)
                <option value="{{ $store->Id }}" {{ request('store') == $store->Id ? 'selected' : '' }}>
                    {{ $store->Description }}
                </option>
            @endforeach
      </select>
    </div>
    <div class="col-md-2">
        <select name="item" class="form-select" id="itemSelect">
        <option value="">All Items</option>
            @foreach($items as $item)
                <option value="{{ $item->Id }}" {{ request('item') == $item->Id ? 'selected' : '' }}>
                    {{ $item->Description }}
                </option>
            @endforeach
      </select>
    </div>
    <div class="col-md-2">
        <input type="date" name="from_date" class="form-control" id="fromDate"
               value="{{ request('from_date', now()->format('Y-m-01')) }}">
    </div>
    <div class="col-md-2">
        <input type="date" name="to_date" class="form-control" id="toDate"
               value="{{ request('to_date', now()->format('Y-m-d')) }}">
    </div>
        <div class="col-md-2 text-end">
            <button type="submit" class="btn btn-primary">🔄 Refresh</button>
        </div>
    </form>

  <div class="row">
    <!-- Stock Movement Grid -->
      <div class="col-md-3">
          <div class="card shadow rounded-4 mb-4">
        <div class="card-header bg-light fw-bold">📋 Stock Movement Grid</div>
              <div class="card-body p-2" id="movementContainer">
                  <!-- Dynamic grid content populated by JS -->
              </div>
      </div>
    </div>

    <!-- Stock Movement Charts -->
      <div class="col-md-9">
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
    const movementData = @json($movementData);

    let quantityChartInstance, valueChartInstance;

    function createMovementSection(title, opening, valueIn, valueOut, closing) {
        return `
            <div class="section-title fw-bold mb-2">${title}</div>
            <div class="row mb-2">
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

        if (quantityChartInstance) quantityChartInstance.destroy();
        if (valueChartInstance) valueChartInstance.destroy();

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
                plugins: {legend: {display: true}},
                scales: {y: {beginAtZero: true, title: {display: true, text: 'Quantity'}}}
            }
        });

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
                plugins: {legend: {display: true}},
                scales: {y: {beginAtZero: true, title: {display: true, text: 'Value (KES)'}}}
            }
        });
    }

    function updateUI() {
        const selectedItem = document.getElementById('itemSelect').value;
        const data = selectedItem && movementData[selectedItem]
            ? movementData[selectedItem]
            : Object.values(movementData)[0] ?? {
            label: 'No Data',
            quantity: [0, 0, 0, 0],
            value: [0, 0, 0, 0]
        };

        const movementContainer = document.getElementById('movementContainer');
        movementContainer.innerHTML =
            createMovementSection("Quantity Movement", ...data.quantity) +
            `<hr/>` +
            createMovementSection("Value Movement", ...data.value);

        updateCharts(data.label, data.quantity, data.value);
    }

    window.addEventListener('DOMContentLoaded', updateUI);
    document.getElementById('itemSelect').addEventListener('change', updateUI);
</script>
@endsection
