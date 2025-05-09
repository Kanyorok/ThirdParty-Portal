@extends('layouts.app')
@section('title', 'Account Setup')
@section('content')


<div class="container">
    <h1 class="my-4">Bank Account Setup</h1>

    <!-- Link to Create page (No form, simple link) -->
    <a href="{{route('bankaccountsetup.create')}} " class="btn btn-primary mb-3">Create a New Bank Account</a>

    <?php
    // Example static data for bank accounts
    $bankAccounts = [
        ['id' => 101, 'account_holder' => 'John Doe', 'account_number' => '1234567890', 'account_type' => 'Savings', 'status' => 'Active'],
        ['id' => 102, 'account_holder' => 'Jane Smith', 'account_number' => '9876543210', 'account_type' => 'Checking', 'status' => 'Pending']
    ];
    ?>

    <!-- Table to display bank account information -->
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Account Holder Name</th>
                <th>Account Number</th>
                <th>Account Type</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <!-- Loop through the bankAccounts array and display each account -->
            <?php foreach ($bankAccounts as $bankAccount): ?>
                <tr>
                    <td><?= htmlspecialchars($bankAccount['id']) ?></td>
                    <td><?= htmlspecialchars($bankAccount['account_holder']) ?></td>
                    <td><?= htmlspecialchars($bankAccount['account_number']) ?></td>
                    <td><?= htmlspecialchars($bankAccount['account_type']) ?></td>
                    <td><?= htmlspecialchars($bankAccount['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


@endsection