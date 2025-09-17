@extends('layouts.app')
@section('content')
<div class="container mt-3">
    <div class="card shadow-sm rounded-4 border-0">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-info"><i class="fas fa-code-branch me-2"></i> Branch Details</h5>
            <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-md-3">Bank</dt><dd class="col-md-9">{{ optional($branch->bank)->BankName ?? '—' }}</dd>
                <dt class="col-md-3">Branch Name</dt><dd class="col-md-9">{{ $branch->BranchName }}</dd>
                <dt class="col-md-3">Branch Code</dt><dd class="col-md-9">{{ $branch->BranchCode ?? '—' }}</dd>
                <dt class="col-md-3">Address1</dt><dd class="col-md-9">{{ $branch->Address1 ?? '—' }}</dd>
                <dt class="col-md-3">Address2</dt><dd class="col-md-9">{{ $branch->Address2 ?? '—' }}</dd>
                <dt class="col-md-3">City ID</dt><dd class="col-md-9">{{ $branch->CityID ?? '—' }}</dd>
                <dt class="col-md-3">Country ID</dt><dd class="col-md-9">{{ $branch->CountryID ?? '—' }}</dd>
                <dt class="col-md-3">Zip Code</dt><dd class="col-md-9">{{ $branch->ZipCode ?? '—' }}</dd>
                <dt class="col-md-3">Phone</dt><dd class="col-md-9">{{ $branch->Phone ?? '—' }}</dd>
                <dt class="col-md-3">Email</dt><dd class="col-md-9">{{ $branch->EmailID ?? '—' }}</dd>
                <dt class="col-md-3">Active</dt><dd class="col-md-9">{{ $branch->IsActive ? 'Yes' : 'No' }}</dd>
            </dl>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('finance.bankbranch.bybank', $branch->BankID) }}" class="btn btn-outline-secondary">Back</a>
            <a href="{{ route('finance.bankbranch.edit', $branch->BranchID) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>
</div>
@endsection
