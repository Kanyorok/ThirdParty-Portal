@extends('layouts.app')
@section('title', 'Compliance And Documentation')
@section('content')

<div class="container-fluid">
    <main class="col-12 px-md-4 py-4">
        <h1 class="h2 mb-4">Compliance Dashboard</h1>

        <!-- Add Compliance Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Add New Compliance Item</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <input type="text" name="standard" class="form-control" placeholder="Standard (e.g., ISO 27001)" required>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-control" required>
                                <option value="">Select Status</option>
                                <option value="Compliant">Compliant</option>
                                <option value="Partial">Partial</option>
                                <option value="Non-Compliant">Non-Compliant</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="last_audit" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="next_due" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" name="add" class="btn btn-success">Add Compliance</button>
                        </div>
                    </div>
                </form>

                <?php
                if (isset($_POST['add'])) {
                    $data = [
                        'standard' => $_POST['standard'],
                        'status' => $_POST['status'],
                        'last_audit' => $_POST['last_audit'],
                        'next_due' => $_POST['next_due']
                    ];

                    $file = 'compliance_data.json';
                    $existing = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
                    $existing[] = $data;
                    file_put_contents($file, json_encode($existing, JSON_PRETTY_PRINT));

                    echo "<div class='alert alert-success mt-3'>Compliance item added successfully.</div>";
                }
                ?>
            </div>
        </div>

        <!-- Compliance Status Table -->
        <div class="card">
            <div class="card-header">
                <h5>Compliance Overview</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Standard</th>
                                <th>Status</th>
                                <th>Last Audit</th>
                                <th>Next Due</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $file = 'compliance_data.json';
                            if (file_exists($file)) {
                                $items = json_decode(file_get_contents($file), true);
                                foreach ($items as $item) {
                                    $badge = $item['status'] == 'Compliant' ? 'bg-success' :
                                             ($item['status'] == 'Partial' ? 'bg-warning text-dark' : 'bg-danger');

                                    echo "<tr>
                                        <td>{$item['standard']}</td>
                                        <td><span class='badge $badge'>{$item['status']}</span></td>
                                        <td>{$item['last_audit']}</td>
                                        <td>{$item['next_due']}</td>
                                        <td><button class='btn btn-sm btn-outline-primary' disabled>Review</button></td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>No data available.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

@endsection
