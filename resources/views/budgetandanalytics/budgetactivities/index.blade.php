@extends('layouts.app')
@section('title', 'Activities Overview')
@section('content')
    <div class="container mt-4">
        <div class="card p-4">
            <h5>📋 Budget Activities Overview</h5>
            <p class="text-muted">All activities contributing to budget lines with allocation summaries.</p>

            <div class="mb-2 d-flex justify-content-between">
                <a href="{{ route('budgetactivities.create') }}" class="btn btn-success">➕ New Activity</a>
            </div>
            <table class="table table-bordered table-hover table-striped align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Budget Line</th>
                    <th>Activity</th>
                    <th>Owner</th>
                    <th>Cost Center</th>
                    <th>Driver-Based</th>
                    <th>Total Allocation (KES)</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td>Marketing</td>
                    <td>Radio Ad Campaign – Q1</td>
                    <td>Jane Mwangi</td>
                    <td>Marketing Dept</td>
                    <td><span class="badge bg-success">Yes</span></td>
                    <td>250,000</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary">✏️ Edit</button>
                        <button class="btn btn-sm btn-outline-secondary">📊 View Monthly</button>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Training</td>
                    <td>Branch Staff Training</td>
                    <td>John Otieno</td>
                    <td>HR Dept</td>
                    <td><span class="badge bg-secondary">No</span></td>
                    <td>180,000</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary">✏️ Edit</button>
                        <button class="btn btn-sm btn-outline-secondary">📊 View Monthly</button>
                    </td>
                </tr>
                <!-- Add more rows as needed -->
                </tbody>
            </table>
        </div>
    </div>

@endsection
