@extends('layouts.app')
@section('title', 'Tax Return History')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">📄 Tax Return History</h4>

        <div class="mb-3 text-end">
            <a href="{{ route('taxreturngenerator.create') }}" class="btn btn-primary">➕ Generate New Return</a>
        </div>

        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>Return ID</th>
                <th>Tax Type</th>
                <th>Jurisdiction</th>
                <th>Period</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Filed At</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <!-- Sample static data -->
            <tr>
                <td>RET-0001</td>
                <td>VAT</td>
                <td>Kenya</td>
                <td>2024-12</td>
                <td>200,000.00</td>
                <td><span class="badge bg-success">Filed</span></td>
                <td>2025-01-10</td>
                <td>
                    <a href="#" class="btn btn-sm btn-info">View</a>
                    <a href="#" class="btn btn-sm btn-secondary">Download</a>
                </td>
            </tr>
            <tr>
                <td>RET-0002</td>
                <td>WHT</td>
                <td>Uganda</td>
                <td>2025-03</td>
                <td>45,000.00</td>
                <td><span class="badge bg-warning">Pending Filing</span></td>
                <td>-</td>
                <td>
                    <a href="#" class="btn btn-sm btn-info">View</a>
                    <a href="#" class="btn btn-sm btn-primary">Submit</a>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection
