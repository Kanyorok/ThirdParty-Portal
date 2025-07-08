@extends('layouts.app')
@section('title', 'Journal Entries')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">📑 Journal Entries</h4>

    <!-- Add New Entry Button -->
    <div class="mb-3">
        <a href="{{ route('journalentry.create') }}" class="btn btn-primary">➕ New Journal Entry</a>
    </div>

    <!-- Journal Entries Table -->
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Description</th>
                <th>Total Debit</th>
                <th>Total Credit</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>2025-06-01</td>
                <td>JV20240601</td>
                <td>Salary Payment - May</td>
                <td>100,000.00</td>
                <td>100,000.00</td>
                <td><span class="badge bg-success">Posted</span></td>
                <td>
                    <a href="#" class="btn btn-sm btn-secondary">View</a>
                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                </td>
            </tr>
            <tr>
                <td>2025-06-02</td>
                <td>JV20240602</td>
                <td>Interest Income Accrual</td>
                <td>50,000.00</td>
                <td>50,000.00</td>
                <td><span class="badge bg-secondary">Draft</span></td>
                <td>
                    <a href="#" class="btn btn-sm btn-secondary">View</a>
                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection