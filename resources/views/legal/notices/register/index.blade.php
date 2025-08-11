@extends('layouts.app')
@section('title','Notice Register')
@section('content')

<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-primary fw-bold">Notice Register</h2>
    <div>
      <!-- ✅ This button is now clearly for issuing only -->
      <a href="{{ route('register.create') }}" class="btn btn-outline-primary">➕ Issue Notice</a>
      
      <!-- Filter buttons -->
      <a href="?filter=issued" class="btn btn-outline-secondary ms-2">👁️ View Issued Notices</a>
      <a href="?filter=received" class="btn btn-outline-secondary ms-2">👁️ View Received Notices</a>
    </div>
  </div>

  <?php
  // Static notices
  $notices = [
    ['type' => 'Claim', 'direction' => 'Received', 'party' => 'ABC Corp', 'date' => '2025-04-20', 'ref' => 'NCLM-001'],
    ['type' => 'Default', 'direction' => 'Issued', 'party' => 'XYZ Ltd', 'date' => '2025-03-18', 'ref' => 'NDEF-005'],
    ['type' => 'Claim', 'direction' => 'Issued', 'party' => 'Delta Inc.', 'date' => '2025-05-02', 'ref' => 'NCLM-006'],
    ['type' => 'Warning', 'direction' => 'Received', 'party' => 'Legal Authority', 'date' => '2025-01-10', 'ref' => 'NWAR-010']
  ];

  // Only display notices after filter is applied
  if (isset($_GET['filter'])) {
    $filter = ucfirst(strtolower($_GET['filter']));
    $found = false;

    foreach ($notices as $notice) {
      if ($notice['direction'] === $filter) {
        $found = true;
        echo "
        <div class='card notice-card mb-3 shadow-sm'>
          <div class='card-body'>
            <h5 class='card-title text-dark mb-1'>{$notice['type']} Notice</h5>
            <p class='mb-1'><strong>Direction:</strong> {$notice['direction']}</p>
            <p class='mb-1'><strong>Party:</strong> {$notice['party']}</p>
            <p class='mb-1'><strong>Date:</strong> {$notice['date']}</p>
            <p class='mb-1'><strong>Reference ID:</strong> {$notice['ref']}</p>
            <a href='view.php?ref={$notice['ref']}' class='btn btn-sm btn-outline-secondary mt-2'>View Details</a>
          </div>
        </div>";
      }
    }

    if (!$found) {
      echo "<p class='text-muted'>No {$filter} notices found.</p>";
    }
  }
  ?>

  <footer class="text-center text-muted mt-5">
    <hr>
    <p>&copy; <?= date("Y"); ?> Legal Office. All rights reserved.</p>
  </footer>
</div>


@endsection