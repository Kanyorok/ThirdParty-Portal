@extends('layouts.app')
@section('title','External Directory')
@section('content')

<div class="container my-5">
  <div class="text-center mb-4">
    <h2 class="text-primary fw-bold">Add New External Counsel</h2>
    <p class="text-muted">Fill in the details below to add a new external counsel firm to the directory</p>
  </div>

  <!-- Add New Counsel Form -->
  <div class="form-container">
    <form action="create.php" method="POST">
      <div class="mb-3">
        <label for="firm" class="form-label">Firm Name</label>
        <input type="text" class="form-control" id="firm" name="firm" required>
      </div>
      <div class="mb-3">
        <label for="contact" class="form-label">Contact Person</label>
        <input type="text" class="form-control" id="contact" name="contact" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>
      <div class="mb-3">
        <label for="phone" class="form-label">Phone Number</label>
        <input type="text" class="form-control" id="phone" name="phone" required>
      </div>
      <div class="mb-3">
        <label for="scope" class="form-label">Engagement Scope</label>
        <input type="text" class="form-control" id="scope" name="scope" required>
      </div>
      <div class="mb-3">
        <label for="terms" class="form-label">Terms of Engagement</label>
        <input type="text" class="form-control" id="terms" name="terms" required>
      </div>
      <button type="submit" class="btn btn-primary">Add Counsel</button>
    </form>
  </div>

  <!-- Back to Directory Link -->
  <div class="mt-4 text-center">
    <a href="{{route('externaldirectory.index')}} " class="btn btn-outline-secondary">Back to Directory</a>
  </div>

  <?php
  // Handle form submission and store new counsel data
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sample data storage (in a real application, you would store this in a database)
    $newCounsel = [
      'firm' => $_POST['firm'],
      'contact' => $_POST['contact'],
      'email' => $_POST['email'],
      'phone' => $_POST['phone'],
      'scope' => $_POST['scope'],
      'terms' => $_POST['terms']
    ];

    // In a real application, you would save this data to a database here

    // Display success message
    echo "<div class='alert alert-success mt-3 text-center'>External Counsel Added Successfully!</div>";
  }
  ?>

</div>

@endsection