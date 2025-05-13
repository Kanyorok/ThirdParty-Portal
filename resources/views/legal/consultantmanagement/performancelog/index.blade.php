@extends('layouts.app')
@section('title','Performance Log')
@section('content')

<div class="container mt-5">
  <h2 class="mb-4">Performance Log</h2>

  <!-- Filter (just UI) -->
  <form class="row mb-4" method="GET">
    <div class="col-md-4">
      <input type="date" name="start_date" class="form-control" placeholder="Start Date">
    </div>
    <div class="col-md-4">
      <input type="date" name="end_date" class="form-control" placeholder="End Date">
    </div>
    <div class="col-md-4">
      <button class="btn btn-primary">Filter</button>
    </div>
  </form>

  <table class="table table-bordered align-middle">
    <thead class="table-light">
      <tr>
        <th>Name / Team</th>
        <th>Assignment</th>
        <th>Success Rate</th>
        <th>Date Completed</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      
      <!-- Entry 1 -->
      <tr>
        <td>Team A</td>
        <td>Contract Review</td>
        <td>95%</td>
        <td>2025-01-12</td>
        <td>
          <form method="GET">
            <input type="hidden" name="view" value="1">
            <input type="hidden" name="entry" value="1">
            <button type="submit" class="btn btn-sm btn-info">View</button>
          </form>
        </td>
      </tr>
      <?php if (isset($_GET['view']) && $_GET['view'] == '1' && isset($_GET['entry']) && $_GET['entry'] == '1'): ?>
      <tr class="details-row">
        <td colspan="5">
          <strong>Feedback:</strong> Well structured and timely.<br>
          <strong>Rating:</strong> Excellent<br>
          <strong>Duration:</strong> 2 weeks<br>
          <strong>Reviewer:</strong> Legal Head, Region A
        </td>
      </tr>
      <?php endif; ?>

      <!-- Entry 2 -->
      <tr>
        <td>Jane Doe</td>
        <td>IP Filing</td>
        <td>89%</td>
        <td>2025-02-03</td>
        <td>
          <form method="GET">
            <input type="hidden" name="view" value="1">
            <input type="hidden" name="entry" value="2">
            <button type="submit" class="btn btn-sm btn-info">View</button>
          </form>
        </td>
      </tr>
      <?php if (isset($_GET['view']) && $_GET['view'] == '1' && isset($_GET['entry']) && $_GET['entry'] == '2'): ?>
      <tr class="details-row">
        <td colspan="5">
          <strong>Feedback:</strong> Accurate but a day late.<br>
          <strong>Rating:</strong> Good<br>
          <strong>Duration:</strong> 5 days<br>
          <strong>Reviewer:</strong> Patent Coordinator
        </td>
      </tr>
      <?php endif; ?>

      <!-- Entry 3 -->
      <tr>
        <td>Team B</td>
        <td>Court Representation</td>
        <td>100%</td>
        <td>2025-03-21</td>
        <td>
          <form method="GET">
            <input type="hidden" name="view" value="1">
            <input type="hidden" name="entry" value="3">
            <button type="submit" class="btn btn-sm btn-info">View</button>
          </form>
        </td>
      </tr>
      <?php if (isset($_GET['view']) && $_GET['view'] == '1' && isset($_GET['entry']) && $_GET['entry'] == '3'): ?>
      <tr class="details-row">
        <td colspan="5">
          <strong>Feedback:</strong> Excellent advocacy and result.<br>
          <strong>Rating:</strong> Outstanding<br>
          <strong>Duration:</strong> 1 month<br>
          <strong>Reviewer:</strong> Litigation Manager
        </td>
      </tr>
      <?php endif; ?>

      <!-- Entry 4 -->
      <tr>
        <td>John Smith</td>
        <td>Policy Drafting</td>
        <td>85%</td>
        <td>2025-04-10</td>
        <td>
          <form method="GET">
            <input type="hidden" name="view" value="1">
            <input type="hidden" name="entry" value="4">
            <button type="submit" class="btn btn-sm btn-info">View</button>
          </form>
        </td>
      </tr>
      <?php if (isset($_GET['view']) && $_GET['view'] == '1' && isset($_GET['entry']) && $_GET['entry'] == '4'): ?>
      <tr class="details-row">
        <td colspan="5">
          <strong>Feedback:</strong> Improvement needed in formatting.<br>
          <strong>Rating:</strong> Average<br>
          <strong>Duration:</strong> 10 days<br>
          <strong>Reviewer:</strong> Compliance Officer
        </td>
      </tr>
      <?php endif; ?>

    </tbody>
  </table>
</div>

@endsection