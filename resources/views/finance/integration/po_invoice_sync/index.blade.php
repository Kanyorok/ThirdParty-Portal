@extends('layouts.app')
@section('title', 'PO to Invoice Sync Setup')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">🔄 PO to Invoice Sync Setup</h4>
        <a href="{{ route('po-invoice-sync.create') }}" class="btn btn-primary mb-3">➕ Add Sync Rule</a>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>PO Type</th>
                <th>Default GL Account</th>
                <th>Auto Sync</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Goods Purchase</td>
                <td>5000 - Procurement Payable</td>
                <td><span class="badge bg-success">Enabled</span></td>
                <td><span class="badge bg-success">Active</span></td>
                <td>
                    <a href="#" class="btn btn-sm btn-secondary">Edit</a>
                </td>
            </tr>
            <tr>
                <td>Service PO</td>
                <td>5010 - Services Payable</td>
                <td><span class="badge bg-secondary">Disabled</span></td>
                <td><span class="badge bg-success">Active</span></td>
                <td>
                    <a href="#" class="btn btn-sm btn-secondary">Edit</a>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection
