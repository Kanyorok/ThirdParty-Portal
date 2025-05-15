@extends('layouts.app')
@section('title', 'Delayed & Flagged Procurement Items')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">🚨 Delayed & Flagged Procurement Items</h4>

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
                <option selected>All Flags</option>
                <option>Overdue</option>
                <option>Not Started</option>
                <option>Delayed Start</option>
                <option>Stalled</option>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-outline-primary w-100">Apply Filters</button>
        </div>
    </form>

    <!-- Delayed Items Table -->
    <div class="card shadow-sm mb-5">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Planned End</th>
                            <th>Actual Start</th>
                            <th>Actual End</th>
                            <th>Officer</th>
                            <th>Flag</th>
                            <th>Days Late</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Sample Overdue Item -->
                        <tr>
                            <td>1</td>
                            <td>Desktop Computers</td>
                            <td>2025-04-30</td>
                            <td>2025-03-10</td>
                            <td>–</td>
                            <td>Grace A.</td>
                            <td><span class="badge bg-danger">Overdue</span></td>
                            <td><span class="text-danger">+12</span></td>
                            <td><a href="#" class="btn btn-sm btn-outline-secondary">Remind</a></td>
                        </tr>
                        <!-- Sample Stalled Item -->
                        <tr>
                            <td>2</td>
                            <td>Boardroom Furniture</td>
                            <td>2025-05-10</td>
                            <td>2025-04-15</td>
                            <td>–</td>
                            <td>Linda M.</td>
                            <td><span class="badge bg-warning text-dark">Stalled</span></td>
                            <td><span class="text-warning">+5</span></td>
                            <td><a href="#" class="btn btn-sm btn-outline-danger">Escalate</a></td>
                        </tr>
                        <!-- Sample Not Started -->
                        <tr>
                            <td>3</td>
                            <td>A4 Stationery</td>
                            <td>2025-04-20</td>
                            <td>–</td>
                            <td>–</td>
                            <td>John M.</td>
                            <td><span class="badge bg-secondary">Not Started</span></td>
                            <td><span class="text-muted">+18</span></td>
                            <td><a href="#" class="btn btn-sm btn-outline-primary">Follow Up</a></td>
                        </tr>
                        <!-- More dynamic rows -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
