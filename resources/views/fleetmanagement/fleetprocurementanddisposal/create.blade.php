@extends('layouts.app')
@section('title', 'Fleet Procurement And Disposal')
@section('content')

<div class="container mt-5">
    <h1 class="text-center text-primary mb-4">Fleet Procurement and Disposal</h1>

    <section class="mt-4">
        <h2 class="mb-4">Register New Fleet Purchase/Disposal</h2>
        <form method="POST" action="create_fleet_procurement.php">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="vehicle_type" class="form-label">Vehicle Type</label>
                    <input type="text" class="form-control" id="vehicle_type" name="vehicle_type" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="purchase_date" class="form-label">Purchase/Disposal Date</label>
                    <input type="date" class="form-control" id="purchase_date" name="purchase_date" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="vendor" class="form-label">Vendor</label>
                <input type="text" class="form-control" id="vendor" name="vendor" required>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="">--Select Status--</option>
                    <option value="Procured">Procured</option>
                    <option value="Disposed">Disposed</option>
                </select>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-success">Save Fleet Record</button>
            </div>
        </form>
    </section>

    <section class="mt-5">
        <h2 class="mb-4">Fleet Procurement/Disposal Records</h2>
        <?php if (!empty($fleetRecords)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Vehicle Type</th>
                            <th>Date</th>
                            <th>Vendor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fleetRecords as $record): ?>
                            <tr>
                                <td><?= $record['id'] ?></td>
                                <td><?= $record['vehicle_type'] ?></td>
                                <td><?= $record['purchase_date'] ?></td>
                                <td><?= $record['vendor'] ?></td>
                                <td>
                                    <span class="badge bg-<?= $record['status'] === 'Procured' ? 'success' : 'danger' ?>">
                                        <?= $record['status'] ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">No fleet records available yet.</div>
        <?php endif; ?>
    </section>
</div>
@endsection