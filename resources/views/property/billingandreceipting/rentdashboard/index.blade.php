@extends('layouts.app')
@section('title', 'Rent Collection Dashboard')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">🏠 Rent Collection Dashboard</h4>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-2">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h6 class="card-title">Collected</h6>
                    <h4>KES 540,000</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h6 class="card-title">Due Soon</h6>
                    <h4>KES 210,000</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h6 class="card-title">Overdue</h6>
                    <h4>KES 125,000</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card text-white bg-secondary">
                <div class="card-body">
                    <h6 class="card-title">Partial Payments</h6>
                    <h4>KES 40,000</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <form class="row g-2 mb-3">
        <div class="col-md-3">
            <select class="form-select" id="branchFilter">
                <option selected>All Branches</option>
                <option>Westlands</option>
                <option>CBD</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="propertyFilter">
                <option selected>All Properties</option>
                <option>Sunrise Apartments</option>
                <option>Green Hills Estate</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="tenantFilter">
                <option selected>All Tenants</option>
                <option>Jane Mwangi</option>
                <option>Michael Otieno</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="month" class="form-control" id="monthFilter">
        </div>
    </form>

    <!-- Data Grid -->
    <div class="table-responsive mb-4">
        <table class="table table-striped table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tenant</th>
                    <th>Unit</th>
                    <th>Due Date</th>
                    <th>Amount Due</th>
                    <th>Amount Paid</th>
                    <th>Late Fee</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Sample Row -->
                <tr>
                    <td>1</td>
                    <td>Jane Mwangi</td>
                    <td>Block A - Unit 103</td>
                    <td>2025-05-01</td>
                    <td>KES 25,000</td>
                    <td>KES 15,000</td>
                    <td>KES 1,250</td>
                    <td><span class="badge bg-warning">Partial</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary">View</button>
                        <button class="btn btn-sm btn-outline-success">Post Payment</button>
                    </td>
                </tr>
                <!-- More rows -->
            </tbody>
        </table>
    </div>

    <!-- Chart Placeholder -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">📈 Collection Trend</h6>
        </div>
        <div class="card-body">
            <div style="height: 300px; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                <span>[Bar Chart Placeholder]</span>
            </div>
        </div>
    </div>
</div>
@endsection