@extends('layouts.app')
@section('title', 'Bank Statement Uploads')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">📄 Uploaded Bank Statements</h4>

        <a href="{{ route('reconuploads.create') }}" class="btn btn-primary mb-3">➕ Upload New Statement</a>

        <table class="table table-bordered table-hover">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Bank Account</th>
                <th>Period</th>
                <th>File Name</th>
                <th>Uploaded By</th>
                <th>Upload Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <!-- Static Data Rows -->
            <tr>
                <td>1</td>
                <td>001 - Equity Bank - KES</td>
                <td>01-Jun-2025 to 30-Jun-2025</td>
                <td>equity_june2025.csv</td>
                <td>jkamau</td>
                <td>02-Jul-2025</td>
                <td><span class="badge bg-success">Reconciled</span></td>
                <td>
                    <a href="#" class="btn btn-sm btn-outline-info">🔍 View</a>
                    <a href="#" class="btn btn-sm btn-outline-primary">↪ Reconcile</a>
                    <a href="#" class="btn btn-sm btn-outline-secondary">📥 Download</a>
                </td>
            </tr>
            <tr>
                <td>2</td>
                <td>002 - KCB Corporate - USD</td>
                <td>01-Jun-2025 to 30-Jun-2025</td>
                <td>kcb_usd_june2025.txt</td>
                <td>nmutua</td>
                <td>03-Jul-2025</td>
                <td><span class="badge bg-warning text-dark">Pending</span></td>
                <td>
                    <a href="#" class="btn btn-sm btn-outline-info">🔍 View</a>
                    <a href="#" class="btn btn-sm btn-outline-primary">↪ Reconcile</a>
                    <a href="#" class="btn btn-sm btn-outline-secondary">📥 Download</a>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection
