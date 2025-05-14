@extends('layouts.app')
@section('title','External Directory')
@section('content')

<div class="container my-5">
  <div class="text-center mb-4">
    <h2 class="text-primary fw-bold">External Counsel Directory</h2>
    <p class="text-muted">View details about external counsel firms, contacts, and engagements</p>
    <!-- Link to create.php for adding new firm -->
    <a href="{{route('externaldirectory.create')}} " class="btn btn-outline-success mb-3">Add New External Counsel</a>
  </div>

  <!-- Table for External Counsel Directory -->
  <table class="table table-striped table-bordered">
    <thead>
      <tr>
        <th>Firm Name</th>
        <th>Contact Person</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Engagement Scope</th>
        <th>Terms</th>
      </tr>
    </thead>
    <tbody>
      <?php
      // Sample data for external counsel
      $firms = [
        ['firm' => 'ABC Legal', 'contact' => 'John Doe', 'email' => 'johndoe@abclegal.com', 'phone' => '+1 234 567 890', 'scope' => 'Intellectual Property', 'terms' => 'Annual Retainer'],
        ['firm' => 'XYZ Law Group', 'contact' => 'Jane Smith', 'email' => 'janesmith@xyzlaw.com', 'phone' => '+1 987 654 321', 'scope' => 'Corporate Litigation', 'terms' => 'Hourly Billing'],
        ['firm' => 'PQR Attorneys', 'contact' => 'Samuel Lee', 'email' => 'samuel.lee@pqrattorneys.com', 'phone' => '+1 345 678 901', 'scope' => 'Contract Law', 'terms' => 'Project-based'],
        ['firm' => 'LMN Partners', 'contact' => 'Alice Johnson', 'email' => 'alice.johnson@lmnpartners.com', 'phone' => '+1 456 789 012', 'scope' => 'Mergers & Acquisitions', 'terms' => 'Flat Fee'],
      ];

      // Display the table rows
      foreach ($firms as $firm) {
        echo "
        <tr>
          <td>{$firm['firm']}</td>
          <td>{$firm['contact']}</td>
          <td><a href='mailto:{$firm['email']}'>{$firm['email']}</a></td>
          <td><a href='tel:{$firm['phone']}'>{$firm['phone']}</a></td>
          <td>{$firm['scope']}</td>
          <td>{$firm['terms']}</td>
        </tr>";
      }
      ?>
    </tbody>
  </table>

  <footer class="text-center text-muted mt-5">
    <hr>
    <p>&copy; <?php echo date("Y"); ?> Legal Department. All rights reserved.</p>
  </footer>
</div>


@endsection