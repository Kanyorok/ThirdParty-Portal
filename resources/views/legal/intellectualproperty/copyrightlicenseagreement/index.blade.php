@extends('layouts.app')
@section('title','License Agreement')
@section('content')

<div class="container mt-5">
  <h2 class="mb-4">Copyright / License Agreements</h2>

  <!-- PHP: Handle Renew Action -->
  <?php
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['renew'])) {
    $entryId = $_POST['entry'];

    // Simulated renewal logic
    $renewalSuccess = true;

    if ($renewalSuccess) {
      echo "<div class='alert alert-success text-center mb-4'>License for entry $entryId renewed successfully.</div>";
    } else {
      echo "<div class='alert alert-danger text-center mb-4'>Failed to renew license for entry $entryId.</div>";
    }
  }
  ?>

  <!-- Optional Filter -->
  <form class="row mb-4" method="GET">
    <div class="col-md-5">
      <input type="date" name="start_date" class="form-control" placeholder="Start Date">
    </div>
    <div class="col-md-5">
      <input type="date" name="end_date" class="form-control" placeholder="End Date">
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100">Filter</button>
    </div>
  </form>

  <table class="table table-bordered align-middle">
    <thead class="table-light">
      <tr>
        <th>Title</th>
        <th>Terms of Usage</th>
        <th>Authorized Parties</th>
        <th>Renewal Alert</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>

      <!-- Entry 1 -->
      <tr>
        <td>Software Suite A</td>
        <td>Single-user, non-commercial use</td>
        <td>IT Department</td>
        <td>2025-09-01</td>
        <td>
          <form method="GET" style="display:inline;">
            <input type="hidden" name="view" value="1">
            <input type="hidden" name="entry" value="1">
            <button type="submit" class="btn btn-sm btn-info">View</button>
          </form>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="renew" value="1">
            <input type="hidden" name="entry" value="1">
            <button type="submit" class="btn btn-sm btn-warning">Renew</button>
          </form>
        </td>
      </tr>
      <?php if (isset($_GET['view']) && $_GET['view'] == '1' && $_GET['entry'] == '1'): ?>
      <tr class="details-row">
        <td colspan="5">
          <strong>Description:</strong> Licensed from Vendor X for internal use.<br>
          <strong>Contact:</strong> licenses@example.com<br>
          <strong>Status:</strong> Active
        </td>
      </tr>
      <?php endif; ?>

      <!-- Entry 2 -->
      <tr>
        <td>Content Library B</td>
        <td>Multi-user, editorial rights</td>
        <td>Media Division</td>
        <td>2025-06-15</td>
        <td>
          <form method="GET" style="display:inline;">
            <input type="hidden" name="view" value="1">
            <input type="hidden" name="entry" value="2">
            <button type="submit" class="btn btn-sm btn-info">View</button>
          </form>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="renew" value="1">
            <input type="hidden" name="entry" value="2">
            <button type="submit" class="btn btn-sm btn-warning">Renew</button>
          </form>
        </td>
      </tr>
      <?php if (isset($_GET['view']) && $_GET['view'] == '1' && $_GET['entry'] == '2'): ?>
      <tr class="details-row">
        <td colspan="5">
          <strong>Description:</strong> Includes images and video for branding.<br>
          <strong>Contact:</strong> media@example.com<br>
          <strong>Status:</strong> Renewal Due
        </td>
      </tr>
      <?php endif; ?>

      <!-- Entry 3 -->
      <tr>
        <td>Design Assets C</td>
        <td>Unlimited commercial use</td>
        <td>Marketing Team</td>
        <td>2025-12-20</td>
        <td>
          <form method="GET" style="display:inline;">
            <input type="hidden" name="view" value="1">
            <input type="hidden" name="entry" value="3">
            <button type="submit" class="btn btn-sm btn-info">View</button>
          </form>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="renew" value="1">
            <input type="hidden" name="entry" value="3">
            <button type="submit" class="btn btn-sm btn-warning">Renew</button>
          </form>
        </td>
      </tr>
      <?php if (isset($_GET['view']) && $_GET['view'] == '1' && $_GET['entry'] == '3'): ?>
      <tr class="details-row">
        <td colspan="5">
          <strong>Description:</strong> Covers logos, templates, and licensed fonts.<br>
          <strong>Contact:</strong> design@example.com<br>
          <strong>Status:</strong> Active
        </td>
      </tr>
      <?php endif; ?>

    </tbody>
  </table>
</div>

@endsection