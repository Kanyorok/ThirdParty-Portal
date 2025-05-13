@extends('layouts.app')
@section('title','Correspondence Archive')
@section('content')

<div class="container my-5">
  <div class="text-center mb-4">
    <h2 class="fw-bold text-primary">Correspondence Archive</h2>
    <p class="text-muted">Track and manage archived emails, letters, and official communications</p>
    <!-- HREF to create.php -->
    <a href="{{route('archive.create')}} " class="btn btn-outline-primary">➕ Add New Correspondence</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead class="table-primary">
            <tr>
              <th scope="col">Type</th>
              <th scope="col">Subject</th>
              <th scope="col">Recipient</th>
              <th scope="col">Date Sent</th>
              <th scope="col">Reference ID</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $correspondence = [
              ['type' => 'Email', 'subject' => 'Contract Finalization', 'to' => 'legal@company.com', 'date' => '2024-07-01', 'ref' => 'EMAIL-2024-001'],
              ['type' => 'Letter', 'subject' => 'Notice of Intent to Renew', 'to' => 'Registrar, IP Office', 'date' => '2024-06-15', 'ref' => 'LTR-2024-022'],
              ['type' => 'Official Communication', 'subject' => 'Policy Change Notification', 'to' => 'All Departments', 'date' => '2024-08-10', 'ref' => 'OFF-2024-003'],
              ['type' => 'Email', 'subject' => 'Meeting Summary', 'to' => 'board@org.com', 'date' => '2024-07-20', 'ref' => 'EMAIL-2024-015']
            ];

            foreach ($correspondence as $index => $item) {
              echo "<tr>
                      <td>{$item['type']}</td>
                      <td>{$item['subject']}</td>
                      <td>{$item['to']}</td>
                      <td>{$item['date']}</td>
                      <td><a href='view.php?id=$index'>{$item['ref']}</a></td>
                    </tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <footer class="text-center text-muted mt-5">
    <hr>
    <p>&copy; <?php echo date("Y"); ?> Your Organization. All rights reserved.</p>
  </footer>
</div>
@endsection