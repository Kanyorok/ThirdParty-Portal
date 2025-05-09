@extends('layouts.app')
@section('title', 'Cash Book')
@section('content')

<div class="container mt-4">
    <div class="top-link mb-3">
        <!-- Href linking to a form page (no backend) -->
        <a href="{{route('cashbook.create')}} " class="btn btn-primary">+ Add New Entry</a>
    </div>

    <h2 class="mb-4">Cash Book Records</h2>

    <?php
    // Sample data for cash book entries
    $cashBook = [
        ['id' => 1, 'date' => '2025-05-01', 'description' => 'Opening Balance', 'amount' => 1000, 'type' => 'Credit'],
        ['id' => 2, 'date' => '2025-05-02', 'description' => 'Office Supplies', 'amount' => 150, 'type' => 'Debit'],
        ['id' => 3, 'date' => '2025-05-03', 'description' => 'Client Payment', 'amount' => 500, 'type' => 'Credit'],
    ];
    ?>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Type</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cashBook as $entry): ?>
                <tr>
                    <td><?= htmlspecialchars($entry['id']) ?></td>
                    <td><?= htmlspecialchars($entry['date']) ?></td>
                    <td><?= htmlspecialchars($entry['description']) ?></td>
                    <td><?= htmlspecialchars($entry['amount']) ?></td>
                    <td><?= htmlspecialchars($entry['type']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

@endsection