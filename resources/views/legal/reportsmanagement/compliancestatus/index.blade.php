@extends('layouts.app')
@section('title','Compliance Status')
@section('content')

<div class="container my-5">

  <div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
      <h2 class="text-primary fw-bold mb-0">Compliance Status</h2>
      <p class="text-muted">Track compliance with regulations, audits, and corrective actions.</p>
    </div>
    <button onclick="window.print()" class="btn btn-outline-secondary">🖨️ Print</button>
  </div>

  <!-- Filter by Date -->
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
    // Sample compliance status data
    $compliance = [
      [
        'title' => 'Data Protection Audit',
        'auditor' => 'ABC Auditors',
        'audit_date' => '2025-04-12',
        'status' => 'Pending Approval',
        'ref' => 'CS-001',
        'description' => 'Annual audit to assess the organization’s data protection measures.'
      ],
      [
        'title' => 'Environmental Compliance Review',
        'auditor' => 'XYZ Auditing Firm',
        'audit_date' => '2025-03-25',
        'status' => 'Reviewed',
        'ref' => 'CS-002',
        'description' => 'Review of compliance with environmental regulations in our manufacturing processes.'
      ],
      [
        'title' => 'Financial Compliance Audit',
        'auditor' => 'LMN Accounting',
        'audit_date' => '2025-02-28',
        'status' => 'Approved',
        'ref' => 'CS-003',
        'description' => 'Audit of financial statements for compliance with international accounting standards.'
      ],
      [
        'title' => 'Health and Safety Inspection',
        'auditor' => 'PQR Safety Consultants',
        'audit_date' => '2025-05-05',
        'status' => 'Pending Review',
        'ref' => 'CS-004',
        'description' => 'Inspection for health and safety compliance in the workplace.'
      ],
      [
        'title' => 'IT Security Compliance',
        'auditor' => 'DEF Security Services',
        'audit_date' => '2025-01-15',
        'status' => 'Reviewed',
        'ref' => 'CS-005',
        'description' => 'Audit to ensure compliance with IT security regulations and protocols.'
      ],
      [
        'title' => 'Employee Benefits Review',
        'auditor' => 'Global HR Consultants',
        'audit_date' => '2025-04-22',
        'status' => 'Incomplete',
        'ref' => 'CS-006',
        'description' => 'Review of employee benefit programs for compliance with national regulations.'
      ],
      [
        'title' => 'Corporate Governance Assessment',
        'auditor' => 'Board Compliance Ltd.',
        'audit_date' => '2025-03-05',
        'status' => 'Completed',
        'ref' => 'CS-007',
        'description' => 'Assessment of corporate governance structures and adherence to best practices.'
      ],
      [
        'title' => 'Anti-Money Laundering Review',
        'auditor' => 'International Compliance Auditors',
        'audit_date' => '2025-04-19',
        'status' => 'Rejected',
        'ref' => 'CS-008',
        'description' => 'Review of anti-money laundering measures and reporting protocols.'
      ]
    ];

    // Filter compliance data by date if the filter is set
    $filterDate = $_GET['filter_date'] ?? '';
    $filteredCompliance = array_filter($compliance, function ($complianceItem) use ($filterDate) {
      return !$filterDate || $complianceItem['audit_date'] === $filterDate;
    });

    // Check if a specific compliance item is selected for detailed view
    $selectedRef = $_GET['ref'] ?? '';
    $viewCompliance = null;
    foreach ($filteredCompliance as $complianceItem) {
      if ($complianceItem['ref'] === $selectedRef) {
        $viewCompliance = $complianceItem;
        break;
      }
    }
  ?>

  <?php if ($viewCompliance): ?>
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title text-dark"><?php echo $viewCompliance['title']; ?></h5>
        <p><strong>Auditor:</strong> <?php echo $viewCompliance['auditor']; ?></p>
        <p><strong>Audit Date:</strong> <?php echo $viewCompliance['audit_date']; ?></p>
        <p><strong>Status:</strong> <?php echo $viewCompliance['status']; ?></p>
        <p><strong>Reference:</strong> <?php echo $viewCompliance['ref']; ?></p>
        <p><strong>Description:</strong> <?php echo $viewCompliance['description']; ?></p>
        <a href="index.php" class="btn btn-outline-primary mt-3 no-print">← Back to List</a>
      </div>
    </div>
  <?php else: ?>
    <!-- Compliance Table -->
    <table class="table table-bordered compliance-table">
      <thead>
        <tr>
          <th>Title</th>
          <th>Auditor</th>
          <th>Audit Date</th>
          <th>Status</th>
          <th>Reference</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($filteredCompliance as $complianceItem): ?>
          <tr>
            <td><?php echo $complianceItem['title']; ?></td>
            <td><?php echo $complianceItem['auditor']; ?></td>
            <td><?php echo $complianceItem['audit_date']; ?></td>
            <td>
              <span class="status <?php echo ($complianceItem['status'] === 'Pending Approval') ? 'status-pending' : ''; ?>
                              <?php echo ($complianceItem['status'] === 'Reviewed') ? 'status-reviewed' : ''; ?>
                              <?php echo ($complianceItem['status'] === 'Approved') ? 'status-approved' : ''; ?>
                              <?php echo ($complianceItem['status'] === 'Incomplete') ? 'status-incomplete' : ''; ?>
                              <?php echo ($complianceItem['status'] === 'Completed') ? 'status-completed' : ''; ?>
                              <?php echo ($complianceItem['status'] === 'Rejected') ? 'status-rejected' : ''; ?>">
                <?php echo $complianceItem['status']; ?>
              </span>
            </td>
            <td><?php echo $complianceItem['ref']; ?></td>
            <td>
              <a href="?ref=<?php echo urlencode($complianceItem['ref']); ?>" class="view-btn btn btn-sm">View Details</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <footer class="text-center text-muted mt-5 no-print">
    <hr>
    <p>&copy; <?php echo date("Y"); ?> Compliance Tracking Office. All rights reserved.</p>
  </footer>
</div>

@endsection