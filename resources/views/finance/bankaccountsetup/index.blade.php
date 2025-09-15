@extends('layouts.app')
@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="mb-0">Bank Accounts</h1>
        <a href="{{ route('finance.bankaccountsetup.create') }}" class="btn btn-success">Add Account</a>
    </div>

    @if(session('success')) <div class="alert alert-success mt-3">{{ session('success') }}</div> @endif

    <div class="table-responsive mt-3">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Bank</th>
                    <th>Branch</th>
                    <th>Account Name</th>
                    <th>Account Number</th>
                    <th>IBAN</th>
                    <th>CurrencyID</th>
                    <th>GLAccountID</th>
                    <th>Opening</th>
                    <th>Current</th>
                    <th>Default</th>
                    <th>Status</th>
                    <th style="width:230px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($accounts as $acc)
                    <tr>
                        <td>{{ optional($acc->bank)->BankName ?? '—' }}</td>
                        <td>{{ optional($acc->branch)->BranchName ?? '—' }}</td>
                        <td>{{ $acc->AccountName ?? '—' }}</td>
                        <td>{{ $acc->AccountNumber }}</td>
                        <td>{{ $acc->IBAN ?? '—' }}</td>
                        <td>{{ optional($acc->currency)->Code ?? '—' }}</td>
                        <td>{{ $acc->GLAccountID ?? '—' }}</td>
                        <td>{{ number_format($acc->OpeningBalance,2) }}</td>
                        <td>{{ number_format($acc->CurrentBalance,2) }}</td>
                        <td>{!! $acc->IsDefault ? '<span class="badge bg-info">Yes</span>' : '—' !!}</td>
                        <td>{!! $acc->IsActive ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('finance.bankaccountsetup.show', $acc->AccountID) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('finance.bankaccountsetup.edit', $acc->AccountID) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('finance.bankaccountsetup.destroy', $acc->AccountID) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this account?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="12" class="text-center text-muted py-4">No bank accounts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $accounts->links() }}
</div>
@endsection
