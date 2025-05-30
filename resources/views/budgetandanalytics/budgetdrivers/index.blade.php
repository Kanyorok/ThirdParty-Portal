@extends('layouts.app')
@section('title', 'Business Drivers')
@section('content')
    <div class="card p-3">
        <h5>📋 Business Drivers List</h5>
        <div class="mb-2 d-flex justify-content-between">
            <a href="{{ route('budgetdrivers.create') }}" class="btn btn-success">➕ Add Driver</a>

        </div>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Code</th>
                <th>Type</th>
                <th>Unit</th>
                <th>Frequency</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>1</td>
                <td>Loan Book Growth</td>
                <td>DRV_LOAN_GROWTH</td>
                <td>Financial</td>
                <td>%</td>
                <td>Monthly</td>
                <td><span class="badge bg-success">Active</span></td>
                <td>
                    <button class="btn btn-sm btn-info">✏️ Edit</button>
                    <button class="btn btn-sm btn-danger">🗑️ Delete</button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection
