@extends('layouts.app')
@section('title', 'Recurring Journals')
@section('content')
<div class="container mt-4">
    <h4 class="mb-3">🔁 Recurring Journals</h4>

    <div class="mb-3 text-end">
        <a href="{{ route('recurrentjournal.create') }}" class="btn btn-primary">➕ New Recurring Journal</a>
    </div>

    <table class="table table-striped table-bordered">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Reference Name</th>
                <th>Frequency</th>
                <th>Start Date</th>
                <th>Status</th>
                <th>Next Run</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Monthly Rent Accrual</td>
                <td>Monthly</td>
                <td>2024-01-01</td>
                <td><span class="badge bg-success">Active</span></td>
                <td>2025-07-01</td>
                <td>
                    <a href="#" class="btn btn-sm btn-secondary">View</a>
                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                </td>
            </tr>
            <tr>
                <td>2</td>
                <td>Quarterly Audit Provision</td>
                <td>Quarterly</td>
                <td>2024-03-01</td>
                <td><span class="badge bg-secondary">Inactive</span></td>
                <td>2025-09-01</td>
                <td>
                    <a href="#" class="btn btn-sm btn-secondary">View</a>
                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
