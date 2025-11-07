@extends('layouts.app')
@section('title', 'Fleet Procurement And Disposal')
@section('content')

<div class="container mt-5">
    <h1 class="text-center text-primary mb-4">Cashlink Financial Modules</h1>

    <!-- Bank Accounts Setup -->
    <section class="mt-4">
        <h3 class="text-secondary">Bank Accounts Setup</h3>
        <div class="card mb-3">
            <div class="card-body">
                <p class="card-text">Manage your organization's bank accounts for transaction mapping.</p>
            </div>
        </div>
    </section>

    <!-- Bank Reconciliation -->
    <section class="mt-4">
        <h3 class="text-secondary">Bank Reconciliation</h3>
        <div class="card mb-3">
            <div class="card-body">
                <p class="card-text">Reconcile bank statements with internal cash book records.</p>
            </div>
        </div>
    </section>

    <!-- Cash Book -->
    <section class="mt-4">
        <h3 class="text-secondary">Cash Book</h3>
        <div class="card mb-3">
            <div class="card-body">
                <p class="card-text">Monitor all cash and bank transactions in one consolidated ledger.</p>
            </div>
        </div>
    </section>

    <!-- Payment & Receipt Vouchers -->
    <section class="mt-4">
        <h3 class="text-secondary">Payment & Receipt Vouchers</h3>
        <div class="card mb-3">
            <div class="card-body">
                <p class="card-text">Generate and record incoming and outgoing payment transactions.</p>
            </div>
        </div>
    </section>

    <!-- Petty Cash Management -->
    <section class="mt-4">
        <h3 class="text-secondary">Petty Cash Management</h3>
        <div class="card mb-3">
            <div class="card-body">
                <p class="card-text">Track and control petty cash usage for small operational expenses.</p>
            </div>
        </div>
    </section>

    <!-- Cheque Management -->
    <section class="mt-4">
        <h3 class="text-secondary">Cheque Management</h3>
        <div class="card mb-3">
            <div class="card-body">
                <p class="card-text">Manage cheques issued, received, and voided across accounts.</p>
            </div>
        </div>
    </section>

    <!-- Add New Record Button -->
    <div class="text-end mt-4">
        <a href="{{route('cashlink.create')}} " class="btn btn-success">Add New Cashlink Record</a>
    </div>
</div>
@endsection