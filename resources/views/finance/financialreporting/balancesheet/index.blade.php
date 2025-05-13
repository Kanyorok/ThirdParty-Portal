@extends('layouts.app')
@section('title', 'Balance Sheet')
@section('content')

<div class="container py-5">
  <h2 class="text-center mb-5 text-primary">Balance Sheet Report</h2>

  <style>
    .card-header {
      background-color: #0d6efd;
      color: white;
      font-weight: 600;
      font-size: 1.1rem;
    }
    .table th, .table td {
      padding: 12px;
      vertical-align: middle;
    }
    .metrics-badge {
      padding: 0.3rem 0.7rem;
      font-size: 0.85rem;
      border-radius: 0.25rem;
    }
    .metrics-box {
      margin-top: 30px;
      background: #f8f9fa;
      border-left: 4px solid #0d6efd;
      padding: 20px;
    }
    .summary-box {
      border-radius: 0.5rem;
      padding: 15px 20px;
      color: white;
      margin-bottom: 20px;
    }
    .summary-assets { background-color: #198754; }
    .summary-liabilities { background-color: #dc3545; }
    .summary-equity { background-color: #0dcaf0; }
  </style>

  <?php
  $periods = [
    '2025-03-31' => [
      'period' => 'Jan 1 – Mar 31, 2025',
      'current_assets' => 85000,
      'non_current_assets' => 120000,
      'current_liabilities' => 40000,
      'non_current_liabilities' => 50000,
      'equity' => 115000
    ],
    '2025-06-30' => [
      'period' => 'Apr 1 – Jun 30, 2025',
      'current_assets' => 95000,
      'non_current_assets' => 130000,
      'current_liabilities' => 42000,
      'non_current_liabilities' => 52000,
      'equity' => 131000
    ],
    '2025-09-30' => [
      'period' => 'Jul 1 – Sep 30, 2025',
      'current_assets' => 100000,
      'non_current_assets' => 140000,
      'current_liabilities' => 45000,
      'non_current_liabilities' => 55000,
      'equity' => 140000
    ]
  ];

  krsort($periods);
  $start_date = $_GET['start_date'] ?? '';
  $end_date = $_GET['end_date'] ?? '';
  $filtered_data = null;

  if ($start_date && $end_date) {
    $start_ts = strtotime($start_date);
    $end_ts = strtotime($end_date);
    foreach ($periods as $date => $data) {
      if (strtotime($date) >= $start_ts && strtotime($date) <= $end_ts) {
        $filtered_data = $data;
        break;
      }
    }
  } else {
    $filtered_data = reset($periods);
  }
  ?>

  <!-- Date Filter Form -->
  <form method="get" class="row g-3 align-items-end mb-4 justify-content-center">
    <div class="col-md-4">
      <label for="start_date" class="form-label">Start Date:</label>
      <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $start_date ?>">
    </div>
    <div class="col-md-4">
      <label for="end_date" class="form-label">End Date:</label>
      <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $end_date ?>">
    </div>
    <div class="col-md-2 d-flex gap-2">
      <button type="submit" class="btn btn-primary">View Report</button>
      <button type="button" class="btn btn-outline-secondary w-100" onclick="printReport()">
        Print
    </button>
    </div>
  </form>

  <!-- Data Display -->
  <?php if ($filtered_data):
    $total_assets = $filtered_data['current_assets'] + $filtered_data['non_current_assets'];
    $total_liabilities = $filtered_data['current_liabilities'] + $filtered_data['non_current_liabilities'];
    $current_ratio = $filtered_data['current_liabilities'] != 0 ? $filtered_data['current_assets'] / $filtered_data['current_liabilities'] : 0;
    $equity_ratio = $total_assets != 0 ? $filtered_data['equity'] / $total_assets : 0;
  ?>

    <div class="row">
      <div class="col-md-4">
        <div class="summary-box summary-assets">
          <h5>Total Assets</h5>
          <p class="mb-0 fw-bold fs-5">$<?= number_format($total_assets, 2) ?></p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="summary-box summary-liabilities">
          <h5>Total Liabilities</h5>
          <p class="mb-0 fw-bold fs-5">$<?= number_format($total_liabilities, 2) ?></p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="summary-box summary-equity">
          <h5>Total Equity</h5>
          <p class="mb-0 fw-bold fs-5">$<?= number_format($filtered_data['equity'], 2) ?></p>
        </div>
      </div>
    </div>

    <div class="card shadow-sm mt-4">
      <div class="card-header">
        Balance Sheet as of <?= $filtered_data['period'] ?>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- Assets -->
          <div class="col-md-6">
            <h5 class="text-muted mb-3">Assets</h5>
            <table class="table table-bordered table-hover">
              <tr>
                <th>Current Assets</th>
                <td>$<?= number_format($filtered_data['current_assets'], 2) ?></td>
              </tr>
              <tr>
                <th>Non-Current Assets</th>
                <td>$<?= number_format($filtered_data['non_current_assets'], 2) ?></td>
              </tr>
              <tr class="table-light">
                <th>Total Assets</th>
                <td><strong>$<?= number_format($total_assets, 2) ?></strong></td>
              </tr>
            </table>
          </div>

          <!-- Liabilities and Equity -->
          <div class="col-md-6">
            <h5 class="text-muted mb-3">Liabilities & Equity</h5>
            <table class="table table-bordered table-hover">
              <tr>
                <th>Current Liabilities</th>
                <td>$<?= number_format($filtered_data['current_liabilities'], 2) ?></td>
              </tr>
              <tr>
                <th>Non-Current Liabilities</th>
                <td>$<?= number_format($filtered_data['non_current_liabilities'], 2) ?></td>
              </tr>
              <tr>
                <th>Equity</th>
                <td>$<?= number_format($filtered_data['equity'], 2) ?></td>
              </tr>
              <tr class="table-light">
                <th>Total Liabilities + Equity</th>
                <td><strong>$<?= number_format($total_liabilities + $filtered_data['equity'], 2) ?></strong></td>
              </tr>
            </table>
          </div>
        </div>

        <!-- Metrics -->
        <div class="metrics-box mt-4">
          <h6>Key Financial Ratios</h6>
          <p>
            <strong>Current Ratio:</strong> <?= round($current_ratio, 2) ?>
            <span class="metrics-badge bg-<?= $current_ratio >= 1.5 ? 'success' : ($current_ratio >= 1 ? 'warning' : 'danger') ?>">
              <?= $current_ratio >= 1.5 ? 'Healthy' : ($current_ratio >= 1 ? 'Adequate' : 'Weak') ?>
            </span>
          </p>
          <p>
            <strong>Equity Ratio:</strong> <?= round($equity_ratio * 100, 2) ?>%
            <span class="metrics-badge bg-<?= $equity_ratio >= 0.5 ? 'info' : 'secondary' ?>">
              <?= $equity_ratio >= 0.5 ? 'Strong Equity' : 'Highly Leveraged' ?>
            </span>
          </p>
        </div>
      </div>
    </div>

  <?php else: ?>
    <div class="alert alert-warning text-center mt-4">No balance sheet data available for the selected date range.</div>
  <?php endif; ?>
</div>
@endsection