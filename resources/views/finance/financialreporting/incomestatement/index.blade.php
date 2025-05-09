@extends('layouts.app')
@section('title', 'Income Statements')
@section('content')

<div class="container py-5">
  <h2 class="text-center mb-4">Income Statement Report</h2>

  <?php
  // Define the income statement periods
  $periods = [
    '2025-03-31' => [
      'period' => 'Jan 1 – Mar 31, 2025',
      'revenue' => 150000,
      'cogs' => 60000,
      'opex' => 30000,
      'other' => 5000
    ],
    '2025-06-30' => [
      'period' => 'Apr 1 – Jun 30, 2025',
      'revenue' => 160000,
      'cogs' => 65000,
      'opex' => 32000,
      'other' => 6000
    ],
    '2025-09-30' => [
      'period' => 'Jul 1 – Sep 30, 2025',
      'revenue' => 170000,
      'cogs' => 70000,
      'opex' => 35000,
      'other' => 7000
    ]
  ];

  krsort($periods); // Sort to get latest period first
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
    $filtered_data = reset($periods); // Display most recent period by default
  }
  ?>

  <!-- Date Filter Form -->
  <form method="get" class="row g-3 align-items-end mb-4">
    <div class="col-md-3">
      <label for="start_date" class="form-label">Start Date:</label>
      <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $start_date ?>">
    </div>
    <div class="col-md-3">
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

  <!-- Report Table -->
  <?php if ($filtered_data): ?>
    <div class="card mb-4">
      <div class="card-header">
        Income Statement for <?= $filtered_data['period'] ?>
      </div>
      <div class="card-body p-0">
        <table class="table mb-0">
          <thead class="table-light">
            <tr><th>Category</th><th class="text-end">Amount (USD)</th></tr>
          </thead>
          <tbody>
            <tr><td>Revenue</td><td class="text-end">$<?= number_format($filtered_data['revenue'], 2) ?></td></tr>
            <tr><td>COGS</td><td class="text-end">$<?= number_format($filtered_data['cogs'], 2) ?></td></tr>
            <tr><td><strong>Gross Profit</strong></td><td class="text-end"><strong>$<?= number_format($filtered_data['revenue'] - $filtered_data['cogs'], 2) ?></strong></td></tr>
            <tr><td>Operating Expenses</td><td class="text-end">$<?= number_format($filtered_data['opex'], 2) ?></td></tr>
            <tr><td><strong>Operating Income</strong></td><td class="text-end"><strong>$<?= number_format($filtered_data['revenue'] - $filtered_data['cogs'] - $filtered_data['opex'], 2) ?></strong></td></tr>
            <tr><td>Other Income</td><td class="text-end">$<?= number_format($filtered_data['other'], 2) ?></td></tr>
            <tr><td><strong>Net Income</strong></td><td class="text-end"><strong>$<?= number_format($filtered_data['revenue'] - $filtered_data['cogs'] - $filtered_data['opex'] + $filtered_data['other'], 2) ?></strong></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  <?php else: ?>
    <div class="alert alert-warning">No data available for the selected date range.</div>
  <?php endif; ?>
  

</div>

@endsection
