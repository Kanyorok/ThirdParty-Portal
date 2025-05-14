@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">📅 Procurement Timeline View</h4>

    <!-- Filters -->
    <form class="row g-3 mb-3">
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
                <option>Overdue</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-primary w-100">Apply Filters</button>
        </div>
    </form>

    <!-- Timeline Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light text-center">
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Method</th>
                    <th>Officer</th>
                    <th>Planned Start</th>
                    <th>Planned End</th>
                    <th>Actual Start</th>
                    <th>Actual End</th>
                    <th>Status</th>
                    <th>Progress</th>
                    <th>Delay</th>
                </tr>
            </thead>
            <tbody>
                <!-- Sample Row -->
                <tr>
                    <td>1</td>
                    <td>Desktop Computers</td>
                    <td>Tender</td>
                    <td>Grace A.</td>
                    <td>2025-03-01</td>
                    <td>2025-04-30</td>
                    <td>2025-03-05</td>
                    <td>–</td>
                    <td><span class="badge bg-warning">In Progress</span></td>
                    <td><div class="progress" style="height: 8px;"><div class="progress-bar bg-warning" style="width: 60%;"></div></div></td>
                    <td><span class="text-danger">10 days late</span></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Stationery Supplies</td>
                    <td>RFQ</td>
                    <td>John M.</td>
                    <td>2025-01-05</td>
                    <td>2025-01-15</td>
                    <td>2025-01-06</td>
                    <td>2025-01-13</td>
                    <td><span class="badge bg-success">Completed</span></td>
                    <td><div class="progress" style="height: 8px;"><div class="progress-bar bg-success" style="width: 100%;"></div></div></td>
                    <td><span class="text-muted">On Time</span></td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Office Furniture</td>
                    <td>Direct</td>
                    <td>Linda O.</td>
                    <td>2025-02-10</td>
                    <td>2025-02-20</td>
                    <td>–</td>
                    <td>–</td>
                    <td><span class="badge bg-secondary">Not Started</span></td>
                    <td><div class="progress" style="height: 8px;"><div class="progress-bar bg-secondary" style="width: 0%;"></div></div></td>
                    <td><span class="text-muted">–</span></td>
                </tr>
                <!-- More dynamic rows -->
            </tbody>
        </table>
    </div>
</div>

@endsection
