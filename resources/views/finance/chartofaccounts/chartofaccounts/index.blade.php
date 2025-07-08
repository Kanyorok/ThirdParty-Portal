@extends('layouts.app')
@section('title', 'Chart of Accounts')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">📘 Chart of Accounts</h4>

    <!-- Add New Account Button -->
    <div class="mb-3">
        <a href="{{ route('chartofaccounts.create') }}" class="btn btn-primary">➕ Add New Account</a>
    </div>

    <!-- Accounts Table -->
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>Account Code</th>
                <th>Account Name</th>
                <th>Type</th>
                <th>Parent</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1000</td>
                <td>Cash & Bank</td>
                <td>Asset</td>
                <td>—</td>
                <td><span class="badge bg-success">Active</span></td>
                <td><a href="#" class="btn btn-sm btn-warning">Edit</a></td>
            </tr>
            <tr>
                <td>1100</td>
                <td>Cash - HQ</td>
                <td>Asset</td>
                <td>Cash & Bank</td>
                <td><span class="badge bg-success">Active</span></td>
                <td><a href="#" class="btn btn-sm btn-warning">Edit</a></td>
            </tr>
            <tr>
                <td>2000</td>
                <td>Accounts Payable</td>
                <td>Liability</td>
                <td>—</td>
                <td><span class="badge bg-secondary">Inactive</span></td>
                <td><a href="#" class="btn btn-sm btn-warning">Edit</a></td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
