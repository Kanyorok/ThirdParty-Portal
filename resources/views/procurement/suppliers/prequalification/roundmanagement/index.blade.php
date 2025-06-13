@extends('layouts.app')
@section('title', 'Prequalification Periods')
@section('content')

    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span>📋 Prequalification Period</span>
            <a href="{{ route('preqrounds.create') }}" class="btn btn-sm btn-light">➕ New Period</a>
        </div>

        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Round Name</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                {{-- Static demo data --}}
                <tr>
                    <td>1</td>
                    <td>2025 Supplier Prequalification</td>
                    <td>01 Jan 2025</td>
                    <td>31 Mar 2025</td>
                    <td><span class="badge bg-success">Open</span></td>
                    <td>05 Dec 2024</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-primary">✏️ Edit</a>
                        <a href="#" class="btn btn-sm btn-info">🔍 View</a>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>2024 Specialized Services Round</td>
                    <td>15 Jun 2024</td>
                    <td>30 Sep 2024</td>
                    <td><span class="badge bg-secondary">Draft</span></td>
                    <td>10 Jun 2024</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-primary">✏️ Edit</a>
                        <a href="#" class="btn btn-sm btn-info">🔍 View</a>
                    </td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>2023 Prequalification Archive</td>
                    <td>01 Jan 2023</td>
                    <td>30 Jun 2023</td>
                    <td><span class="badge bg-danger">Closed</span></td>
                    <td>10 Nov 2022</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-primary">✏️ Edit</a>
                        <a href="#" class="btn btn-sm btn-info">🔍 View</a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

@endsection
