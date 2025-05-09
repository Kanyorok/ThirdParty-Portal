@extends('layouts.app')
@section('title', 'Vouchers')
@section('content')

<div class="container py-5">
        <div class="mb-4">
            <?php
                $validTypes = ['payment', 'receipt'];
                $type = isset($_GET['type']) && in_array($_GET['type'], $validTypes) ? $_GET['type'] : 'payment';
                $voucherTitle = ucfirst($type) . " Voucher";
            ?>
            <h2>Create <?= htmlspecialchars($voucherTitle) ?></h2>
            <p class="text-muted">Fill in the details below to register a new <?= strtolower(htmlspecialchars($voucherTitle)) ?>.</p>
        </div>

        <form action="store.php" method="post">
            <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">

            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" name="date" id="date" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="party" class="form-label">
                    <?= $type === 'payment' ? 'Payee' : 'Payer' ?>
                </label>
                <input type="text" name="party" id="party" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="amount" class="form-label">Amount (KSh)</label>
                <input type="number" name="amount" id="amount" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="3" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Save Voucher</button>
        </form>
    </div>
@endsection