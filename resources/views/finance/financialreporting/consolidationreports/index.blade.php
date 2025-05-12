@extends('layouts.app')
@section('title', 'Consolidation Reports')
@section('content')

<div class="container py-5">
  <h2 class="text-center text-primary mb-4">Consolidation Report</h2>

  <style>
    .summary-card {
      background: #f8f9fa;
      border-left: 5px solid #0d6efd;
      padding: 15px 20px;
      margin-bottom: 20px;
      border-radius: 0.5rem;
      box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
    }
    .summary-card h5 {
      margin-bottom: 10px;
      font-weight: bold;
    }
    .accordion-button:not(.collapsed) {
      background-color: #e7f1ff;
      color: #0d6efd;
    }
    .accordion-body {
      background: #fff;
    }
    .data-label {
      font-weight: 500;
    }
  </style>

  <?php
  // Consolidation data with branches
  $consolidated_periods = [
    '2025-03-31' => [
      'period' => 'Q1 2025',
      'branches' => [
        'Nairobi' => [
          'revenue' => 120000,
          'assets' => 200000,
          'liabilities' => 80000,
          'minority_interest' => 10000,
          'net_income' => 30000,
        ],
        'Mombasa' => [
          'revenue' => 100000,
          'assets' => 150000,
          'liabilities' => 50000,
          'minority_interest' => 6000,
          'net_income' => 27000,
        ],
      ]
    ],
    '2025-06-30' => [
      'period' => 'Q2 2025',
      'branches' => [
        'Nairobi' => [
          'revenue' => 130000,
          'assets' => 210000,
          'liabilities' => 90000,
          'minority_interest' => 11000,
          'net_income' => 32000,
        ],
        'Kisumu' => [
          'revenue' => 90000,
          'assets' => 120000,
          'liabilities' => 40000,
          'minority_interest' => 5000,
          'net_income' => 22000,
        ],
      ]
    ]
  ];

  $branches = ['All'];
  foreach ($consolidated_periods as $p) {
    foreach (array_keys($p['branches']) as $b) {
      if (!in_array($b, $branches)) $branches[] = $b;
    }
  }

  $start_date = $_GET['start_date'] ?? '';
  $end_date = $_GET['end_date'] ?? '';
  $selected_branch = $_GET['branch'] ?? 'All';

  $filtered_data = [];

  if ($start_date && $end_date) {
    $start_ts = strtotime($start_date);
    $end_ts = strtotime($end_date);
    foreach ($consolidated_periods as $date => $data) {
      $ts = strtotime($date);
      if ($ts >= $start_ts && $ts <= $end_ts) {
        $filtered_data[$date] = $data;
      }
    }
  } else {
    $filtered_data = [key($consolidated_periods) => current($consolidated_periods)];
  }
  ?>

  <!-- Filter Form -->
  <form method="get" class="row g-3 align-items-end mb-4">
    <div class="col-md-3">
      <label for="start_date" class="form-label">Start Date:</label>
      <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $start_date ?>">
    </div>
    <div class="col-md-3">
      <label for="end_date" class="form-label">End Date:</label>
      <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $end_date ?>">
    </div>
    <div class="col-md-3">
      <label for="branch" class="form-label">Branch:</label>
      <select class="form-select" id="branch" name="branch">
        <?php foreach ($branches as $b): ?>
          <option value="<?= $b ?>" <?= $selected_branch === $b ? 'selected' : '' ?>><?= $b ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2 d-flex gap-2">
      <button type="submit" class="btn btn-primary">View Report</button>
      <button type="button" class="btn btn-outline-secondary w-100" onclick="printReport()">
        Print
    </button>
    </div>
  </form>

  <?php if (!empty($filtered_data)): ?>
    <div class="accordion mt-4" id="branchAccordion">
      <?php $i = 0; foreach ($filtered_data as $period): $i++; ?>
        <?php foreach ($period['branches'] as $branch => $info): ?>
          <?php if ($selected_branch === 'All' || $selected_branch === $branch): ?>
            <div class="accordion-item mb-3">
              <h2 class="accordion-header" id="heading<?= $i . $branch ?>">
                <button class="accordion-button <?= $i === 1 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $i . $branch ?>" aria-expanded="<?= $i === 1 ? 'true' : 'false' ?>" aria-controls="collapse<?= $i . $branch ?>">
                  <?= $period['period'] ?> – <?= $branch ?> Branch
                </button>
              </h2>
              <div id="collapse<?= $i . $branch ?>" class="accordion-collapse collapse <?= $i === 1 ? 'show' : '' ?>" aria-labelledby="heading<?= $i . $branch ?>" data-bs-parent="#branchAccordion">
                <div class="accordion-body">
                  <table class="table table-bordered">
                    <tr><th>Revenue</th><td>$<?= number_format($info['revenue'], 2) ?></td></tr>
                    <tr><th>Total Assets</th><td>$<?= number_format($info['assets'], 2) ?></td></tr>
                    <tr><th>Total Liabilities</th><td>$<?= number_format($info['liabilities'], 2) ?></td></tr>
                    <tr><th>Minority Interest</th><td>$<?= number_format($info['minority_interest'], 2) ?></td></tr>
                    <tr><th>Net Income</th><td><strong>$<?= number_format($info['net_income'], 2) ?></strong></td></tr>
                  </table>
                </div>
              </div>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="alert alert-warning">No data available for the selected filters.</div>
  <?php endif; ?>
</div>
@endsection