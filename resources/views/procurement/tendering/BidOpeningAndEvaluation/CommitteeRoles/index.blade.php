@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
     <a href="{{ route('assignrole.create') }}" class="btn btn-primary mb-3">Assign Role</a>
    <h4 class="mb-3">📄 Committee Roles Overview</h4>
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Tender Reference</th>
                    <th>Member Name</th>
                    <th>Assigned Role</th>
                    <th>Appointment Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Example Row -->
                <tr>
                    <td>TND/PROC/2025/001</td>
                    <td>Grace A.</td>
                    <td>Financial Evaluator</td>
                    <td>2025-05-10</td>
                    <td><span class="badge bg-success">Accepted</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary">Edit Role</button>
                    </td>
                </tr>
                <!-- Repeat rows for other members -->
            </tbody>
        </table>
    </div>
</div>

@endsection
