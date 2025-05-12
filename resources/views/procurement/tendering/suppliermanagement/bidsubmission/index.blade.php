@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Manual Bid Submissions</h4>
        <a href="{{ route('tendersubmission.create') }}" class="btn btn-sm btn-success">+ Record Manual Submission</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tender Ref</th>
                    <th>Supplier</th>
                    <th>Submission Mode</th>
                    <th>Received At</th>
                    <th>Recorded By</th>
                    <th>Remarks</th>
                    <th>Documents</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Sample Row -->
                <tr>
                    <td>1</td>
                    <td>TND/PROC/2025/002</td>
                    <td>Nova Systems Ltd</td>
                    <td>Courier</td>
                    <td>2025-05-09 09:30 AM</td>
                    <td>Procurement Assistant</td>
                    <td>Delivered in sealed envelope</td>
                    <td><a href="#">Download</a></td>
                    <td>
                        <a href="/bid-submission/manual/view/1" class="btn btn-sm btn-outline-info">View</a>
                        <a href="/bid-submission/manual/edit/1" class="btn btn-sm btn-outline-primary">Edit</a>
                    </td>
                </tr>
                <!-- Repeat for other entries -->
            </tbody>
        </table>
    </div>
</div>
@endsection



