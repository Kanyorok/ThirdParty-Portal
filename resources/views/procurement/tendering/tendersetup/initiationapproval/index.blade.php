@extends('layouts.app')
@section('title', 'Tender Initiation Approval')
@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Tender Initiation Approvals</h4>
        <a href="{{ route('initiateapprove.create') }}" class="btn btn-sm btn-success">+ New Approval</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tender Reference</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Approved By</th>
                    <th>Date</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Sample Row -->
                <tr>
                    <td>1</td>
                    <td>TND/PROC/2025/001</td>
                    <td>Supply of Laptops</td>
                    <td><span class="badge bg-success">Approved</span></td>
                    <td>Procurement Head</td>
                    <td>2025-05-06</td>
                    <td>All documents checked and approved.</td>
                    <td>
                        <button class="btn btn-sm btn-outline-info">View</button>
                        <button class="btn btn-sm btn-outline-primary">Edit</button>
                    </td>
                </tr>
                <!-- Additional rows -->
            </tbody>
        </table>
    </div>
</div>

@endsection
