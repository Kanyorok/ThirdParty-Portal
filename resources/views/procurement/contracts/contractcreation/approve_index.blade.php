@extends('layouts.app')
@section('title', 'Contract Approval Queue')

@section('content')
<div class="container mt-4">
    <h4>✅ Contract Approval & Sign-Off Queue</h4>

    <!-- Filters -->
    <form class="row g-3 mb-3">
        <div class="col-md-3">
            <select class="form-select">
                <option selected>All Status</option>
                <option value="Pending">Pending</option>
                <option value="Partially Approved">Partially Approved</option>
                <option value="Signed">Signed</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" placeholder="Search by Title or Ref">
        </div>
        <div class="col-md-3">
            <button class="btn btn-outline-primary w-100">Filter</button>
        </div>
    </form>

    <!-- Contracts Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Ref No.</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Vendor</th>
                            <th>Start - End</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <tr>
                            <td>1</td>
                            <td>CONTRACT/PROC/2025/009</td>
                            <td>Supply of Office Furniture</td>
                            <td><span class="badge bg-warning text-dark">Partially Approved</span></td>
                            <td>OfficePro Suppliers</td>
                            <td>2025-07-01 → 2025-12-31</td>
                            <td>
                                <a href="{{ route('contracts.contracts.approve', 1) }}" class="btn btn-sm btn-outline-primary">Review</a>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>CONTRACT/PROC/2025/010</td>
                            <td>Procurement of Network Switches</td>
                            <td><span class="badge bg-success">Signed</span></td>
                            <td>CompTech Solutions</td>
                            <td>2025-06-01 → 2025-12-01</td>
                            <td>
                                <a href="{{ route('contracts.contracts.approve', 2) }}" class="btn btn-sm btn-outline-secondary">View</a>
                            </td>
                        </tr>
                        <!-- More rows... -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
