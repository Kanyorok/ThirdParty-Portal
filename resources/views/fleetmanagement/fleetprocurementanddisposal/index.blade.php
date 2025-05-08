@extends('layouts.app')
@section('title', 'Fleet Procurement And Disposal')
@section('content')

<div class="container mt-5">
    <h1 class="text-center text-primary mb-4">Fleet Procurement and Disposal</h1>

    <?php
    // Define sample fleet records
    $fleetRecords = [
        ['id' => 1, 'vehicle_type' => 'Toyota Hilux', 'purchase_date' => '2024-06-01', 'vendor' => 'Toyota Kenya', 'status' => 'Procured'],
        ['id' => 2, 'vehicle_type' => 'Mitsubishi Canter', 'purchase_date' => '2023-11-10', 'vendor' => 'MMC Auto', 'status' => 'Disposed'],
        ['id' => 3, 'vehicle_type' => 'Isuzu NQR', 'purchase_date' => '2024-02-15', 'vendor' => 'General Motors', 'status' => 'Procured']
    ];

    // Extract vendors
    $vendors = array_unique(array_column($fleetRecords, 'vendor'));

    // Count status
    $procured = array_filter($fleetRecords, fn($r) => $r['status'] === 'Procured');
    $disposed = array_filter($fleetRecords, fn($r) => $r['status'] === 'Disposed');
    ?>

    <section class="mt-4">
        <h3 class="text-secondary">Fleet Records</h3>
        <div class="table-responsive">
            <table class="table table-striped">
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
    </section>

    <section class="mt-5">
        <h3 class="text-secondary">Fleet Status Summary</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="card bg-success text-white mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Procured</h5>
                        <p class="card-text"><?= count($procured) ?> Vehicles</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-danger text-white mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Disposed</h5>
                        <p class="card-text"><?= count($disposed) ?> Vehicles</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-5">
        <h3 class="text-secondary">Vendors</h3>
        <ul class="list-group">
            <?php foreach ($vendors as $vendor): ?>
                <li class="list-group-item"><?= $vendor ?></li>
            <?php endforeach; ?>
        </ul>
    </section>

    <div class="text-end mt-4">
        <a href="{{route('fleetprocurementanddisposal.create')}}" class="btn btn-success">Add New Fleet Record</a>
    </div>
</div>
@endsection