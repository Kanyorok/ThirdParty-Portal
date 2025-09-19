@extends('layouts.app')
@section('content')
<div class="container mt-3">
    <div class="card shadow-sm rounded-4 border-0">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-info">🏦 Bank Details</h5>
            <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-md-3">Bank Name</dt><dd class="col-md-9">{{ $bank->BankName }}</dd>
                <dt class="col-md-3">Short Name</dt><dd class="col-md-9">{{ $bank->ShortName ?? '—' }}</dd>
                <dt class="col-md-3">Bank Code</dt><dd class="col-md-9">{{ $bank->BankCode ?? '—' }}</dd>
                <dt class="col-md-3">SWIFT Code</dt><dd class="col-md-9">{{ $bank->SwiftCode ?? '—' }}</dd>
                <dt class="col-md-3">Clearing Code</dt><dd class="col-md-9">{{ $bank->ClearingCode ?? '—' }}</dd>
                <dt class="col-md-3">Country</dt><dd class="col-md-9">{{ $bank->country?->Name ?? '—' }}</dd>
                <dt class="col-md-3">Created By</dt><dd class="col-md-9">{{ $bank->createdByUser?->Name ?? ($bank->CreatedBy ?? '—') }}</dd>
                <dt class="col-md-3">Modified By</dt><dd class="col-md-9">{{ $bank->modifiedByUser?->Name ?? ($bank->ModifiedBy ?? '—') }}</dd>
                <dt class="col-md-3">Email</dt><dd class="col-md-9">{{ $bank->EmailID ?? '—' }}</dd>
                <dt class="col-md-3">Phone</dt><dd class="col-md-9">{{ $bank->Phone ?? '—' }}</dd>
                <dt class="col-md-3">Website</dt><dd class="col-md-9">{{ $bank->Website ?? '—' }}</dd>
                <dt class="col-md-3">Active</dt><dd class="col-md-9">{{ $bank->IsActive ? 'Yes' : 'No' }}</dd>
                <dt class="col-md-3">Created On</dt><dd class="col-md-9">{{ $bank->CreatedOn }}</dd>
                <dt class="col-md-3">Created By</dt><dd class="col-md-9">{{ $bank->CreatedBy ?? '—' }}</dd>
                <dt class="col-md-3">Modified On</dt><dd class="col-md-9">{{ $bank->ModifiedOn ?? '—' }}</dd>
                <dt class="col-md-3">Modified By</dt><dd class="col-md-9">{{ $bank->ModifiedBy ?? '—' }}</dd>
            </dl>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('finance.bank.index') }}" class="btn btn-outline-secondary">Back</a>
            <a href="{{ route('finance.bank.edit', $bank->BankID) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>
</div>
@endsection
