@extends('layouts.app')
@section('title', 'Cash FLow Statement')
@section('content')

<div class="container py-5">
  <h2 class="text-center mb-5 text-dark">Cash Flow Statement Report</h2>

  <style>
    .card-header {
      font-size: 1.2rem;
      font-weight: 500;
      border-bottom: 2px solid #007bff;
    }
    .table th, .table td {
      padding: 12px;
    }
    .table-hover tbody tr:hover {
      background-color: #f1f1f1;
    }
    .alert-warning {
      font-size: 1.1rem;
      font-weight: 600;
    }
    .display-4 {
      font-size: 2.5rem;
    }
    .metrics-box {
      background: #f8f9fa;
      border-left: 5px solid #0d6efd;
      padding: 15px;
      margin-top: 20px;
    }
  </style>

  <?php
  // Enhanced cash flow periods with breakdowns
  $periods = [
    '2025-03-31' => [
      'period' => 'Jan 1 – Mar 31, 2025',
      'opening_balance' => 100000,
      'operating' => [
        'cash_receipts' => 80000,
        'cash_paid_suppliers' => -20000,
        'cash_paid_expenses' => -10000
      ],
      'investing' => [
        'purchase_equipment' => -15000
      ],
      'financing' => [
        'loan_proceeds' => 25000,
        'repayments' => -5000
      ]
    ],
    '2025-06-30' => [
      'period' => 'Apr 1 – Jun 30, 2025',
      'opening_balance' => 120000,
      'operating' => [
        'cash_receipts' => 90000,
        'cash_paid_suppliers' => -20000,
        'cash_paid_expenses' => -10000
      ],
      'investing' => [
        'purchase_equipment' => -18000
      ],
      'financing' => [
        'loan_proceeds' => 30000,
        'repayments' => -5000
      ]
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
  <div class="row justify-content-center mb-4">
    <form method="get" class="col-md-8">
      <div class="row g-3 align-items-end">
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
      </div>
    </form>
  </div>

  <?php if ($filtered_data):
    $op = $filtered_data['operating'];
    $inv = $filtered_data['investing'];
    $fin = $filtered_data['financing'];

    $operating_total = array_sum($op);
    $investing_total = array_sum($inv);
    $financing_total = array_sum($fin);

    $net_cash_flow = $operating_total + $investing_total + $financing_total;
    $closing_balance = $filtered_data['opening_balance'] + $net_cash_flow;

    $cash_conversion_ratio = $operating_total / (
      abs($operating_total) + abs($investing_total) + abs($financing_total)
    );
  ?>
    <div class="card shadow-lg mb-4">
      <div class="card-header bg-info text-white">
        <h5 class="mb-0">Cash Flow Statement for <?= $filtered_data['period'] ?></h5>
      </div>
      <div class="card-body">
        <div class="row mb-4">
          <div class="col-md-6">
            <h5 class="text-muted">Operating Activities</h5>
            <ul class="list-unstyled">
              <li><strong>Cash Receipts from Customers:</strong> $<?= number_format($op['cash_receipts'], 2) ?></li>
              <li><strong>Payments to Suppliers:</strong> $<?= number_format($op['cash_paid_suppliers'], 2) ?></li>
              <li><strong>Operating Expenses Paid:</strong> $<?= number_format($op['cash_paid_expenses'], 2) ?></li>
              <li class="mt-2"><strong>Net Cash from Operating:</strong> $<?= number_format($operating_total, 2) ?></li>
            </ul>

            <h5 class="text-muted mt-4">Investing Activities</h5>
            <ul class="list-unstyled">
              <li><strong>Purchase of Equipment:</strong> $<?= number_format($inv['purchase_equipment'], 2) ?></li>
              <li class="mt-2"><strong>Net Investing Cash:</strong> $<?= number_format($investing_total, 2) ?></li>
            </ul>

            <h5 class="text-muted mt-4">Financing Activities</h5>
            <ul class="list-unstyled">
              <li><strong>Loan Proceeds:</strong> $<?= number_format($fin['loan_proceeds'], 2) ?></li>
              <li><strong>Loan Repayments:</strong> $<?= number_format($fin['repayments'], 2) ?></li>
              <li class="mt-2"><strong>Net Financing Cash:</strong> $<?= number_format($financing_total, 2) ?></li>
            </ul>

            <hr>
            <h5><strong>Total Net Cash Flow:</strong> $<?= number_format($net_cash_flow, 2) ?></h5>
          </div>

          <div class="col-md-6">
            <h5 class="text-muted">Cash Position</h5>
            <ul class="list-unstyled">
              <li><strong>Opening Cash Balance:</strong> $<?= number_format($filtered_data['opening_balance'], 2) ?></li>
              <li><strong>Closing Cash Balance:</strong> <span class="text-success fw-bold">$<?= number_format($closing_balance, 2) ?></span></li>
            </ul>

            <div class="metrics-box mt-4">
              <h6>Cash Management Metrics</h6>
              <p><strong>Cash Conversion Ratio:</strong> <?= round($cash_conversion_ratio * 100, 2) ?>%</p>
              <p><strong>Analysis:</strong> 
                <?php if ($cash_conversion_ratio >= 0.6): ?>
                  Strong cash generation from core activities.
                <?php elseif ($cash_conversion_ratio >= 0.4): ?>
                  Moderate efficiency; monitor operating performance.
                <?php else: ?>
                  Low cash efficiency; consider reviewing operations.
                <?php endif; ?>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="alert alert-warning text-center">No data available for the selected date range.</div>
  <?php endif; ?>
</div>
@endsection