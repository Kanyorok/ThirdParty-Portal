@extends('layouts.app')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Cashbook Entry #{{ $entry->CashbookID }}</h1>
        <div>
            @if($entry->Status==='Draft')
                <a href="{{ route('cashbook.edit', $entry->CashbookID) }}" class="btn btn-warning">Edit</a>
                <form action="{{ route('cashbook.post', $entry->CashbookID) }}" method="POST" class="d-inline">
                    @csrf <button class="btn btn-primary" onclick="return confirm('Post this entry?')">Post</button>
                </form>
            @elseif($entry->Status==='Posted')
                <form action="{{ route('cashbook.void', $entry->CashbookID) }}" method="POST" class="d-inline">
                    @csrf <button class="btn btn-outline-danger" onclick="return confirm('Void this entry?')">Void</button>
                </form>
            @endif
            <a href="{{ route('cashbook.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    @if(session('success')) <div class="alert alert-success mt-3">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger mt-3">{{ session('error') }}</div> @endif

    <div class="card mt-3">
        <div class="card-body">
            <dl class="row">
                <dt class="col-md-3">Type</dt><dd class="col-md-9">{{ $entry->EntryType }}</dd>
                <dt class="col-md-3">Date</dt><dd class="col-md-9">{{ $entry->DocDate }}</dd>
                <dt class="col-md-3">Bank Account</dt>
                <dd class="col-md-9">
                    {{ optional($entry->bankAccount->bank)->BankName ?? '—' }} — {{ $entry->bankAccount->AccountNumber ?? '—' }}
                </dd>
                <dt class="col-md-3">Currency</dt><dd class="col-md-9">{{ optional($entry->currency)->Code ?? '—' }}</dd>
                <dt class="col-md-3">Exchange Rate</dt><dd class="col-md-9">{{ $entry->ExchangeRate }}</dd>
                <dt class="col-md-3">Amount</dt><dd class="col-md-9">{{ number_format($entry->Amount, 2) }}</dd>
                <dt class="col-md-3">Reference</dt><dd class="col-md-9">{{ $entry->Reference ?? '—' }}</dd>
                <dt class="col-md-3">Party</dt><dd class="col-md-9">{{ $entry->PartyName ?? '—' }}</dd>
                <dt class="col-md-3">Status</dt><dd class="col-md-9">{{ $entry->Status }}</dd>
                <dt class="col-md-3">Narration</dt><dd class="col-md-9">{{ $entry->Narration ?? '—' }}</dd>
            </dl>
            <h5 class="mt-4">GL Split</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead><tr><th>GL Account</th><th>Description</th><th>Debit</th><th>Credit</th></tr></thead>
                    <tbody>
                        @forelse($entry->lines as $ln)
                            <tr>
                                <td>{{ $ln->GLAccountID ?? '—' }}</td>
                                <td>{{ $ln->Description ?? '—' }}</td>
                                <td>{{ number_format($ln->AmountDr,2) }}</td>
                                <td>{{ number_format($ln->AmountCr,2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No GL lines</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
