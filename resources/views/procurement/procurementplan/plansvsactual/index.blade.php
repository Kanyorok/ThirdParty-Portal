@extends('layouts.app')
@section('title', 'Plan vs Actual Variance')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">📉 Plan vs Actual Variance Report</h4>

    <!-- Filters -->
    <form class="row g-3 mb-4">
        <div class="col-md-3">
            <select class="form-select">
                <option selected>All Branches</option>
                <option>Nairobi HQ</option>
                <option>Mombasa</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select">
                <option selected>All Departments</option>
                <option>ICT</option>
                <option>Finance</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select">
                <option selected>All Years</option>
                <option>2025</option>
                <option>2026</option>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-outline-primary w-100">Apply Filters</button>
        </div>
    </form>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title">📊 Item-Level Variance Report</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Planned Cost</th>
                            <th>Actual Cost</th>
                            <th>Variance (Cost)</th>
                            <th>Planned End</th>
                            <th>Actual End</th>
                            <th>Variance (Time)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Sample Row -->
                        <tr>
                            <td>1</td>
                            <td>Desktop Computers</td>
                            <td>KES 720,000</td>
                            <td>KES 800,000</td>
                            <td><span class="text-danger">+80,000</span></td>
                            <td>2025-04-30</td>
                            <td>2025-05-10</td>
                            <td><span class="text-danger">+10 days</span></td>
                            <td><span class="badge bg-success">Completed</span></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Stationery</td>
                            <td>KES 150,000</td>
                            <td>KES 145,000</td>
                            <td><span class="text-success">-5,000</span></td>
                            <td>2025-01-15</td>
                            <td>2025-01-13</td>
                            <td><span class="text-success">-2 days</span></td>
                            <td><span class="badge bg-success">Completed</span></td>
                        </tr>
                        <!-- Add dynamic rows -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection