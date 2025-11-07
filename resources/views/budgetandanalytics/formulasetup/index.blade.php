@extends('layouts.app')
@section('title', 'Budget Formulas')
@section('content')
    <div class="card p-3">
        <div class="mb-2 d-flex justify-content-between">
            <a href="{{ route('budgetformula.create') }}" class="btn btn-success">➕ New Formula</a>

        </div>
        <h5>📋 Budget Formulas</h5>
        <table class="table table-hover table-bordered">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Budget Line</th>
                <th>Drivers Used</th>
                <th>Formula</th>
                <th>Output Unit</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>1</td>
                <td>Interest Income – Loans</td>
                <td>LoanBook, InterestRate</td>
                <td>LoanBook * InterestRate</td>
                <td>KES</td>
                <td><span class="badge bg-success">Active</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary">✏️ Edit</button>
                    <button class="btn btn-sm btn-outline-danger">🗑 Delete</button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection
