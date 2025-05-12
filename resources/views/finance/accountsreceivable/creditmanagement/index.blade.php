@extends('layouts.app')
@section('title', 'Credit Management- Accounts Receivable')
@section('content')
<div class="container mt-5">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Credit Management</h2>
            <a href= "{{ route('customermaster.create') }}" class="btn btn-primary">Add Credit Profile</a>
        </div>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Customer</th>
                    <th>Credit Limit (Ksh)</th>
                    <th>Outstanding Balance</th>
                    <th>Credit Terms</th>
                    <th>Last Updated</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Static Sample Data -->
                <tr>
                    <td>ABC Distributors</td>
                    <td>500,000</td>
                    <td>350,000</td>
                    <td>30 Days</td>
                    <td>2025-05-01</td>
                    <td><span class="badge bg-warning">Active</span></td>
                    <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
                <tr>
                    <td>XYZ Enterprises</td>
                    <td>300,000</td>
                    <td>50,000</td>
                    <td>45 Days</td>
                    <td>2025-04-25</td>
                    <td><span class="badge bg-success">Good Standing</span></td>
                    <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
            </tbody>
        </table>
    </div>
    @endsection

