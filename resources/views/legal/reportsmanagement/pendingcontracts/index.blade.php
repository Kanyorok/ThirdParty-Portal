@extends('layouts.app')
@section('title','Pending Contracts')
@section('content')

<div class="container my-5">

  <div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
      <h2 class="text-primary fw-bold mb-0">Pending Contracts</h2>
      <p class="text-muted">Manage contracts that are awaiting approval or action.</p>
    </div>
    <button onclick="window.print()" class="btn btn-outline-secondary">🖨️ Print</button>
  </div>

  <!-- Date Filter -->
  <form method="get" class="mb-4">
    <div class="row justify-content-center">
      <div class="col-md-4">
        <input type="date" name="filter_date" class="form-control" value="<?php echo $_GET['filter_date'] ?? ''; ?>" placeholder="Filter by date">
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
      </div>
    </div>
  </form>

  <?php
    // Sample pending contracts data
    $contracts = [
      [
        'contract_name' => 'Software License Agreement',
        'client' => 'ABC Technologies',
        'start_date' => '2025-04-01',
        'end_date' => '2026-04-01',
        'status' => 'Pending Approval',
        'ref' => 'PC-001',
        'description' => 'A software license agreement for enterprise-level software deployment.'
      ],
      [
        'contract_name' => 'Consulting Services Agreement',
        'client' => 'XYZ Ltd.',
        'start_date' => '2025-03-15',
        'end_date' => '2026-03-15',
        'status' => 'Pending Review',
        'ref' => 'PC-002',
        'description' => 'Consulting services for business analysis and strategic planning.'
      ],
      [
        'contract_name' => 'Supply Agreement',
        'client' => 'LMN Corp.',
        'start_date' => '2025-05-01',
        'end_date' => '2026-05-01',
        'status' => 'Pending Signature',
        'ref' => 'PC-003',
        'description' => 'Agreement for the supply of raw materials for manufacturing.'
      ],
      [
        'contract_name' => 'Maintenance Agreement',
        'client' => 'OPQ Industries',
        'start_date' => '2025-06-01',
        'end_date' => '2026-06-01',
        'status' => 'Pending Approval',
        'ref' => 'PC-004',
        'description' => 'Maintenance and support services for industrial equipment.'
      ],
      [
        'contract_name' => 'Employment Agreement',
        'client' => 'ABC Technologies',
        'start_date' => '2025-07-01',
        'end_date' => '2026-07-01',
        'status' => 'Pending Review',
        'ref' => 'PC-005',
        'description' => 'A new employment agreement for a senior management position.'
      ]
    ];

    // Filter contracts by date if the filter is set
    $filterDate = $_GET['filter_date'] ?? '';
    $filteredContracts = array_filter($contracts, function ($contract) use ($filterDate) {
      return !$filterDate || $contract['start_date'] === $filterDate;
    });

    // Check if a specific contract is selected for detailed view
    $selectedRef = $_GET['ref'] ?? '';
    $viewContract = null;
    foreach ($filteredContracts as $contract) {
      if ($contract['ref'] === $selectedRef) {
        $viewContract = $contract;
        break;
      }
    }
  ?>

  <?php if ($viewContract): ?>
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title text-dark"><?php echo $viewContract['contract_name']; ?></h5>
        <p><strong>Client:</strong> <?php echo $viewContract['client']; ?></p>
        <p><strong>Start Date:</strong> <?php echo $viewContract['start_date']; ?></p>
        <p><strong>End Date:</strong> <?php echo $viewContract['end_date']; ?></p>
        <p><strong>Status:</strong> <?php echo $viewContract['status']; ?></p>
        <p><strong>Reference:</strong> <?php echo $viewContract['ref']; ?></p>
        <p><strong>Description:</strong> <?php echo $viewContract['description']; ?></p>
        <a href="index.php" class="btn btn-outline-primary mt-3 no-print">← Back to List</a>
      </div>
    </div>
  <?php else: ?>
    <!-- Contracts Table -->
    <table class="table table-bordered contract-table">
      <thead>
        <tr>
          <th>Contract Name</th>
          <th>Client</th>
          <th>Start Date</th>
          <th>Status</th>
          <th>Reference</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($filteredContracts as $contract): ?>
          <tr>
            <td><?php echo $contract['contract_name']; ?></td>
            <td><?php echo $contract['client']; ?></td>
            <td><?php echo $contract['start_date']; ?></td>
            <td>
              <?php 
                // Color-coded status
                if ($contract['status'] === 'Pending Approval') {
                  echo "<span class='badge bg-warning text-dark'>Pending Approval</span>";
                } elseif ($contract['status'] === 'Pending Review') {
                  echo "<span class='badge bg-info text-dark'>Pending Review</span>";
                } elseif ($contract['status'] === 'Pending Signature') {
                  echo "<span class='badge bg-primary text-white'>Pending Signature</span>";
                }
              ?>
            </td>
            <td><?php echo $contract['ref']; ?></td>
            <td>
              <a href="?ref=<?php echo urlencode($contract['ref']); ?>" class="view-btn btn-sm">View Details</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <footer class="text-center text-muted mt-5 no-print">
    <hr>
    <p>&copy; <?php echo date("Y"); ?> Legal Office. All rights reserved.</p>
  </footer>
</div>

@endsection