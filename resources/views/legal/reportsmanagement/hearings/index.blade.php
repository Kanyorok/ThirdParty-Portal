@extends('layouts.app')
@section('title', 'hearings')
@section('content')
<div class="container mt-5">

  <?php
    // Sample hearing data (not similar to previous cases)
    $hearings = [
      ['id' => 1, 'caseTitle' => 'Ngugi vs Njeri', 'hearingDate' => '2025-05-15', 'location' => 'High Court - Nairobi', 'notes' => 'Land dispute'],
      ['id' => 2, 'caseTitle' => 'Republic vs Kamau', 'hearingDate' => '2025-05-20', 'location' => 'Kisumu Law Courts', 'notes' => 'Murder trial'],
      ['id' => 3, 'caseTitle' => 'Mumo Traders vs County Gov.', 'hearingDate' => '2025-05-22', 'location' => 'Machakos Court', 'notes' => 'Licensing appeal'],
      ['id' => 4, 'caseTitle' => 'Ali vs Umoja Bank', 'hearingDate' => '2025-05-25', 'location' => 'Commercial Court - Mombasa', 'notes' => 'Loan default case'],
      ['id' => 5, 'caseTitle' => 'Chebet vs Kibet', 'hearingDate' => '2025-05-28', 'location' => 'Family Court - Eldoret', 'notes' => 'Divorce petition'],
      ['id' => 6, 'caseTitle' => 'Transport Union vs KRA', 'hearingDate' => '2025-06-01', 'location' => 'Industrial Court - Nairobi', 'notes' => 'Strike legality hearing'],
      ['id' => 7, 'caseTitle' => 'Karimi vs Muturi', 'hearingDate' => '2025-06-03', 'location' => 'Kerugoya Law Courts', 'notes' => 'Boundary issue'],
    ];

    $filterDate = $_GET['filter_date'] ?? '';
    $filtered = array_filter($hearings, fn($h) => !$filterDate || $h['hearingDate'] === $filterDate);

    $viewId = $_GET['view'] ?? null;
    $viewHearing = null;
    if ($viewId) {
      foreach ($hearings as $hearing) {
        if ($hearing['id'] == $viewId) {
          $viewHearing = $hearing;
          break;
        }
      }
    }
  ?>

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
      <h3>📅 Upcoming Hearings Calendar</h3>
      <p class="text-muted mb-0">Court hearing schedule with filter and detail view.</p>
    </div>
    <button onclick="window.print()" class="btn btn-outline-secondary">🖨️ Print Report</button>
  </div>

  <?php if ($viewHearing): ?>
    <!-- Individual Hearing View -->
    <div class="card shadow-sm">
      <div class="card-body">
        <h4 class="card-title text-dark"><?= htmlspecialchars($viewHearing['caseTitle']) ?></h4>
        <p><strong>Date:</strong> <?= $viewHearing['hearingDate'] ?></p>
        <p><strong>Location:</strong> <?= htmlspecialchars($viewHearing['location']) ?></p>
        <p><strong>Notes:</strong> <?= htmlspecialchars($viewHearing['notes']) ?></p>
        <a href="hearings.php<?= $filterDate ? '?filter_date=' . urlencode($filterDate) : '' ?>" class="btn btn-outline-primary mt-3 no-print">← Back to List</a>
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
          <a href="hearings.php" class="btn btn-outline-secondary w-100">Reset</a>
        </div>
      </div>
    </form>

    <!-- Table View -->
    <div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Case Title</th>
            <th>Hearing Date</th>
            <th>Location</th>
            <th>Notes</th>
            <th class="no-print">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($filtered)): ?>
            <tr>
              <td colspan="6" class="text-center text-muted">No hearings scheduled for the selected date.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($filtered as $hearing): ?>
              <tr>
                <td><?= $hearing['id'] ?></td>
                <td><?= htmlspecialchars($hearing['caseTitle']) ?></td>
                <td><?= $hearing['hearingDate'] ?></td>
                <td><?= htmlspecialchars($hearing['location']) ?></td>
                <td><?= htmlspecialchars($hearing['notes']) ?></td>
                <td class="no-print">
                  <a href="?view=<?= $hearing['id'] ?>&filter_date=<?= urlencode($filterDate) ?>" class="btn btn-sm btn-outline-info">View</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <footer class="text-center text-muted mt-5 no-print">
    <hr>
    <p>&copy; <?= date('Y') ?> Hearing Management System</p>
  </footer>
</div>
@endsection