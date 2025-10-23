@extends('layouts.app')
@section('title', 'Bank Account Details')
@section('content')
<div class="container mt-3">
    <div class="card shadow-sm rounded-4 border-0">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-info"><i class="fas fa-piggy-bank me-2"></i> Account Details</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('finance.bankaccountsetup.edit', $account->AccountID) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <button class="btn btn-sm btn-outline-dark" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <h6 class="text-muted text-uppercase mb-2">General</h6>
                    <dl class="row mb-0">
                        <dt class="col-5 fw-semibold">Bank</dt>
                        <dd class="col-7">{{ optional($account->bank)->BankName ?? '—' }}</dd>
                        <dt class="col-5 fw-semibold">Branch</dt>
                        <dd class="col-7">{{ optional($account->branch)->BranchName ?? '—' }}</dd>
                        <dt class="col-5 fw-semibold">Account Name</dt>
                        <dd class="col-7">{{ $account->AccountName ?? '—' }}</dd>
                        <dt class="col-5 fw-semibold">Account Number</dt>
                        <dd class="col-7">{{ $account->AccountNumber }}</dd>
                        <dt class="col-5 fw-semibold">IBAN</dt>
                        <dd class="col-7">{{ $account->IBAN ?? '—' }}</dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted text-uppercase mb-2">Financial</h6>
                    <dl class="row mb-0">
                        <dt class="col-5 fw-semibold">Currency</dt>
                        <dd class="col-7">
                            @if($account->currency)
                                {{ $account->currency->Code }} — {{ $account->currency->Name }} {{ $account->currency->Symbol ? '(' . $account->currency->Symbol . ')' : '' }}
                            @else
                                —
                            @endif
                        </dd>
                        <dt class="col-5 fw-semibold">GL Account</dt>
                        <dd class="col-7">{{$account->glAccount?->GLCode}} <small>({{ $account->glAccount?->GLName ?? $account->GLAccountID ?? '—' }})</small></dd>
                        <dt class="col-5 fw-semibold">Opening Balance</dt>
                        <dd class="col-7">{{ number_format($account->OpeningBalance,2) }}</dd>
                        <dt class="col-5 fw-semibold">Current Balance</dt>
                        <dd class="col-7">{{ number_format($account->CurrentBalance,2) }}</dd>
                        {{-- <dt class="col-5 fw-semibold">Default</dt>
                        <dd class="col-7">{!! $account->IsDefault ? '<span class="badge bg-info">Yes</span>' : '—' !!}</dd> --}}
                        <dt class="col-5 fw-semibold">Active</dt>
                        <dd class="col-7">{!! $account->IsActive ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted">Created {{ $account->CreatedOn }} • Modified {{ $account->ModifiedOn ?? '—' }}</small>
            <a href="{{ route('finance.bankaccountsetup.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
        </div>
    </div>
</div>
@endsection
