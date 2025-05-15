@extends('layouts.app')
@section('title', 'Planned Procurement Items')
@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>📦 Planned Procurement Items</h4>
        <a href="{{ route('procurementplanmaintain.index') }}" class="btn btn-outline-primary btn-sm">🔙 Back to Plans</a>
    </div>

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
        <div class="col-md-2">
            <select class="form-select">
                <option selected>All Years</option>
                <option>2025</option>
                <option>2026</option>
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select">
                <option selected>Status</option>
                <option>Not Started</option>
                <option>In Progress</option>
                <option>Completed</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-primary w-100">Apply Filters</button>
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Est. Cost</th>
                    <th>Method</th>
                    <th>Planned Period</th>
                    <th>Assigned Officer</th>
                    <th>Status</th>
                    <th>Linked To</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Sample Row -->
                <tr>
                    <td>1</td>
                    <td>Desktop Computers</td>
                    <td>12</td>
                    <td>KES 720,000</td>
                    <td>Tender</td>
                    <td>Q2</td>
                    <td>Grace A.</td>
                    <td><span class="badge bg-warning">In Progress</span></td>
                    <td><a href="/tenders/view/102">TND/2025/002</a></td>
                    <td>
                        <a href="#" class="btn btn-sm btn-outline-secondary">Update</a>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Stationery</td>
                    <td>50</td>
                    <td>KES 150,000</td>
                    <td>RFQ</td>
                    <td>Q1</td>
                    <td>John M.</td>
                    <td><span class="badge bg-success">Completed</span></td>
                    <td><a href="/rfq/view/59">RFQ/2025/059</a></td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary" disabled>View</button>
                    </td>
                </tr>
                <!-- Additional dynamic rows -->
            </tbody>
        </table>
    </div>
</div>

@endsection
