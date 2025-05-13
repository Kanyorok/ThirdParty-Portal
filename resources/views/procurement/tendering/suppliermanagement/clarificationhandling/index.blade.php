@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Clarification Requests</h4>
        <select class="form-select w-auto" style="min-width: 250px;">
            <option selected>Filter by Tender</option>
            <option>TND/PROC/2025/001 - ICT Equipment</option>
            <option>TND/PROC/2025/002 - Office Furniture</option>
        </select>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tender Ref</th>
                    <th>Supplier</th>
                    <th>Question</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Responded</th>
                    <th>Published</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Sample Row -->
                <tr>
                    <td>1</td>
                    <td>TND/PROC/2025/001</td>
                    <td>Tech Supplies Ltd</td>
                    <td>Should installation costs be included in the price?</td>
                    <td><span class="badge bg-warning">Pending</span></td>
                    <td>2025-05-08</td>
                    <td>-</td>
                    <td><span class="badge bg-secondary">No</span></td>
                    <td>
                        <a href="{{ route('tenderclarification.create') }}" class="btn btn-sm btn-outline-primary">Respond</a>
                    </td>
                </tr>

                <tr>
                    <td>2</td>
                    <td>TND/PROC/2025/001</td>
                    <td>Nova Systems</td>
                    <td>Can we submit documents digitally only?</td>
                    <td><span class="badge bg-success">Responded</span></td>
                    <td>2025-05-07</td>
                    <td>2025-05-08</td>
                    <td><span class="badge bg-success">Yes</span></td>
                    <td>
                        <a href="/clarification/edit/2" class="btn btn-sm btn-outline-success">Edit</a>
                    </td>
                </tr>

                <!-- More rows dynamically populated -->
            </tbody>
        </table>
    </div>
</div>
@endsection