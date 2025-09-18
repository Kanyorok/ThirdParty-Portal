@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Bank Account Details</h1>
    <div class="card mt-3">
        <div class="card-body">
            <dl class="row">
                <dt class="col-md-3">Bank</dt><dd class="col-md-9">{{ optional($account->bank)->BankName ?? '—' }}</dd>
                <dt class="col-md-3">Branch</dt><dd class="col-md-9">{{ optional($account->branch)->BranchName ?? '—' }}</dd>
                <dt class="col-md-3">Account Name</dt><dd class="col-md-9">{{ $account->AccountName ?? '—' }}</dd>
                <dt class="col-md-3">Account Number</dt><dd class="col-md-9">{{ $account->AccountNumber }}</dd>
                <dt class="col-md-3">IBAN</dt><dd class="col-md-9">{{ $account->IBAN ?? '—' }}</dd>
                <dt class="col-md-3">Currency</dt>
                    <dd class="col-md-9">
                        @if($account->currency)
                            {{ $account->currency->Code }} — {{ $account->currency->Name }} {{ $account->currency->Symbol ? '(' . $account->currency->Symbol . ')' : '' }}
                        @else
                            —
                        @endif
                    </dd>
                <dt class="col-md-3">GLAccountID</dt><dd class="col-md-9">{{ $account->GLAccountID ?? '—' }}</dd>
                <dt class="col-md-3">Opening Balance</dt><dd class="col-md-9">{{ number_format($account->OpeningBalance,2) }}</dd>
                <dt class="col-md-3">Current Balance</dt><dd class="col-md-9">{{ number_format($account->CurrentBalance,2) }}</dd>
                <dt class="col-md-3">Default</dt><dd class="col-md-9">{{ $account->IsDefault ? 'Yes' : 'No' }}</dd>
                <dt class="col-md-3">Active</dt><dd class="col-md-9">{{ $account->IsActive ? 'Yes' : 'No' }}</dd>
                <dt class="col-md-3">Created On</dt><dd class="col-md-9">{{ $account->CreatedOn }}</dd>
                <dt class="col-md-3">Created By</dt><dd class="col-md-9">{{ $account->CreatedBy ?? '—' }}</dd>
                <dt class="col-md-3">Modified On</dt><dd class="col-md-9">{{ $account->ModifiedOn ?? '—' }}</dd>
                <dt class="col-md-3">Modified By</dt><dd class="col-md-9">{{ $account->ModifiedBy ?? '—' }}</dd>
            </dl>
        </div>
        <div class="card-footer">
            <a href="{{ route('finance.bankaccountsetup.edit', $account->AccountID) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('finance.bankaccountsetup.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
