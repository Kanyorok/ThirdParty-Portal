@extends('layouts.app')
@section('title', 'Fleet Procurement And Disposal')
@section('content')

<div class="container mt-5">
        <h1 class="text-center text-primary">Spare Parts Inventory System</h1>

        <?php
        // All variables and containers defined inside the div section

        // Sample array of spare parts
        $spareParts = [
            ['id' => 1, 'name' => 'Brake Disc', 'vehicle' => 'Toyota Prado', 'quantity' => 8, 'reorder_level' => 3, 'vendor' => 'BrakeZone Ltd'],
            ['id' => 2, 'name' => 'Oil Filter', 'vehicle' => 'Subaru Forester', 'quantity' => 4, 'reorder_level' => 5, 'vendor' => 'FilterTech'],
            ['id' => 3, 'name' => 'Clutch Kit', 'vehicle' => 'Isuzu D-Max', 'quantity' => 6, 'reorder_level' => 2, 'vendor' => 'ClutchPro']
        ];

        // Example container for vendors
        $vendors = ['BrakeZone Ltd', 'FilterTech', 'ClutchPro'];

        // Example: Spare parts used per vehicle (associative container)
        $sparesPerVehicle = [
            'Toyota Prado' => ['Brake Disc', 'Oil Filter'],
            'Subaru Forester' => ['Oil Filter'],
            'Isuzu D-Max' => ['Clutch Kit']
        ];

        // Reorder levels (critical spares)
        $criticalReorders = array_filter($spareParts, function($part) {
            return $part['quantity'] <= $part['reorder_level'];
        });
        ?>

        <!-- Inventory Table -->
        <section class="mt-4">
            <h3>Inventory Module</h3>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Part Name</th>
                        <th>Vehicle</th>
                        <th>Quantity</th>
                        <th>Reorder Level</th>
                        <th>Vendor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($spareParts as $part): ?>
                        <tr>
                            <td><?= $part['id'] ?></td>
                            <td><?= $part['name'] ?></td>
                            <td><?= $part['vehicle'] ?></td>
                            <td><?= $part['quantity'] ?></td>
                            <td><?= $part['reorder_level'] ?></td>
                            <td><?= $part['vendor'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- Spare Parts per Vehicle -->
        <section class="mt-5">
            <h3>Spare Parts Used per Vehicle</h3>
            <?php foreach ($sparesPerVehicle as $vehicle => $parts): ?>
                <p><strong><?= $vehicle ?>:</strong> <?= implode(', ', $parts) ?></p>
            <?php endforeach; ?>
        </section>

        <!-- Reorder Level Alerts -->
        <section class="mt-5">
            <h3>Critical Spares (Below Reorder Level)</h3>
            <?php if (empty($criticalReorders)): ?>
                <div class="alert alert-success">All spares are above reorder levels.</div>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($criticalReorders as $part): ?>
                        <li class="list-group-item list-group-item-danger">
                            <?= $part['name'] ?> for <?= $part['vehicle'] ?> (Qty: <?= $part['quantity'] ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <!-- Vendor List -->
        <section class="mt-5">
            <h3>Vendors</h3>
            <ul class="list-group">
                <?php foreach ($vendors as $vendor): ?>
                    <li class="list-group-item"><?= $vendor ?></li>
                <?php endforeach; ?>
            </ul>
        </section>

        <!-- Link to Create Page -->
        <div class="text-end mt-4">
            <a href="{{route('inventoryofspareparts.create')}}" class="btn btn-success">Add New Spare Part</a>
        </div>
    </div>


@endsection