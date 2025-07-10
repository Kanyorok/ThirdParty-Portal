@extends('layouts.app')
@section('title', 'Tax Jurisdictions')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">🌍 Tax Jurisdictions</h4>

        <div class="mb-3 text-end">
            <a href="{{ route('taxjurisdiction.create') }}" class="btn btn-primary">➕ Add Jurisdiction</a>
        </div>

        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                <th>Jurisdiction</th>
                <th>Currency</th>
                <th>Tax Authority</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Kenya</td>
                <td>KES</td>
                <td>KRA</td>
                <td><span class="badge bg-success">Active</span></td>
                <td><a href="#" class="btn btn-sm btn-info">View</a></td>
            </tr>
            <tr>
                <td>Uganda</td>
                <td>UGX</td>
                <td>URA</td>
                <td><span class="badge bg-success">Active</span></td>
                <td><a href="#" class="btn btn-sm btn-info">View</a></td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection
