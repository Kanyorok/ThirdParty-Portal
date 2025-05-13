@extends('layouts.app')
@section('title', 'legalexpenses')
@section('content')
<div class="container mt-5">

  <?php
    // Updated expense data (not similar to previous ones)
    $expenses = [
      ['id' => 1, 'caseOrDept' => 'Land Tribunal', 'description' => 'Surveyor fees', 'amount' => 310.00, 'date' => '2025-05-03'],
      ['id' => 2, 'caseOrDept' => 'Tax Office', 'description' => 'Audit consultation', 'amount' => 195.50, 'date' => '2025-05-06'],
      ['id' => 3, 'caseOrDept' => 'Legal Unit', 'description' => 'Document notarization', 'amount' => 85.00, 'date' => '2025-05-09'],
      ['id' => 4, 'caseOrDept' => 'Corporate Case D', 'description' => 'Interpreter services', 'amount' => 160.75, 'date' => '2025-05-11'],
      ['id' => 5, 'caseOrDept' => 'Admin Division', 'description' => 'Statutory filing fees', 'amount' => 100.25, 'date' => '2025-05-14'],
      ['id' => 6, 'caseOrDept' => 'Transport Case T', 'description' => 'Courier charges', 'amount' => 45.80, 'date' => '2025-05-15'],
      ['id' => 7, 'caseOrDept' => 'Planning Dept.', 'description' => 'Legal brief preparation', 'amount' => 230.90, 'date' => '2025-05-17'],
    ];

    // Handle filtering and view
    $filterDate = $_GET['filter_date'] ?? '';
    $filtered = array_filter($expenses, fn($e) => !$filterDate || $e['date'] === $filterDate);
    $viewId = $_GET['view'] ?? null;

    $viewExpense = null;
    if ($viewId) {
      foreach ($expenses as $expense) {
        if ($expense['id'] == $viewId) {
          $viewExpense = $expense;
          break;
        }
      }
    }

    $totalAmount = array_reduce($filtered, fn($sum, $e) => $sum + $e['amount'], 0);
  ?>

  <div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
      <h3>📊 Legal Expenses Report</h3>
      <p class="text-muted mb-0">Expense tracking by department or case</p>
    </div>
    <button onclick="window.print()" class="btn btn-outline-secondary">🖨️ Print Report</button>
  </div>

  <?php if ($viewExpense): ?>
    <!-- Single Expense View -->
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title"><?= htmlspecialchars($viewExpense['caseOrDept']) ?></h5>
        <p><strong>Description:</strong> <?= htmlspecialchars($viewExpense['description']) ?></p>
        <p><strong>Amount:</strong> $<?= number_format($viewExpense['amount'], 2) ?></p>
        <p><strong>Date:</strong> <?= $viewExpense['date'] ?></p>
        <a href="expenses.php<?= $filterDate ? '?filter_date=' . urlencode($filterDate) : '' ?>" class="btn btn-outline-primary mt-3 no-print">← Back to List</a>
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
          <a href="expenses.php" class="btn btn-outline-secondary w-100">Reset</a>
        </div>
      </div>
    </form>

    <!-- Table Display -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Case / Department</th>
            <th>Description</th>
            <th>Amount</th>
            <th>Date</th>
            <th class="no-print">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($filtered)): ?>
            <tr>
              <td colspan="6" class="text-center text-muted">No expenses found for the selected date.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($filtered as $expense): ?>
              <tr>
                <td><?= $expense['id'] ?></td>
                <td><?= htmlspecialchars($expense['caseOrDept']) ?></td>
                <td><?= htmlspecialchars($expense['description']) ?></td>
                <td>$<?= number_format($expense['amount'], 2) ?></td>
                <td><?= $expense['date'] ?></td>
                <td class="no-print">
                  <a href="?view=<?= $expense['id'] ?>&filter_date=<?= urlencode($filterDate) ?>" class="btn btn-sm btn-outline-info">View</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
        <tfoot>
          <tr class="table-light fw-bold">
            <td colspan="3" class="text-end">Total</td>
            <td colspan="3">$<?= number_format($totalAmount, 2) ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  <?php endif; ?>

  <footer class="text-center text-muted mt-5 no-print">
    <hr>
    <p>&copy; <?= date('Y') ?> Legal Finance System</p>
  </footer>
</div>
@endsection