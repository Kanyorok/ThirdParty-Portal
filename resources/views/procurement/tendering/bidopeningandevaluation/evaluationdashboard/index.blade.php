@extends('layouts.app')
@section('title', 'Evaluation Dashboard')
@section('content')
<div class="container mt-4">
    <h4 class="mb-3">🎯 Evaluation Dashboard – My Assignments</h4>

    <!-- Task Table -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tender</th>
                    <th>Supplier</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Assigned On</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Sample task -->
                <tr>
                    <td>1</td>
                    <td>TND/PROC/2025/001</td>
                    <td>Tech Supplies Ltd</td>
                    <td>Technical Evaluator</td>
                    <td><span class="badge bg-warning">Pending</span></td>
                    <td>2025-05-10</td>
                    <td>
                        <a href="{{ route('bidevaluation.index') }}" class="btn btn-sm btn-outline-primary">Evaluate</a>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>TND/PROC/2025/001</td>
                    <td>Nova Systems</td>
                    <td>Technical Evaluator</td>
                    <td><span class="badge bg-success">Submitted</span></td>
                    <td>2025-05-10</td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary" disabled>Done</button>
                    </td>
                </tr>
                <!-- Additional tasks -->
            </tbody>
        </table>
    </div>
</div>
@endsection