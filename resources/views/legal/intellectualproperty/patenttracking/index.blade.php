@extends('layouts.app')
@section('title','Patent Tracking')
@section('content')

<div class="container my-5">
    <div class="text-center mb-4">
        <h1 class="fw-bold text-primary">Patent Tracking System</h1>
        <p class="text-muted">Overview of file numbers, jurisdictions, and status updates</p>
        <!-- Add New Patent Button -->
        <a href="{{ route('patenttracking.create') }}" class="btn btn-outline-primary mt-3">➕ Add New Patent</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th scope="col">File Number</th>
                            <th scope="col">Jurisdiction</th>
                            <th scope="col">Filing Date</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $patents = [
                            ['file' => 'PTN00123', 'jurisdiction' => 'US', 'date' => '2023-06-01', 'status' => 'Pending'],
                            ['file' => 'PTN00124', 'jurisdiction' => 'EU', 'date' => '2023-05-12', 'status' => 'Approved'],
                            ['file' => 'PTN00125', 'jurisdiction' => 'JP', 'date' => '2023-04-20', 'status' => 'Under Review'],
                            ['file' => 'PTN00126', 'jurisdiction' => 'KE', 'date' => '2023-03-10', 'status' => 'Rejected'],
                            ['file' => 'PTN00127', 'jurisdiction' => 'IN', 'date' => '2023-02-14', 'status' => 'Pending']
                        ];

                        foreach ($patents as $patent) {
                            $badgeClass = match ($patent['status']) {
                                'Pending' => 'bg-warning text-dark',
                                'Approved' => 'bg-success',
                                'Rejected' => 'bg-danger',
                                'Under Review' => 'bg-info text-dark',
                                default => 'bg-secondary'
                            };

                            // For demonstration: Replace '#' with actual route or URL
                            $editUrl = "#edit-" . $patent['file'];
                            $deleteUrl = "#delete-" . $patent['file'];

                            echo "<tr>
                                <td>{$patent['file']}</td>
                                <td>{$patent['jurisdiction']}</td>
                                <td>{$patent['date']}</td>
                                <td><span class='badge {$badgeClass} status-badge'>{$patent['status']}</span></td>
                                <td>
                                    <a href='{$editUrl}' class='btn btn-sm btn-outline-primary me-2'>✏️ Edit</a>
                                    <a href='{$deleteUrl}' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"Are you sure you want to delete this patent?\")'>🗑️ Delete</a>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="text-center mt-5 text-muted">
        <hr>
        <p>&copy; <?php echo date("Y"); ?> Your Company Name. All rights reserved.</p>
    </footer>
</div>


@endsection