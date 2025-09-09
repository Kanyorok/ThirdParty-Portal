@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Branch Details: {{ $branch->BranchName }}</h1>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Branch Information</h5>
        </div>
        <div class="card-body">
            <p><strong>Branch Code:</strong> {{ $branch->BranchCode ?? 'N/A' }}</p>
            <p><strong>Address Line 1:</strong> {{ $branch->Address1 ?? 'N/A' }}</p>
            <p><strong>Address Line 2:</strong> {{ $branch->Address2 ?? 'N/A' }}</p>
            <p><strong>Phone:</strong> {{ $branch->Phone ?? 'N/A' }}</p>
            <p><strong>Email:</strong> {{ $branch->EmailID ?? 'N/A' }}</p>
            <p><strong>Status:</strong> {{ $branch->IsActive ? 'Active' : 'Inactive' }}</p>
        </div>
        <div class="card-footer">
            <a href="{{ route('finance.bankbranch.edit', [$bankId, $branch->BranchID]) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('finance.bankbranch.index', $bankId) }}" class="btn btn-secondary">Back to Branch List</a>
        </div>
    </div>
</div>
@endsection
