@extends('layouts.app')
@section('title','Fee Tracker')
@section('content')


<div class="container my-5">
  <div class="text-center mb-4">
    <h2 class="text-primary fw-bold">Legal Fee Tracker</h2>
    <p class="text-muted">Review and monitor existing legal fees, retainers, and invoices</p>
  </div>

  <!-- Filter Form -->
  <form method="GET" class="row g-3 mb-4 align-items-end">
    <div class="col-md-4">
      <input type="text" name="client" class="form-control" placeholder="Filter by client name..." value="<?= htmlspecialchars($_GET['client'] ?? '') ?>">
    </div>
    <div class="col-md-4">
      <input type="date" name="date" class="form-control" placeholder="Filter by invoice date..." value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <select name="status" class="form-select">
        <option value="">All Payment Statuses</option>
        <option value="Paid" <?= (($_GET['status'] ?? '') === 'Paid') ? 'selected' : '' ?>>Paid</option>
        <option value="Pending" <?= (($_GET['status'] ?? '') === 'Pending') ? 'selected' : '' ?>>Pending</option>
      </select>
    </div>
    <div class="col-md-1">
      <button type="submit" class="btn btn-primary w-100">Filter</button>
    </div>
  </form>

  <!-- Fee Tracker Table -->
  <table class="table table-striped table-bordered" id="feeTable">
    <thead>
      <tr>
        <th>Client</th>
        <th>Invoice #</th>
        <th>Fee Type</th>
        <th>Amount</th>
        <th>Invoice Date</th>
        <th>Payment Status</th>
      </tr>
    </thead>
    <tbody>
      <?php
      // Static data (could be from a DB)
      $fees = [
        ['client' => 'ABC Corp', 'invoice' => 'INV001', 'type' => 'Retainer', 'amount' => '$5,000', 'date' => '2025-01-10', 'status' => 'Paid'],
        ['client' => 'XYZ Ltd.', 'invoice' => 'INV002', 'type' => 'Hourly', 'amount' => '$2,500', 'date' => '2025-03-15', 'status' => 'Pending'],
        ['client' => 'LMN Enterprises', 'invoice' => 'INV003', 'type' => 'Retainer', 'amount' => '$3,000', 'date' => '2025-04-05', 'status' => 'Paid'],
        ['client' => 'PQR Industries', 'invoice' => 'INV004', 'type' => 'Hourly', 'amount' => '$1,200', 'date' => '2025-04-10', 'status' => 'Pending'],
      ];

      // Get filter values from GET parameters
      $clientFilter = strtolower(trim($_GET['client'] ?? ''));
      $dateFilter = $_GET['date'] ?? '';
      $statusFilter = $_GET['status'] ?? '';

      // Display filtered rows
      foreach ($fees as $fee) {
        $matchesClient = $clientFilter === '' || strpos(strtolower($fee['client']), $clientFilter) !== false;
        $matchesDate = $dateFilter === '' || $fee['date'] === $dateFilter;
        $matchesStatus = $statusFilter === '' || $fee['status'] === $statusFilter;

        if ($matchesClient && $matchesDate && $matchesStatus) {
          echo "<tr>
                  <td>{$fee['client']}</td>
                  <td>{$fee['invoice']}</td>
                  <td>{$fee['type']}</td>
                  <td>{$fee['amount']}</td>
                  <td>{$fee['date']}</td>
                  <td>{$fee['status']}</td>
                </tr>";
        }
      }
      ?>
    </tbody>
  </table>

  <footer class="text-center text-muted mt-5">
    <hr>
    <p>&copy; <?= date("Y"); ?> Legal Department. Fee Tracker System.</p>
  </footer>
</div>

@endsection