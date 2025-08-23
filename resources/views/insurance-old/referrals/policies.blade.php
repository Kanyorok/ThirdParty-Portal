@extends('layouts.app')
@section('title', 'My Referred Client Policies')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">📄 My Clients' Issued Policies</h4>

        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                <th>#</th>
                <th>Client Name</th>
                <th>Policy No.</th>
                <th>Product</th>
                <th>Cover Type</th>
                <th>Premium</th>
                <th>Status</th>
                <th>Start Date</th>
                <th>Expiry</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            {{-- Example row --}}
            <tr>
                <td>1</td>
                <td>Jane Njeri</td>
                <td>POL-202507001</td>
                <td>Credit Life</td>
                <td>Loan Protection</td>
                <td>KES 5,000</td>
                <td><span class="badge bg-success">Active</span></td>
                <td>2025-07-10</td>
                <td>2026-07-10</td>
                <td>
                    <a href="#" class="btn btn-sm btn-info">View Schedule</a>
                    <a href="#" class="btn btn-sm btn-outline-secondary">Download PDF</a>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection
