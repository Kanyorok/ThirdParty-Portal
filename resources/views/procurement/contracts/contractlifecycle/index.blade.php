@extends('layouts.app')
@section('title', '📘 Contracts Lifecycle')

@section('content')
    <div class="container mt-4">
        <h4>📘 Contracts Lifecycle</h4>

        <!-- Filters -->
        <form class="row g-3 mb-3">
            <div class="col-md-3">
                <select class="form-select">
                    <option selected>All Status</option>
                    <option value="Active">Active</option>
                    <option value="Terminated">Terminated</option>
                    <option value="Expired">Expired</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" placeholder="Search by Title / Ref / Vendor">
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100">Filter</button>
            </div>
        </form>

        <!-- Contracts Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Ref No.</th>
                            <th>Title</th>
                            <th>Vendor</th>
                            <th>Status</th>
                            <th>Start – End</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>1</td>
                            <td>CONTRACT/PROC/2025/010</td>
                            <td>Supply of Office Furniture</td>
                            <td>OfficePro Ltd</td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>2025-07-01 → 2025-12-31</td>
                            <td class="d-flex gap-1 justify-content-center flex-wrap">
                                <a href="{{ route('contracts.contracts.view', 1) }}"
                                   class="btn btn-sm btn-outline-secondary">View</a>
                                <a href="{{ route('contracts.contracts.execution', 1) }}"
                                   class="btn btn-sm btn-outline-primary">Monitor</a>
                                <a href="{{ route('contracts.contracts.amend', 1) }}"
                                   class="btn btn-sm btn-outline-danger">Amend/Terminate</a>
                            </td>
                        </tr>
                        <!-- More contracts -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
