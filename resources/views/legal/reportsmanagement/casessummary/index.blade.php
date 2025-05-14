@extends('layouts.app')
@section('title', 'casessummary')
@section('content')
<div class="container my-5">

  <?php
    // Sample data with date field
    $cases = [
      ['id' => 1, 'title' => 'Case A', 'description' => 'Description for Case A', 'status' => 'Open', 'date' => '2025-05-10'],
      ['id' => 2, 'title' => 'Case B', 'description' => 'Description for Case B', 'status' => 'Closed', 'date' => '2025-04-22'],
      ['id' => 3, 'title' => 'Case C', 'description' => 'Description for Case C', 'status' => 'Open', 'date' => '2025-05-11'],
      ['id' => 4, 'title' => 'Case D', 'description' => 'Description for Case D', 'status' => 'Closed', 'date' => '2025-03-15'],
      ['id' => 5, 'title' => 'Case E', 'description' => 'Description for Case E', 'status' => 'Open', 'date' => '2025-05-12'],
    ];

    // Filter by date
    $filterDate = $_GET['filter_date'] ?? '';
    $filteredCases = array_filter($cases, fn($case) =>
      !$filterDate || $case['date'] === $filterDate
    );

    // Detect view request
    $viewId = $_GET['view'] ?? null;
    $viewCase = null;

    if ($viewId) {
      foreach ($cases as $case) {
        if ($case['id'] == $viewId) {
          $viewCase = $case;
          break;
        }
      }
    }

    $open_cases = array_filter($filteredCases, fn($c) => $c['status'] === 'Open');
    $closed_cases = array_filter($filteredCases, fn($c) => $c['status'] === 'Closed');
  ?>

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
      <h2 class="text-primary fw-bold mb-0">📋 Case Summary Report</h2>
      <p class="text-muted">Overview of open and closed legal cases. Filter by date and view details.</p>
    </div>
    <button onclick="window.print()" class="btn btn-outline-secondary">🖨️ Print</button>
  </div>

  <?php if ($viewCase): ?>
    <!-- Individual Case View -->
    <div class="card shadow-sm">
      <div class="card-body">
        <h4 class="card-title text-dark"><?= htmlspecialchars($viewCase['title']) ?></h4>
        <p><strong>Description:</strong> <?= htmlspecialchars($viewCase['description']) ?></p>
        <p><strong>Status:</strong>
          <span class="badge <?= $viewCase['status'] === 'Open' ? 'bg-success' : 'bg-secondary' ?>">
            <?= $viewCase['status'] ?>
          </span>
        </p>
        <p><strong>Date:</strong> <?= $viewCase['date'] ?></p>
        <a href="index.php<?= $filterDate ? '?filter_date=' . urlencode($filterDate) : '' ?>" class="btn btn-outline-primary mt-3 no-print">← Back to List</a>
      </div>
    </div>

  <?php else: ?>
    <!-- Filter Form -->
    <form method="get" class="mb-4 no-print">
      <div class="row g-2">
        <div class="col-md-4">
          <input type="date" name="filter_date" class="form-control" value="<?= htmlspecialchars($filterDate) ?>">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
        </div>
        <div class="col-md-2">
          <a href="index.php" class="btn btn-outline-secondary w-100">Reset</a>
        </div>
      </div>
    </form>

    <!-- Cases Table -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Case Title</th>
            <th>Description</th>
            <th>Status</th>
            <th>Date</th>
            <th class="no-print">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($filteredCases)): ?>
            <tr>
              <td colspan="6" class="text-center text-muted">No cases found for the selected date.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($filteredCases as $case): ?>
              <tr>
                <td><?= $case['id'] ?></td>
                <td><?= htmlspecialchars($case['title']) ?></td>
                <td><?= htmlspecialchars($case['description']) ?></td>
                <td>
                  <span class="badge <?= $case['status'] === 'Open' ? 'bg-success' : 'bg-secondary' ?>">
                    <?= $case['status'] ?>
                  </span>
                </td>
                <td><?= $case['date'] ?></td>
                <td class="no-print">
                  <a href="?view=<?= $case['id'] ?>&filter_date=<?= urlencode($filterDate) ?>" class="btn btn-sm btn-outline-info">View</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
        <tfoot class="table-light">
          <tr>
            <td colspan="4" class="text-end fw-bold">Total Open Cases</td>
            <td colspan="2"><?= count($open_cases) ?></td>
          </tr>
          <tr>
            <td colspan="4" class="text-end fw-bold">Total Closed Cases</td>
            <td colspan="2"><?= count($closed_cases) ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  <?php endif; ?>

  <footer class="text-center text-muted mt-5 no-print">
    <hr>
    <p>&copy; <?= date("Y") ?> Case Reporting System</p>
  </footer>
</div>
@endsection