@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Tender Committees</h4>
        <a href="{{ route('tendercommittee.create') }}" class="btn btn-sm btn-success">+ Appoint New Committee</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tender Ref</th>
                    <th>Member Name</th>
                    <th>Role</th>
                    <th>Appointment Date</th>
                    <th>Status</th>
                    <th>Responded On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>TND/PROC/2025/001</td>
                    <td>Moses K.</td>
                    <td>Chairperson</td>
                    <td>2025-05-10</td>
                    <td><span class="badge bg-warning">Pending</span></td>
                    <td>-</td>
                    <td>
                        <button class="btn btn-sm btn-outline-info">View</button>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>TND/PROC/2025/001</td>
                    <td>Grace A.</td>
                    <td>Member</td>
                    <td>2025-05-10</td>
                    <td><span class="badge bg-success">Accepted</span></td>
                    <td>2025-05-11</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary">Reassign</button>
                    </td>
                </tr>
                <!-- More rows -->
            </tbody>
        </table>
    </div>
</div>
@endsection
